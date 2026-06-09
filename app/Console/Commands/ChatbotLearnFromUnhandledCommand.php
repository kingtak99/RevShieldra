<?php

namespace App\Console\Commands;

use App\Models\ChatbotLearnedKeyword;
use App\Models\ChatbotUnhandledQuery;
use App\Services\ChatbotFlowService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatbotLearnFromUnhandledCommand extends Command
{
    protected $signature = 'chatbot:learn-from-unhandled {--limit=25 : Maximum pending queries per language (25 for quota optimization)}';

    protected $description = 'Learn from unhandled user queries and map them to existing flows using Gemini.';

    public function handle(): int
    {
        $apiKey = config('services.gemini.key');
        $model = config('services.gemini.model', env('GEMINI_MODEL', 'gemini-flash-latest'));

        if (!$apiKey) {
            $this->warn('GEMINI_API_KEY is not configured.');
            return self::FAILURE;
        }

        $totalLearned = 0;
        $learnedDetails = [];
        $limit = (int) $this->option('limit');

        foreach (['ar', 'en'] as $language) {
            $this->info("========================================");
            $this->info("Processing [{$language}] Language");
            $this->info("========================================");

            // جلب خريطة التدفقات الحالية
            $flowMap = $this->buildFlowMap($language);

            // جلب الاستفسارات المعلقة من المستخدمين
            $pending = ChatbotUnhandledQuery::query()
                ->where('language', $language)
                ->where('status', 'pending')
                ->orderByDesc('occurrences')
                ->orderBy('created_at')
                ->limit($limit)
                ->get();

            if ($pending->isEmpty()) {
                $this->info("No pending {$language} queries to process.");
                continue;
            }

            $this->info("Processing " . $pending->count() . " pending unhandled queries...");
            $queriesToProcess = $pending->take(25)->pluck('query')->all();
            $prompt = $this->buildPrompt($language, $flowMap, $queriesToProcess);

            try {
                $response = $this->executeGeminiRequest($prompt, $model, 15, [
                    'temperature' => 0.1,
                    'maxOutputTokens' => 1200,
                ]);

                if (!$response) {
                    $this->warn("⚠️  Gemini API not responding. Check storage/logs/laravel.log for details.");
                    Log::warning("Gemini request returned null for pending {$language} queries");
                } elseif ($response->successful()) {
                    $results = $this->extractLearningResults($response->json());
                    if ($results !== null) {
                        $pendingByQuery = $pending->keyBy(fn($pendingQuery) => $this->normalize($pendingQuery->getAttribute('query'), $language));
                        $processedIds = [];

                        foreach ($results as $item) {
                            $keyword = trim((string) ($item['keyword'] ?? ''));
                            $matchedFlow = trim((string) ($item['matched_flow'] ?? 'none'));
                            $matchedBranch = trim((string) ($item['matched_branch'] ?? ''));
                            $confidence = isset($item['confidence']) ? (float) $item['confidence'] : null;
                            $generatedResponse = isset($item['generated_response']) ? trim((string) $item['generated_response']) : null;

                            if ($keyword === '') {
                                continue;
                            }

                            $normalized = $this->normalize($keyword, $language);
                            $unhandled = $pendingByQuery->get($normalized);
                            if ($unhandled) {
                                $processedIds[] = $unhandled->id;
                            }

                            if ($matchedFlow === 'none') {
                                continue;
                            }

                            if ($matchedFlow !== 'chitchat' && !$this->isValidTarget($flowMap, $matchedFlow, $matchedBranch !== '' ? $matchedBranch : null)) {
                                continue;
                            }

                            $summary = $this->persistLearnedKeyword(
                                $language,
                                $normalized,
                                $keyword,
                                $matchedFlow,
                                $matchedBranch !== '' ? $matchedBranch : null,
                                $matchedFlow === 'chitchat' ? $generatedResponse : null,
                                'user_error',
                                $confidence
                            );

                            if ($summary !== null) {
                                $learnedDetails[] = $summary;
                                $totalLearned++;
                            }
                        }

                        if ($processedIds !== []) {
                            ChatbotUnhandledQuery::query()
                                ->whereIn('id', array_unique($processedIds))
                                ->update([
                                    'status' => 'processed',
                                    'processed_at' => now(),
                                ]);

                            $this->info("Updated " . count(array_unique($processedIds)) . " queries to 'processed' status.");
                        }
                    } else {
                        Log::warning("Gemini pending-query extraction returned no parseable results for {$language}", [
                            'body' => $response->body(),
                        ]);
                        $this->warn("Gemini returned unparseable results.");
                    }
                } else {
                    $errorBody = $response ? $response->body() : 'no response';
                    Log::warning("Gemini failed for pending {$language} queries", ['body' => $errorBody]);

                    if (str_contains($errorBody, 'exceeded your current quota')) {
                        $this->error("❌ Gemini API Quota Exceeded!");
                        $this->error("Your API plan has reached its limit. Please:");
                        $this->error("1. Check your Google Cloud Console quota usage");
                        $this->error("2. Upgrade your API plan if needed");
                        $this->error("3. Contact Google Cloud support");

                        // Fallback: Map unhandled queries using local keyword matching
                        $this->info("Using fallback keyword matching (offline mode)...");
                        foreach ($pending as $unhandledQuery) {
                            $normalized = $this->normalize($unhandledQuery->getAttribute('query'), $language);
                            // محاولة مطابقة عامة - إذا كان يحتوي على كلمات الاحتيال
                            $fraudKeywords = $language === 'ar' ? ['احتيال', 'مشبوهة', 'غير عادي'] : ['fraud', 'suspicious', 'unusual'];
                            $isRelated = false;
                            foreach ($fraudKeywords as $fk) {
                                if (str_contains(mb_strtolower($normalized), mb_strtolower($fk))) {
                                    $isRelated = true;
                                    break;
                                }
                            }
                            if ($isRelated) {
                                $processedIds[] = $unhandledQuery->id;
                            }
                        }
                        $this->info("Marked " . count($processedIds) . " queries as processed (offline fallback).");
                    } elseif (str_contains($errorBody, 'high demand')) {
                        $this->warn("⚠️  Gemini API High Demand: Service temporarily unavailable");
                        $this->warn("Please try again later.");
                    } else {
                        $this->warn("Gemini API failed: " . mb_substr($errorBody, 0, 200));
                    }
                }
            } catch (\Throwable $exception) {
                Log::error("Failed to run Gemini on pending {$language} queries: " . $exception->getMessage());
                $this->error("Error: " . $exception->getMessage());
            }
        }

        $this->info("========================================");
        $this->info("Learning from Unhandled Queries Completed!");
        $this->info("Total learned from user errors: {$totalLearned} keywords.");
        $this->outputDetailList('Detailed results', $learnedDetails);
        $this->info("========================================");

        return self::SUCCESS;
    }

    private function buildFlowMap(string $language): array
    {
        $flows = ChatbotFlowService::getFlows($language);
        $map = [];

        foreach (($flows['root']['flows'] ?? []) as $flowKey => $flow) {
            $map[$flowKey] = [
                'label' => $flow['label'] ?? $flowKey,
                'branches' => [],
            ];

            foreach (($flow['branches'] ?? []) as $branchKey => $branch) {
                $map[$flowKey]['branches'][$branchKey] = [
                    'label' => $branch['label'] ?? $branchKey,
                    'response' => $branch['response'] ?? '',
                    'category' => $branch['category'] ?? null,
                ];
            }
        }

        return $map;
    }

    private function buildPrompt(string $language, array $flowMap, array $queries): string
    {
        return implode("\n", [
            "You are a strict data mapper for the RevShieldra chatbot.",
            "Your job is to map failed user queries to either an existing flow or recognize them as general pleasantries/chitchat.",
            "Language: {$language}",
            "Available flows and branches JSON:",
            json_encode($flowMap, JSON_UNESCAPED_UNICODE),
            "Unhandled user queries JSON:",
            json_encode(array_values($queries), JSON_UNESCAPED_UNICODE),
            "Rules:",
            "1. If the query matches a business flow, use the existing flow key and set generated_response to null.",
            "2. If the query is general pleasantry, greeting, or small talk (for example 'مساء الخير', 'شكراً', 'hi'), set matched_flow to 'chitchat', matched_branch to null, and write a polite response in generated_response using the same language.",
            "3. If the query is completely irrelevant, toxic, or spam, return matched_flow as none, matched_branch as null, and generated_response as null.",
            "4. Do not invent new flows, branches, answers, or keywords.",
            "5. Return only valid raw JSON with this schema:",
            '{"learning_results":[{"keyword":"original query","matched_flow":"flow_key_or_chitchat_or_none","matched_branch":"branch_key_or_null","confidence":0.0,"generated_response":"polite_reply_or_null"}]}',
        ]);
    }

    private function extractLearningResults(array $body): ?array
    {
        $text = $this->getLearningResponseText($body);
        if (!is_string($text) || trim($text) === '') {
            return null;
        }

        $decoded = json_decode($text, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            Log::warning('Chatbot learn-from-unhandled returned invalid JSON from Gemini', [
                'raw_text' => mb_substr($text, 0, 2000),
                'json_error' => json_last_error_msg(),
            ]);
            return null;
        }

        if (!isset($decoded['learning_results']) || !is_array($decoded['learning_results'])) {
            Log::warning('Chatbot learn-from-unhandled Gemini output missing learning_results', [
                'decoded' => $decoded,
            ]);
            return null;
        }

        return $decoded['learning_results'];
    }

    private function executeGeminiRequest(string $prompt, string $model, int $timeout, array $generationConfig)
    {
        $apiKey = config('services.gemini.key');
        if (!$apiKey) {
            Log::error("Gemini API key not configured");
            return null;
        }

        // $endpoint = "https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key={$apiKey}";
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
        try {
            $response = Http::timeout($timeout)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($endpoint, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => array_merge($generationConfig, [
                        'responseMimeType' => 'application/json',
                    ]),
                ]);

            if ($response->successful()) {
                return $response;
            }

            // Log failed response with HTTP status
            Log::error("Gemini request failed for model {$model}: HTTP {$response->status()}", [
                'body' => mb_substr($response->body(), 0, 300),
            ]);

            // Try fallback model only for rate-limit / service issues, not 404
            if (in_array($response->status(), [429, 503, 504], true)) {
                $fallbackModel = config('services.gemini.fallback_model', 'gemini-1.5-flash');
                if ($fallbackModel !== $model) {
                    Log::info("Trying fallback model {$fallbackModel}");
                    return $this->executeGeminiRequest($prompt, $fallbackModel, $timeout, $generationConfig);
                }
            }

            // Return response even if failed so we can see the error message
            return $response;
        } catch (\Throwable $exception) {
            Log::error("Gemini request exception: " . $exception->getMessage(), [
                'model' => $model,
                'class' => class_basename($exception),
            ]);
            return null;
        }
    }

    private function getLearningResponseText(array $body): ?string
    {
        $candidates = [
            'candidates.0.content.parts.0.text',
            'candidates.0.content.0.text',
            'candidates.0.output.0.content.0.text',
            'candidates.0.text',
        ];

        foreach ($candidates as $path) {
            $text = data_get($body, $path);
            if (is_string($text) && trim($text) !== '') {
                return $this->cleanLearningResponseText($text);
            }
        }

        return null;
    }

    private function cleanLearningResponseText(string $text): string
    {
        $text = trim($text);
        $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);
        $text = preg_replace('/^\s*JSON:\s*/i', '', $text);

        $backticks = chr(96) . chr(96) . chr(96);
        $text = str_replace([$backticks . 'json', $backticks], '', $text);

        return trim($text);
    }

    private function isValidTarget(array $flowMap, string $flow, ?string $branch): bool
    {
        if (!isset($flowMap[$flow])) {
            return false;
        }

        return $branch === null || $branch === '' || isset($flowMap[$flow]['branches'][$branch]);
    }

    private function normalize(string $text, string $language): string
    {
        $text = trim(mb_strtolower($text));

        if ($language === 'ar') {
            $text = str_replace(['أ', 'إ', 'آ', 'ى', 'ئ', 'ؤ', 'ة', 'ـ'], ['ا', 'ا', 'ا', 'ي', 'ي', 'و', 'ه', ''], $text);
        }

        return Str::squish($text);
    }

    private function persistLearnedKeyword(
        string $language,
        string $normalized,
        string $keyword,
        string $matchedFlow,
        ?string $matchedBranch,
        ?string $generatedResponse,
        string $source,
        ?float $confidence
    ): ?string {
        $record = ChatbotLearnedKeyword::firstOrNew([
            'language' => $language,
            'normalized_keyword' => $normalized,
        ]);

        $record->keyword = $keyword;
        $record->target_flow = $matchedFlow;
        $record->target_branch = $matchedBranch;
        $record->custom_response = $generatedResponse;
        $record->source = $source;
        $record->confidence = $confidence;
        $record->save();

        $action = $record->wasRecentlyCreated ? 'added' : 'updated';
        $flowBranch = $matchedBranch ? "{$matchedFlow}.{$matchedBranch}" : $matchedFlow;
        $responsePart = $generatedResponse ? " | response: {$generatedResponse}" : '';

        return sprintf(
            '%s: "%s" => %s%s',
            ucfirst($action),
            $keyword,
            $flowBranch,
            $responsePart
        );
    }

    private function outputDetailList(string $title, array $items): void
    {
        if (empty($items)) {
            $this->info("{$title}: none.");
            return;
        }

        $this->info("{$title}:");
        foreach (array_slice($items, 0, 80) as $item) {
            $this->line(" - {$item}");
        }

        if (count($items) > 80) {
            $this->info('... and ' . (count($items) - 80) . ' more results.');
        }
    }
}

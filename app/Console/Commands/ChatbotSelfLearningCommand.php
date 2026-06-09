<?php

namespace App\Console\Commands;

use App\Models\ChatbotLearnedKeyword;
use App\Services\ChatbotFlowService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatbotSelfLearningCommand extends Command
{
    protected $signature = 'chatbot:self-learning';

    protected $description = 'Proactively generate new keywords and phrases for existing flows using Gemini without requiring user errors.';

    public function handle(): int
    {
        $apiKey = config('services.gemini.key');
        $model = config('services.gemini.model', 'gemini-flash-latest');

        if (!$apiKey) {
            $this->warn('GEMINI_API_KEY is not configured.');
            return self::FAILURE;
        }

        $totalGenerated = 0;
        $generatedDetails = [];

        foreach (['ar', 'en'] as $language) {
            $this->info("========================================");
            $this->info("Self-Learning for [{$language}] Language");
            $this->info("========================================");

            // جلب خريطة التدفقات الحالية
            $flowMap = $this->buildFlowMap($language);

            // جلب الكلمات التي تعلمها البوت بالفعل لتجنب التكرار
            $existingLearnedKeywords = ChatbotLearnedKeyword::query()
                ->where('language', $language)
                ->limit(80)
                ->pluck('keyword')
                ->all();

            $this->info("Found " . count($existingLearnedKeywords) . " existing keywords (sample for optimization).");
            $this->info("Launching proactive generation cycle...");

            $expansionPrompt = $this->buildSelfExpansionPrompt($language, $flowMap, $existingLearnedKeywords);

            try {
                $response = $this->executeGeminiRequest($expansionPrompt, $model, 15, [
                    'temperature' => 0.6,
                    'maxOutputTokens' => 1200,
                ]);

                if (!$response) {
                    $this->warn("⚠️  Gemini API not responding. Check storage/logs/laravel.log for details.");
                    Log::warning("Gemini request returned null for {$language}");
                } elseif ($response->successful()) {
                    $expandedResults = $this->extractLearningResults($response->json());
                    if ($expandedResults !== null) {
                        $this->info("Gemini returned " . count($expandedResults) . " generated results.");

                        foreach ($expandedResults as $item) {
                            $keyword = trim((string) ($item['keyword'] ?? ''));
                            $matchedFlow = trim((string) ($item['matched_flow'] ?? 'none'));
                            $matchedBranch = trim((string) ($item['matched_branch'] ?? ''));
                            $confidence = isset($item['confidence']) ? (float) $item['confidence'] : 0.85;
                            $generatedResponse = isset($item['generated_response']) ? trim((string) $item['generated_response']) : null;

                            if ($keyword === '' || $matchedFlow === 'none') {
                                continue;
                            }

                            if ($matchedFlow !== 'chitchat' && !$this->isValidTarget($flowMap, $matchedFlow, $matchedBranch !== '' ? $matchedBranch : null)) {
                                continue;
                            }

                            $normalized = $this->normalize($keyword, $language);

                            // حفظ الكلمات المبتكرة ذاتياً
                            $summary = $this->persistLearnedKeyword(
                                $language,
                                $normalized,
                                $keyword,
                                $matchedFlow,
                                $matchedBranch !== '' ? $matchedBranch : null,
                                $matchedFlow === 'chitchat' ? $generatedResponse : null,
                                'self_learning',
                                $confidence
                            );

                            if ($summary !== null) {
                                $generatedDetails[] = $summary;
                                $totalGenerated++;
                            }
                        }

                        $this->info("Successfully processed " . count($expandedResults) . " results.");
                    } else {
                        Log::warning("Gemini self-expansion returned no parseable results for {$language}", [
                            'body' => $response->body(),
                        ]);
                        $this->warn("Gemini returned unparseable results.");
                    }
                } else {
                    $errorBody = $response ? $response->body() : 'no response';
                    Log::warning("Gemini self-expansion request failed for {$language}", ['body' => $errorBody]);
                    
                    if (str_contains($errorBody, 'exceeded your current quota')) {
                        $this->error("❌ Gemini API Quota Exceeded!");
                        $this->error("Your API plan has reached its limit. Please:");
                        $this->error("1. Check your Google Cloud Console quota usage");
                        $this->error("2. Upgrade your API plan if needed");
                        $this->error("3. Contact Google Cloud support");
                        
                        // Fallback: استخدام keywords مسبقة
                        if ($language === 'ar') {
                            $fallbackKeywords = ['عملية احتيالية', 'محاولة احتيال', 'معاملة مشبوهة', 'تحويل غير آمن'];
                        } else {
                            $fallbackKeywords = ['fraudulent transaction', 'suspicious activity', 'unauthorized charge', 'payment fraud'];
                        }
                        
                        $this->info("Using fallback keywords to maintain service (offline mode)...");
                        foreach ($fallbackKeywords as $keyword) {
                            if (!ChatbotLearnedKeyword::where('keyword', $keyword)->where('language', $language)->exists()) {
                                $summary = $this->persistLearnedKeyword(
                                    $language,
                                    $this->normalize($keyword, $language),
                                    $keyword,
                                    'fraud_detection',
                                    null,
                                    null,
                                    'fallback_offline',
                                    0.8
                                );
                                if ($summary !== null) {
                                    $generatedDetails[] = $summary;
                                    $totalGenerated++;
                                }
                            }
                        }
                        $this->info("Added " . $totalGenerated . " fallback keywords.");
                    } elseif (str_contains($errorBody, 'high demand')) {
                        $this->warn("⚠️  Gemini API High Demand: Service temporarily unavailable");
                        $this->warn("Please try again later.");
                    } else {
                        $this->warn("Gemini API failed: " . mb_substr($errorBody, 0, 200));
                    }
                }
            } catch (\Throwable $exception) {
                Log::error("Failed to execute self-expansion for {$language}: " . $exception->getMessage());
                $this->error("Error: " . $exception->getMessage());
            }
        }

        $this->info("========================================");
        $this->info("Self-Learning Cycle Completed!");
        $this->info("Total proactive keywords generated: {$totalGenerated}.");
        $this->outputDetailList('Generated keywords', $generatedDetails);
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

    private function buildSelfExpansionPrompt(string $language, array $flowMap, array $existingKeywords): string
    {
        return implode("\n", [
            "You are a proactive AI Self-Learning system for the RevShieldra chatbot.",
            "Your goal is to brain-storm, predict, and generate realistic search queries, questions, or alternative phrases that a human might ask to trigger the existing system flows.",
            "Language: {$language}",
            "We want to build robust synonyms, colloquial phrases, typical user typos, and various syntactic ways of asking for things.",
            "Here is the system's Flow Map JSON containing all targets you must generate phrases for:",
            json_encode($flowMap, JSON_UNESCAPED_UNICODE),
            "",
            "These are keywords we already have in our database (DO NOT generate exact duplicates of these):",
            json_encode(array_slice($existingKeywords, 0, 150), JSON_UNESCAPED_UNICODE),
            "",
            "Rules for Self-Learning & Brainstorming:",
            "1. For each active flow and branch in the Flow Map, brainstorm 3 to 5 highly probable alternative ways a user would ask for it in a natural conversation.",
            "2. Generate diverse formats: short direct commands, complete questions, colloquial slang (if Arabic, think of common Jordanian/Gulf/Levantine phrasings as well), and common typos.",
            "3. If a flow relates to pricing, generate phrases like 'كم الاشتراك', 'بكم الخدمة', 'pricing options', 'how much'.",
            "4. If a flow relates to location setup, generate phrases like 'كيف اضيف موقعي', 'اضافة فرع جديد', 'adding branches', 'new address'.",
            "5. You can also generate pleasantries/chitchat variations (greetings, feedback compliments, test queries) and map them to 'chitchat' with an appropriate 'generated_response'.",
            "6. Do not repeat any keyword that already exists in the provided keywords list. Avoid duplicates and avoid returning the same phrase with only small differences.",
            "7. Return ONLY valid RAW JSON with no Markdown, no code fences, and no explanatory text.",
            "8. If a prompt cannot be mapped to a flow, return matched_flow as 'none' and matched_branch as null.",
            "9. Make sure 'matched_flow' and 'matched_branch' exactly match the keys present in the Flow Map JSON.",
            "10. Return between 10 and 15 total creative results in the array to optimize API usage.",
            '{"learning_results":[{"keyword":"brainstormed phrase or query","matched_flow":"flow_key_or_chitchat","matched_branch":"branch_key_or_null","confidence":0.90,"generated_response":"polite_reply_if_chitchat_else_null"}]}',
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
            Log::warning('Chatbot self-learning returned invalid JSON from Gemini', [
                'raw_text' => mb_substr($text, 0, 2000),
                'json_error' => json_last_error_msg(),
            ]);
            return null;
        }

        if (!isset($decoded['learning_results']) || !is_array($decoded['learning_results'])) {
            Log::warning('Chatbot self-learning Gemini output missing learning_results', [
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

        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

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

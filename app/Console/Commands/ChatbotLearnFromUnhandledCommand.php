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
    protected $signature = 'chatbot:learn-from-unhandled {--limit=50 : Maximum pending queries per language}';

    protected $description = 'Map unhandled chatbot queries to existing flows using Gemini and save learned keywords.';

    public function handle(): int
    {
        $apiKey = config('services.gemini.key');
        $model = config('services.gemini.model', 'gemini-flash-latest');

        if (!$apiKey) {
            $this->warn('GEMINI_API_KEY is not configured.');

            return self::FAILURE;
        }

        $totalLearned = 0;
        $limit = (int) $this->option('limit');

        foreach (['ar', 'en'] as $language) {
            $pending = ChatbotUnhandledQuery::query()
                ->where('language', $language)
                ->where('status', 'pending')
                ->orderByDesc('occurrences')
                ->orderBy('created_at')
                ->limit($limit)
                ->get();

            if ($pending->isEmpty()) {
                $this->info("No pending {$language} queries.");
                continue;
            }

            $flowMap = $this->buildFlowMap($language);
            $prompt = $this->buildPrompt($language, $flowMap, $pending->pluck('query')->all());

            try {
                $response = Http::timeout(30)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt],
                                ],
                            ],
                        ],
                        'generationConfig' => [
                            'temperature' => 0.1,
                            'maxOutputTokens' => 1600,
                            'responseMimeType' => 'application/json',
                        ],
                    ]);
            } catch (\Throwable $exception) {
                Log::warning('Chatbot auto learn Gemini request failed: ' . $exception->getMessage());
                $this->error("Gemini request failed for {$language}: {$exception->getMessage()}");

                continue;
            }

            if (!$response->successful()) {
                Log::warning('Chatbot auto learn Gemini response failed', [
                    'language' => $language,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                $this->error("Gemini returned HTTP {$response->status()} for {$language}.");

                continue;
            }

            $results = $this->extractLearningResults($response->json());
            if ($results === null) {
                $this->error("Gemini returned invalid JSON for {$language}.");
                continue;
            }

            $pendingByQuery = $pending->keyBy(fn(ChatbotUnhandledQuery $query) => $this->normalize($query->query, $language));
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

                ChatbotLearnedKeyword::updateOrCreate(
                    [
                        'language' => $language,
                        'normalized_keyword' => $normalized,
                    ],
                    [
                        'keyword' => $keyword,
                        'target_flow' => $matchedFlow,
                        'target_branch' => $matchedBranch !== '' ? $matchedBranch : null,
                        'custom_response' => $matchedFlow === 'chitchat' ? $generatedResponse : null,
                        'source' => 'gemini',
                        'confidence' => $confidence,
                    ]
                );

                $totalLearned++;
            }

            if ($processedIds !== []) {
                ChatbotUnhandledQuery::query()
                    ->whereIn('id', array_unique($processedIds))
                    ->update([
                        'status' => 'processed',
                        'processed_at' => now(),
                    ]);
            }

            $this->info("Processed {$language}: learned {$totalLearned} total keywords so far.");
        }

        $this->info("Chatbot auto learning completed. Learned {$totalLearned} keywords.");

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
        $text = data_get($body, 'candidates.0.content.parts.0.text');
        if (!is_string($text) || trim($text) === '') {
            return null;
        }

        $text = trim(preg_replace('/^```(?:json)?\s*|\s*```$/', '', $text));
        $decoded = json_decode($text, true);

        if (!is_array($decoded) || !isset($decoded['learning_results']) || !is_array($decoded['learning_results'])) {
            return null;
        }

        return $decoded['learning_results'];
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
            $text = str_replace(['أ', 'إ', 'آ', 'ى', 'ئ', 'ؤ', 'ة', 'ـ'], ['ا', 'ا', 'ا', 'ي', 'ي', 'و', 'ه', ''], $text);
        }

        return Str::squish($text);
    }
}

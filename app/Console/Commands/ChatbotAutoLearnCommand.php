<?php

namespace App\Console\Commands;

use App\Models\ChatbotLearnedKeyword;
use App\Models\ChatbotUnhandledQuery;
use App\Services\ChatbotFlowService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatbotAutoLearnCommand extends Command
{
    protected $signature = 'chatbot:auto-learn {--limit=50 : Maximum pending queries per language}';

    protected $description = 'Evolve chatbot knowledge by mapping pending unhandled queries AND proactively brainstorming new expression variants for existing flows using Gemini.';

    public function handle(): int
    {
        $apiKey = config('services.gemini.key');
        $model = config('services.gemini.model', 'gemini-flash-latest');

        if (!$apiKey) {
            $this->warn('GEMINI_API_KEY is not configured.');
            return self::FAILURE;
        }

        $totalLearnedFromUnhandled = 0;
        $totalSelfExpanded = 0;
        $learnedFromUnhandledDetails = [];
        $selfExpandedDetails = [];
        $limit = (int) $this->option('limit');

        foreach (['ar', 'en'] as $language) {
            $this->info("========================================");
            $this->info("Processing [{$language}] Language");
            $this->info("========================================");

            // جلب خريطة التدفقات الحالية (Flow Map)
            $flowMap = $this->buildFlowMap($language);

            // ==========================================
            // المسار الأول: معالجة أخطاء ومشاكل المستخدمين المعلقة (Unhandled Queries)
            // ==========================================
            $pending = ChatbotUnhandledQuery::query()
                ->where('language', $language)
                ->where('status', 'pending')
                ->orderByDesc('occurrences')
                ->orderBy('created_at')
                ->limit($limit)
                ->get();

            if ($pending->isEmpty()) {
                $this->info("No pending {$language} queries to process.");
            } else {
                $this->info("Processing " . $pending->count() . " pending unhandled queries...");
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

                    if ($response->successful()) {
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
                                    'gemini',
                                    $confidence
                                );

                                if ($summary !== null) {
                                    $learnedFromUnhandledDetails[] = $summary;
                                    $totalLearnedFromUnhandled++;
                                }
                            }

                            if ($processedIds !== []) {
                                ChatbotUnhandledQuery::query()
                                    ->whereIn('id', array_unique($processedIds))
                                    ->update([
                                        'status' => 'processed',
                                        'processed_at' => now(),
                                    ]);
                            }
                        }
                    } else {
                        Log::warning("Gemini failed for pending {$language} queries", ['body' => $response->body()]);
                    }
                } catch (\Throwable $exception) {
                    Log::error("Failed to run Gemini on pending {$language} queries: " . $exception->getMessage());
                }
            }

            // ==========================================
            // المسار الثاني: التعلم والتوسيع الذاتي الاستباقي (Self-Learning & Proactive Expansion)
            // ==========================================
            $this->info("Launching proactive self-learning & expansion cycle for [{$language}]...");

            // جلب عينة من الكلمات التي تعلمها البوت سابقاً لتجنب التكرار
            $existingLearnedKeywords = ChatbotLearnedKeyword::query()
                ->where('language', $language)
                ->pluck('keyword')
                ->all();

            $expansionPrompt = $this->buildSelfExpansionPrompt($language, $flowMap, $existingLearnedKeywords);

            try {
                $response = Http::timeout(30)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $expansionPrompt],
                                ],
                            ],
                        ],
                        'generationConfig' => [
                            'temperature' => 0.7, // لرفع القدرة الإبداعية وتوليد جمل بديلة متنوعة
                            'maxOutputTokens' => 2000,
                            'responseMimeType' => 'application/json',
                        ],
                    ]);

                if ($response->successful()) {
                    $expandedResults = $this->extractLearningResults($response->json());
                    if ($expandedResults !== null) {
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

                            // حفظ الكلمات المبتكرة ذاتياً وتحديد مصدرها كتعلم ذاتي
                            $summary = $this->persistLearnedKeyword(
                                $language,
                                $normalized,
                                $keyword,
                                $matchedFlow,
                                $matchedBranch !== '' ? $matchedBranch : null,
                                $matchedFlow === 'chitchat' ? $generatedResponse : null,
                                'gemini_self_learning',
                                $confidence
                            );

                            if ($summary !== null) {
                                $selfExpandedDetails[] = $summary;
                                $totalSelfExpanded++;
                            }
                        }
                    }
                } else {
                    Log::warning("Gemini self-expansion request failed for {$language}", ['body' => $response->body()]);
                }
            } catch (\Throwable $exception) {
                Log::error("Failed to execute self-expansion for {$language}: " . $exception->getMessage());
            }
        }

        $this->info("========================================");
        $this->info("Learning Cycle Completed Successfully!");
        $this->info("Learned from Unhandled Queries: {$totalLearnedFromUnhandled} keywords.");
        $this->info("Learned via Generative Self-Learning: {$totalSelfExpanded} proactive keywords.");
        $this->outputDetailList('Detailed pending-query learning results', $learnedFromUnhandledDetails);
        $this->outputDetailList('Detailed proactive self-learning results', $selfExpandedDetails);
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
            "6. Make sure 'matched_flow' and 'matched_branch' exactly match the keys present in the Flow Map JSON.",
            "7. Return ONLY valid raw JSON with this schema (make sure to generate up to 25-35 total creative results in the array):",
            '{"learning_results":[{"keyword":"brainstormed phrase or query","matched_flow":"flow_key_or_chitchat","matched_branch":"branch_key_or_null","confidence":0.90,"generated_response":"polite_reply_if_chitchat_else_null"}]}',
        ]);
    }

    private function extractLearningResults(array $body): ?array
    {
        $text = data_get($body, 'candidates.0.content.parts.0.text');
        if (!is_string($text) || trim($text) === '') {
            return null;
        }

        // تم الاستبدال الآمن للرموز الحساسة لتجنب انقطاع الكود البرمجي أثناء الإرسال والـ push
        $backticks = chr(96) . chr(96) . chr(96);
        $text = trim(str_replace([$backticks . 'json', $backticks], '', $text));

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
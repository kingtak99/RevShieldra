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
    // الاسم البرمجي لتشغيل الأمر عبر Artisan
    protected $signature = 'chatbot:self-learning {language=ar}';

    // وصف الأمر
    protected $description = 'Proactively generate keywords for a single language with robust fallback and retry logic for Free Tier API limits.';

    public function handle(): int
    {
        // قراءة المفتاح مباشرة من الـ Config أو الـ env كخيار احتياطي لضمان العمل
        $apiKey = config('services.gemini.key', env('GEMINI_API_KEY'));
        $model = config('services.gemini.model', env('GEMINI_MODEL', 'gemini-flash-latest'));
        $language = $this->argument('language');

        if (!$apiKey) {
            $this->warn('GEMINI_API_KEY is not configured in .env file.');
            return self::FAILURE;
        }

        $this->info("\n========================================");
        $this->info("Self-Learning for [{$language}] Language");
        $this->info("========================================");

        $flowMap = $this->buildFlowMap($language);
        $flows = array_keys($flowMap['root']['flows'] ?? []);
        $targetFlow = count($flows) > 0 ? $flows[array_rand($flows)] : null;

        if (!$targetFlow) {
            $this->info("No flows found to learn.");
            return self::SUCCESS;
        }

        $this->info("Targeting flow: {$targetFlow}");
        $this->info("Using Model: {$model}");

        // محاولة تنفيذ الطلب مع آلية إعادة محاولة ذكية عند حدوث 429 أو 503
        $response = null;
        $maxRetries = 2;
        $attempt = 0;
        $success = false;

        while ($attempt <= $maxRetries) {
            try {
                $response = $this->executeGeminiRequest($this->buildTargetedPrompt($language, $targetFlow), $model, $apiKey);

                if ($response && $response->successful()) {
                    $success = true;
                    break;
                }

                if ($response && ($response->status() === 429 || $response->status() === 503)) {
                    $attempt++;
                    if ($attempt <= $maxRetries) {
                        // وقت انتظار تصاعدي حقيقي لتخطي نافذة الدقيقة للـ Free Tier بنجاح
                        $wait = ($attempt === 1) ? rand(31, 40) : rand(41, 50);
                        $this->warn("Rate limit hit (429/503). Waiting {$wait} seconds before retry {$attempt}/{$maxRetries} to bypass Google restrictions...");
                        sleep($wait);
                        continue;
                    }
                }
                break;
            } catch (\Throwable $e) {
                Log::error("Gemini API Exception [{$language}]: " . $e->getMessage());
                break;
            }
        }

        // معالجة النتائج في حال النجاح
        if ($success && $response) {
            $expandedResults = $this->extractLearningResults($response->json());
            $count = 0;
            if ($expandedResults) {
                foreach ($expandedResults as $item) {
                    $keyword = trim((string) ($item['keyword'] ?? ''));
                    if ($keyword === '' || !$this->isValidTarget($flowMap, $targetFlow, null))
                        continue;

                    if ($this->persistLearnedKeyword($language, $this->normalize($keyword, $language), $keyword, $targetFlow, null, null, 'ai_gen', 0.9)) {
                        $count++;
                        $this->line(" <info>Added AI Keyword:</info> \"{$keyword}\"");
                    }
                }
            }
            $this->info("\n🎉 Cycle Completed successfully! Generated: {$count} keywords for flow [{$targetFlow}].");
        } else {
            // في حال فشل الاتصال بالكامل بعد المحاولات، يتم الانتقال تلقائياً للـ Fallback الذكي المخصص للتدفق المستهدف
            $errorBody = $response ? $response->body() : 'No response / Connection Timeout';
            Log::warning("Gemini API failed for [{$language}] (Resorting to Offline Fallback). Reason: " . $errorBody);

            $this->warn("\n⚠️  Gemini API is currently busy or rate-limited. Activating targeted offline fallback...");
            $this->runOfflineFallback($language, $targetFlow);
        }

        return self::SUCCESS;
    }

    /**
     * تشغيل نظام الطوارئ الذكي لتوليد كلمات مفتاحية محلية تناسب التدفق المستهدف بدقة
     */
    private function runOfflineFallback(string $language, string $flow): void
    {
        // بنية ذكية للكلمات المفتاحية المسبقة لضمان تغذية ذكية للبوت عند انقطاع الـ API
        $fallbackDatabase = [
            'ar' => [
                'setup' => ['طريقة ضبط البوت', 'إعداد النظام', 'تهيئة الحساب', 'تعديل الإعدادات'],
                'pricing' => ['كم الاشتراك', 'بكم الخدمة', 'باقات الأسعار', 'تكلفة الخدمة'],
                'support' => ['تحدث مع الدعم', 'مساعدة فنية', 'الدعم الفني', 'عندي مشكلة'],
                'platform' => ['منصة ريف شيلدرا', 'كيف تعمل المنصة', 'شرح الموقع', 'ميزات المنصة'],
                'default' => ['معاملة مشبوهة', 'عملية احتيالية', 'تحويل مشكوك فيه', 'رابط غير آمن']
            ],
            'en' => [
                'setup' => ['setup bot', 'configuration guide', 'how to configure', 'initialize system'],
                'pricing' => ['how much', 'pricing plans', 'subscription cost', 'payment options'],
                'support' => ['contact support', 'technical help', 'open a ticket', 'customer support'],
                'platform' => ['how it works', 'about platform', 'revshieldra features', 'platform demo'],
                'default' => ['suspicious link', 'fraud transaction', 'unauthorized charge', 'scam alert']
            ]
        ];

        $list = $fallbackDatabase[$language][$flow] ?? $fallbackDatabase[$language]['default'];
        $count = 0;

        foreach ($list as $keyword) {
            if ($this->persistLearnedKeyword($language, $this->normalize($keyword, $language), $keyword, $flow, null, null, 'offline_fallback', 0.75)) {
                $count++;
                $this->line(" <comment>Added Fallback Keyword:</comment> \"{$keyword}\"");
            }
        }

        if ($count > 0) {
            $this->info("✅ Offline Fallback completed! Generated {$count} offline keywords for flow [{$flow}] successfully.");
        } else {
            $this->info("ℹ️ All offline fallback keywords for [{$flow}] already exist in the database.");
        }
    }

    private function buildTargetedPrompt(string $language, string $flow): string
    {
        return "System Language: {$language}. Generate 2 unique user search queries that would trigger the flow '{$flow}'. Return ONLY JSON: {\"learning_results\":[{\"keyword\":\"...\",\"matched_flow\":\"{$flow}\"}]}";
    }

    private function executeGeminiRequest(string $prompt, string $model, string $apiKey)
    {
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        return Http::timeout(45)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'X-goog-api-key' => $apiKey
            ])
            ->post($endpoint, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'temperature' => 0.4
                ]
            ]);
    }

    private function extractLearningResults(array $body): ?array
    {
        $text = data_get($body, 'candidates.0.content.parts.0.text');
        $data = json_decode(preg_replace('/^```json\s*|\s*```$/', '', $text ?? ''), true);
        return $data['learning_results'] ?? null;
    }

    private function persistLearnedKeyword($l, $norm, $key, $flow, $branch, $resp, $src, $conf): bool
    {
        if (ChatbotLearnedKeyword::where('language', $l)->where('normalized_keyword', $norm)->exists())
            return false;
        return ChatbotLearnedKeyword::create([
            'language' => $l,
            'normalized_keyword' => $norm,
            'keyword' => $key,
            'target_flow' => $flow,
            'target_branch' => $branch,
            'custom_response' => $resp,
            'source' => $src,
            'confidence' => $conf
        ]) ? true : false;
    }

    private function buildFlowMap(string $language): array
    {
        return ChatbotFlowService::getFlows($language);
    }

    private function isValidTarget($flowMap, $flow, $branch): bool
    {
        return isset($flowMap['root']['flows'][$flow]);
    }

    private function normalize($t, $l): string
    {
        return Str::squish(mb_strtolower($t));
    }
}
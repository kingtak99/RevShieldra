<?php

use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// مسارات البوت الأساسية والـ Webhook
Route::post('/chatbot/process', [ChatbotController::class, 'handleChat'])->name('chatbot.process');
Route::post('/lemonsqueezy/webhook', [WebhookController::class, 'handle'])->name('lemonsqueezy.webhook');

// مسار تشغيل الـ Cron Jobs الآمن والمجاني
Route::get('/cron/run-command', function (Request $request) {
    // توكن سري لحماية الرابط من المتطفلين (تأكد من مطابقتك له في موقع الجدولة)
    $secretToken = 'REV_SHIELDRA_SUPER_SECURE_TOKEN_2026'; 

    if ($request->query('token') !== $secretToken) {
        return response()->json(['error' => 'غير مصرح لك بالوصول!'], 401);
    }

    $command = $request->query('command');

    // تعريف الأوامر وتمرير المعاملات كمصفوفة لضمان الاستقرار البرمجي على السيرفر
    $allowedCommands = [
        'self-learning' => [
            'signature' => 'chatbot:self-learning',
            'params' => ['language' => 'ar']
        ],
        'auto-learn' => [
            'signature' => 'chatbot:auto-learn',
            'params' => []
        ],
    ];

    if (!array_key_exists($command, $allowedCommands)) {
        return response()->json(['error' => 'الأمر المطلوب غير مدعوم أو غير معرف برمجياً'], 400);
    }

    try {
        $target = $allowedCommands[$command];

        // تشغيل الأمر بالطريقة الرسمية والمستقرة لـ Laravel
        Artisan::call($target['signature'], $target['params']);
        $output = Artisan::output();

        Log::info("Cron Job Run Successfully: " . $target['signature']);

        return response()->json([
            'status' => 'success',
            'command_run' => $target['signature'],
            'output' => trim($output)
        ]);
    } catch (\Throwable $e) {
        Log::error("Cron Job Failed: " . $command . " | Error: " . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});
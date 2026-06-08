<?php

use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/chatbot/process', [ChatbotController::class, 'handleChat'])->name('chatbot.process');

Route::post('/lemonsqueezy/webhook', [WebhookController::class, 'handle'])->name('lemonsqueezy.webhook');

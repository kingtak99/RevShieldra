<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PublicFeedbackController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\WebhookController;
use App\Http\Middleware\EnsureSubscriptionActive;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
});

Route::post('logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('subscriptions/{plan}/checkout', [SubscriptionController::class, 'checkout'])->name('subscriptions.checkout');
    Route::get('subscriptions/success', [SubscriptionController::class, 'success'])->name('subscriptions.success');
    Route::get('subscriptions/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');

    Route::middleware([EnsureSubscriptionActive::class])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('locations', [LocationController::class, 'index'])->name('locations.index');
        Route::get('locations/create', [LocationController::class, 'create'])->name('locations.create');
        Route::post('locations', [LocationController::class, 'store'])->name('locations.store');
        Route::get('locations/{location}/edit', [LocationController::class, 'edit'])->name('locations.edit');
        Route::put('locations/{location}', [LocationController::class, 'update'])->name('locations.update');
        Route::delete('locations/{location}', [LocationController::class, 'destroy'])->name('locations.destroy');
        Route::get('feedback', [FeedbackController::class, 'index'])->name('feedback.index');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export-ratings', [ReportController::class, 'exportRatings'])->name('reports.export_ratings');
        Route::get('reports/export-complaints', [ReportController::class, 'exportComplaints'])->name('reports.export_complaints');
        Route::get('team', [TeamController::class, 'index'])->name('team.index');
        Route::post('team', [TeamController::class, 'store'])->name('team.store');
    });

    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');
});

Route::get('locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// Public pages
Route::get('terms', function () {
    $locale = app()->getLocale();
    return view('pages.' . ($locale === 'ar' ? 'terms-ar' : 'terms'));
})->name('terms');

Route::get('privacy', function () {
    $locale = app()->getLocale();
    return view('pages.' . ($locale === 'ar' ? 'privacy-ar' : 'privacy'));
})->name('privacy');

Route::get('support', function () {
    $locale = app()->getLocale();
    return view('pages.' . ($locale === 'ar' ? 'support-ar' : 'support'));
})->name('support');

Route::get('review/{feedbackUrl}', [PublicFeedbackController::class, 'show'])->name('public.feedback');
Route::match(['get', 'post'], 'review/{feedbackUrl}/rate', [PublicFeedbackController::class, 'rateForm'])->name('public.feedback.rate');
Route::post('review/{feedbackUrl}', [PublicFeedbackController::class, 'submit'])->name('public.feedback.submit');

// Webhooks
Route::post('webhooks/lemon-squeezy', [WebhookController::class, 'handle'])->name('webhooks.lemon-squeezy');

Route::get('/clear-all-cache', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    return "All cache is cleared successfully!";
});

// تشغيل أمر التعلم التلقائي للـ Chatbot يدوياً
Route::get('/run-chatbot-learning', function () {
    try {
        // قمنا باستدعائه بناءً على الـ Signature الخاص بالأمر
        Artisan::call('chatbot:auto-learn');

        return response()->json([
            'status' => 'success',
            'message' => 'Chatbot auto-learning executed successfully!',
            'output' => Artisan::output()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to execute command: ' . $e->getMessage()
        ], 500);
    }
});
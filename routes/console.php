<?php

use App\Mail\TrialNotification;
use App\Models\Subscription;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('trial:send-reminders', function () {
    $now = now();

    $subscriptions = Subscription::where('status', 'active')
        ->whereNotNull('current_period_end')
        ->get();

    foreach ($subscriptions as $subscription) {
        $daysRemaining = $subscription->daysRemaining();
        $user = $subscription->user;
        $business = $user->currentBusiness() ?? $user->businessesOwned()->first();

        if (! $business) {
            continue;
        }

        if ($daysRemaining === 7 && ! $subscription->reminder_7_days_sent) {
            try {
                (new TrialNotification($user, $subscription, $business, 'seven_day'))
                    ->sendViaBrevo($user->email, $user->name);
                $subscription->update(['reminder_7_days_sent' => true]);
                $this->info("Sent 7-day reminder to {$user->email}");
            } catch (\Throwable $e) {
                $this->error("Failed to send 7-day reminder to {$user->email}: " . $e->getMessage());
            }
            continue;
        }

        if ($daysRemaining === 1 && ! $subscription->reminder_1_day_sent) {
            try {
                (new TrialNotification($user, $subscription, $business, 'one_day'))
                    ->sendViaBrevo($user->email, $user->name);
                $subscription->update(['reminder_1_day_sent' => true]);
                $this->info("Sent 1-day reminder to {$user->email}");
            } catch (\Throwable $e) {
                $this->error("Failed to send 1-day reminder to {$user->email}: " . $e->getMessage());
            }
            continue;
        }

        if ($subscription->isExpired() && ! $subscription->expiry_notice_sent) {
            $subscription->update([
                'status' => 'expired',
                'expiry_notice_sent' => true,
            ]);
            $business->update(['plan' => 'expired']);
            try {
                (new TrialNotification($user, $subscription, $business, 'expired'))
                    ->sendViaBrevo($user->email, $user->name);
                $this->info("Sent expiration notice to {$user->email}");
            } catch (\Throwable $e) {
                $this->error("Failed to send expiration notice to {$user->email}: " . $e->getMessage());
            }
            continue;
        }
    }
})->purpose('Send trial reminder and expiration emails for expiring subscriptions');

Schedule::command('trial:send-reminders')->daily();
Schedule::command('chatbot:auto-learn')->dailyAt('02:00')->withoutOverlapping();

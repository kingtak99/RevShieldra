<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            Log::info('Lemon Squeezy Webhook Payload', $request->all());

            $payload = $request->all();
            $eventName = $payload['meta']['event_name'] ?? null;

            if (!$eventName) {
                Log::warning('No event name in webhook payload');
                return response()->json(['status' => 'no_event']);
            }

            Log::info('Lemon Squeezy Webhook Received', [
                'event_type' => $eventName,
                'custom_data' => $payload['meta']['custom_data'] ?? [],
            ]);

            switch ($eventName) {
                case 'order.created':
                case 'subscription_payment_success':
                    $this->handleOrderCreated($payload);
                    break;
                case 'subscription.created':
                case 'subscription_created':
                    $this->handleSubscriptionCreated($payload);
                    break;
                case 'subscription.updated':
                case 'subscription_updated':
                    $this->handleSubscriptionUpdated($payload);
                    break;
                case 'subscription.cancelled':
                case 'subscription_cancelled':
                case 'subscription.expired':
                case 'subscription_expired':
                    $this->handleSubscriptionCancelled($payload);
                    break;
                default:
                    Log::info('Unhandled webhook event: ' . $eventName);
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }

    private function handleOrderCreated($payload)
    {
        $customData = $payload['meta']['custom_data'] ?? [];

        if (!isset($customData['billable_id'])) {
            Log::warning('Order created without billable_id in custom data');
            return;
        }

        $user = User::find((int) $customData['billable_id']);
        if (!$user) {
            Log::error('User not found', ['user_id' => $customData['billable_id']]);
            return;
        }

        $plan = null;
        if (isset($customData['plan_id'])) {
            $plan = SubscriptionPlan::find((int) $customData['plan_id']);
        }

        if (!$plan) {
            Log::error('Plan not found', ['plan_id' => $customData['plan_id'] ?? null]);
            return;
        }

        Subscription::updateOrCreate(
            ['user_id' => $user->id],
            [
                'subscription_plan_id' => $plan->id,
                'status' => 'active',
                'current_period_start' => now(),
                'current_period_end' => null,
            ]
        );

        Log::info('Subscription activated from payment success', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
        ]);
    }

    private function handleSubscriptionCreated($payload)
    {
        $customData = $payload['meta']['custom_data'] ?? [];
        $userId = $customData['billable_id'] ?? null;

        if (!$userId) {
            Log::warning('Subscription created without billable_id');
            return;
        }

        $user = User::find((int) $userId);
        if (!$user) {
            Log::error('User not found for subscription', ['user_id' => $userId]);
            return;
        }

        $plan = null;
        if (isset($customData['plan_id'])) {
            $plan = SubscriptionPlan::find((int) $customData['plan_id']);
        }

        $subscription = Subscription::firstOrNew(['user_id' => $user->id]);

        $subscription->fill([
            'subscription_plan_id' => $plan?->id,
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => null,
        ]);

        $subscription->save();

        Log::info('Subscription created for user', [
            'user_id' => $user->id,
            'plan_id' => $plan?->id,
        ]);

            // Update the user's business plan so the dashboard reflects the change
            try {
                $user = User::find((int) $userId);
                if ($user) {
                    $business = $user->businessesOwned()->first() ?? $user->businesses()->first();
                    if ($business && $plan) {
                        $business->plan = $plan->name ?? ($plan->is_free ? 'Free' : 'Paid');
                        $business->save();
                        Log::info('Business plan updated from webhook', ['business_id' => $business->id, 'plan' => $business->plan]);
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Failed to update business plan from webhook', ['error' => $e->getMessage()]);
            }
    }

    private function handleSubscriptionUpdated($payload)
    {
        $customData = $payload['meta']['custom_data'] ?? [];
        $userId = $customData['billable_id'] ?? null;

        if (!$userId) {
            Log::warning('Subscription updated without billable_id');
            return;
        }

        $subscription = Subscription::where('user_id', (int) $userId)->first();

        if ($subscription) {
            $subscription->update([
                'status' => 'active',
            ]);

            Log::info('Subscription updated', ['user_id' => $userId]);
        }
    }

    private function handleSubscriptionCancelled($payload)
    {
        $customData = $payload['meta']['custom_data'] ?? [];
        $userId = $customData['billable_id'] ?? null;

        if (!$userId) {
            Log::warning('Subscription cancelled without billable_id');
            return;
        }

        $subscription = Subscription::where('user_id', (int) $userId)->first();

        if ($subscription) {
            $subscription->update([
                'status' => 'cancelled',
            ]);

            Log::info('Subscription cancelled', ['user_id' => $userId]);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::all()
            ->unique(fn ($plan) => $plan->name . '|' . $plan->interval)
            ->values();

        return view('subscriptions.index', compact('plans'));
    }

    public function checkout(Request $request, SubscriptionPlan $plan)
    {
        $planType = $request->get('plan_type', 'monthly');

        // For free plans, use the appropriate variant ID based on plan_type
        if ($plan->is_free) {
            $variantId = $planType === 'yearly'
                ? (env('LS_VARIANT_FREE_YEARLY') ?: getenv('LS_VARIANT_FREE_YEARLY'))
                : (env('LS_VARIANT_FREE_MONTHLY') ?: getenv('LS_VARIANT_FREE_MONTHLY'));
        } else {
            $variantId = $plan->lemon_squeezy_variant_id;
        }

        if (!$variantId) {
            \Log::warning('Missing Lemon Squeezy variant id for plan', [
                'plan_id' => $plan->id,
                'is_free' => $plan->is_free,
                'plan_type' => $planType,
            ]);
            return back()->withErrors(['error' => 'Variant ID not configured for this plan']);
        }

        $user = Auth::user();

        // Create checkout session with Lemon Squeezy
        $checkout = $user->checkout($variantId, [
            'name' => $plan->name . ' (' . ucfirst($planType) . ')',
            'description' => "Subscription for {$plan->max_branches} branches",
        ], [
            'plan_id' => (string) $plan->id,
            'plan_type' => (string) $planType,
        ])
        ->redirectTo(route('dashboard', false));

        return $checkout->redirect();
    }

    public function success(Request $request)
    {
        return view('subscriptions.success');
    }

    public function cancel(Request $request)
    {
        return view('subscriptions.cancel');
    }
}

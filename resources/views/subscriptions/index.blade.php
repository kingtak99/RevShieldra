@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ __('ui.subscription_plans') }}</h1>
        <p class="text-xl text-gray-600">{{ __('ui.choose_plan') }}</p>
    </div>
    @if($errors->any())
        <div class="max-w-3xl mx-auto mb-6">
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                {{ $errors->first() }}
            </div>
        </div>
    @endif

    <div class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-8 max-w-7xl mx-auto">
        @foreach($plans as $plan)
        <div class="bg-white rounded-lg shadow-lg p-6 border border-gray-200 hover:shadow-xl transition-shadow">
            <div class="text-center">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $plan->name }}</h3>
                @if($plan->is_free)
                    <div class="text-4xl font-bold text-green-600 mb-4">{{ __('ui.free') }}</div>
                    <p class="text-gray-600 mb-6">{{ __('ui.max_branches', ['count' => $plan->max_branches]) }}</p>
                    <div class="space-y-3">
                        <form action="{{ route('subscriptions.checkout', $plan, false) }}" method="POST" class="inline-block w-full">
                            @csrf
                            <input type="hidden" name="plan_type" value="monthly">
                            <button type="submit" class="w-full bg-green-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-green-700 transition-colors text-sm">
                                {{ __('ui.start_free_trial_monthly') }}
                            </button>
                        </form>

                        <form action="{{ route('subscriptions.checkout', $plan, false) }}" method="POST" class="inline-block w-full">
                            @csrf
                            <input type="hidden" name="plan_type" value="yearly">
                            <button type="submit" class="w-full border border-green-600 text-green-600 bg-white py-3 px-4 rounded-lg font-semibold hover:bg-green-600 hover:text-white transition-colors text-sm">
                                {{ __('ui.start_free_trial_yearly') }}
                            </button>
                        </form>
                    </div>
                @else
                    <div class="text-4xl font-bold text-blue-600 mb-4">
                        @if($plan->interval === 'yearly')
                            ${{ number_format($plan->price / 12, 0) }}/month
                            <div class="text-sm text-gray-500 line-through">${{ number_format($plan->price / 10, 0) }}/month</div>
                            <div class="text-sm text-green-600 font-semibold">{{ __('ui.save_20_percent') }}</div>
                        @else
                            ${{ $plan->price }}/month
                        @endif
                    </div>
                    <p class="text-gray-600 mb-6">{{ __('ui.max_branches', ['count' => $plan->max_branches]) }}</p>

                    <form action="{{ route('subscriptions.checkout', $plan) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                            {{ __('ui.subscribe_now') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
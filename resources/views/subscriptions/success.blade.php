@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-8 text-center">
        <div class="text-green-500 mb-4">
            <svg class="w-16 h-16 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 0116 0zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ __('ui.subscription_success') }}</h1>
        <p class="text-gray-600 mb-6">{{ __('ui.thank_you_subscription') }}</p>
        <a href="{{ route('dashboard') }}" class="inline-block bg-blue-600 text-white py-2 px-6 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
            {{ __('ui.back_to_dashboard') }}
        </a>
    </div>
</div>
@endsection
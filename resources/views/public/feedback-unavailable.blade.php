@extends('layouts.guest')

@section('title', __('ui.feedback_unavailable_title'))

@section('content')
    <div class="mx-auto w-full max-w-xl rounded-[2rem] border border-slate-200 bg-white p-6 shadow-panel ring-1 ring-slate-200/80 sm:p-8">
        <div class="text-center">
            <p class="text-sm uppercase tracking-[0.35em] text-sky-600">{{ __('ui.service_notice') }}</p>
            <h1 class="mt-4 text-3xl font-semibold text-slate-900">{{ __('ui.feedback_offline_title') }}</h1>
            <p class="mt-4 text-slate-600">{{ __('ui.feedback_offline_body') }}</p>

            @if($isOwner)
                <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-left text-sm text-rose-900">
                    <strong>{{ __('ui.owner_alert_title') }}</strong>
                    <p class="mt-2">{{ __('ui.owner_alert_description') }}</p>
                </div>
            @endif

            <div class="mt-8 inline-flex flex-col items-center justify-center gap-3">
                @if(auth()->check() && $isOwner)
                    <a href="{{ route('settings.index') }}" class="rounded-full bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800 transition">{{ __('ui.go_to_dashboard') }}</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-full bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800 transition">{{ __('ui.login_to_dashboard') }}</a>
                @endif
                <p class="text-xs text-slate-500">{{ __('ui.owner_hint') }}</p>
            </div>
        </div>
    </div>
@endsection

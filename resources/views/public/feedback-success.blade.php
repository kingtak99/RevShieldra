@extends('layouts.guest')

@section('title', __('ui.thank_you_page'))

@section('content')
    <div class="rounded-[2rem] card-soft p-8 shadow-glow ring-1 ring-slate-200/80 text-center">
        <div class="mb-6 inline-flex h-20 w-20 items-center justify-center rounded-full bg-slate-50 text-emerald-600 shadow-lg shadow-emerald-500/10 mx-auto">
            <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>

        <h1 class="text-3xl font-semibold text-slate-900 mb-4">{{ __('ui.thank_you_page') }}</h1>
        <p class="text-slate-600 mb-6">{{ $message }}</p>

        <a href="{{ route('home') }}" class="inline-flex rounded-full btn-primary px-8 py-3 text-sm font-semibold shadow-xl shadow-slate-950/25">{{ __('ui.back_to_home') }}</a>
    </div>
@endsection

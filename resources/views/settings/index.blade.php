@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">
        <section class="rounded-[2rem] card-soft p-8 shadow-glow ring-1 ring-slate-200/80">
            <p class="text-sm uppercase tracking-[0.35em] text-blue-700">{{ __('ui.settings') }}</p>
            <h1 class="mt-2 text-4xl font-semibold text-slate-900">{{ __('ui.business_settings') }}</h1>
            <p class="mt-2 text-sm text-slate-600">{{ __('ui.business_plan_overview') }}</p>
        </section>

        @if (session('success'))
            <div class="rounded-[2rem] border border-emerald-200 bg-emerald-50 p-5 text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-[2rem] border border-rose-200 bg-rose-50 p-5 text-rose-800">
                {{ session('error') }}
            </div>
        @endif

        <section class="rounded-[2rem] card-soft p-6 shadow-glow ring-1 ring-slate-200/80 grid gap-4 sm:grid-cols-2">
            <div>
                <p class="text-sm uppercase tracking-[0.35em] text-slate-500">{{ __('ui.business_name') }}</p>
                <p class="mt-2 text-xl font-semibold text-slate-900">{{ $business?->name ?? __('ui.not_available') }}</p>
            </div>
            <div>
                <p class="text-sm uppercase tracking-[0.35em] text-slate-500">{{ __('ui.plan') }}</p>
                <p class="mt-2 text-xl font-semibold text-slate-900">{{ ucfirst($business?->plan ?? 'free') }}</p>
            </div>
            <div>
                <p class="text-sm uppercase tracking-[0.35em] text-slate-500">Subscription remaining</p>
                <p class="mt-2 text-xl font-semibold text-slate-900">
                    @if($daysRemaining === null)
                        --
                    @elseif($daysRemaining < 0)
                        Expired
                    @else
                        {{ $daysRemaining }} day{{ $daysRemaining === 1 ? '' : 's' }} left
                    @endif
                </p>
            </div>
        </section>

        <section class="rounded-[2rem] card-soft p-8 shadow-glow ring-1 ring-slate-200/80">
            <div class="space-y-4">
                <h2 class="text-2xl font-semibold text-slate-900">{{ __('ui.email_language') }}</h2>
                <p class="text-sm text-slate-600">{{ __('ui.email_language_help') }}</p>
            </div>

            <form method="POST" action="{{ route('settings.update') }}" class="mt-6 space-y-5">
                @csrf

                <div>
                    <label for="email_locale" class="text-sm font-medium text-slate-700">{{ __('ui.select_email_language') }}</label>
                    <select id="email_locale" name="email_locale" class="input-base mt-2 w-full">
                        <option value="en" @selected(old('email_locale', $business?->email_locale ?? 'en') === 'en')>{{ __('ui.english') }}</option>
                        <option value="ar" @selected(old('email_locale', $business?->email_locale ?? 'en') === 'ar')>{{ __('ui.arabic') }}</option>
                    </select>
                </div>

                <button type="submit" class="rounded-full btn-primary px-6 py-3 text-sm font-semibold">{{ __('ui.save_settings') }}</button>
            </form>
        </section>
    </div>
@endsection

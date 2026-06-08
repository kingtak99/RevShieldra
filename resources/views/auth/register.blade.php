@extends('layouts.app')

@section('content')
    <div class="mx-auto w-full max-w-5xl overflow-hidden rounded-[2rem] bg-white shadow-panel ring-1 ring-slate-200/80">
        <div class="grid gap-0 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="relative overflow-hidden rounded-t-[2rem] lg:rounded-l-[2rem] bg-slate-50 p-10">
                <div class="absolute -right-16 top-10 h-40 w-40 rounded-full bg-blue-200/40 blur-3xl"></div>
                <div class="absolute left-10 bottom-10 h-36 w-36 rounded-full bg-emerald-200/40 blur-3xl"></div>
                <div class="relative space-y-8">
                    <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-4 py-2 text-xs uppercase tracking-[0.35em] text-blue-700">{{ __('ui.build_trust_faster') }}</span>
                    <div class="space-y-4">
                        <h1 class="text-4xl font-semibold text-slate-900">{{ __('ui.create_your_account') }}</h1>
                        <p class="max-w-xl text-slate-600">{{ __('ui.get_polished_dashboard') }}</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-[1.75rem] card-soft p-5">
                            <p class="text-sm uppercase tracking-[0.35em] text-slate-500">{{ __('ui.launch_speed') }}</p>
                            <p class="mt-3 text-2xl font-semibold text-slate-900">{{ __('ui.five_min') }}</p>
                        </div>
                        <div class="rounded-[1.75rem] card-soft p-5">
                            <p class="text-sm uppercase tracking-[0.35em] text-slate-500">{{ __('ui.review_paths') }}</p>
                            <p class="mt-3 text-2xl font-semibold text-slate-900">{{ __('ui.smart_text') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-10">
                <div class="space-y-6">
                    <div>
                        <p class="text-sm uppercase tracking-[0.35em] text-blue-700">{{ __('ui.create_your_account') }}</p>
                        <h2 class="mt-3 text-3xl font-semibold text-slate-900">{{ __('ui.start_revshieldra_trial') }}</h2>
                    </div>
                    <form method="POST" action="{{ route('register') }}" class="space-y-5 rounded-[1.75rem] card-soft p-6 shadow-panel">
                        @csrf
                        <div>
                            <label class="text-sm font-medium text-slate-700">{{ __('ui.name') }}</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="input-base mt-2" />
                            @error('name')<p class="mt-2 text-sm text-rose-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">{{ __('ui.email') }}</label>
                            <input type="email" name="email" value="{{ old('email') }}" required class="input-base mt-2" />
                            @error('email')<p class="mt-2 text-sm text-rose-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">{{ __('ui.password') }}</label>
                            <input type="password" name="password" required class="input-base mt-2" />
                            @error('password')<p class="mt-2 text-sm text-rose-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">{{ __('ui.confirm_password') }}</label>
                            <input type="password" name="password_confirmation" required class="input-base mt-2" />
                        </div>
                        <button type="submit" class="w-full rounded-full btn-primary px-5 py-3 text-sm font-semibold">{{ __('ui.register') }}</button>
                    </form>
                    <p class="text-center text-sm text-slate-600">{{ __('ui.already_registered') }} <a href="{{ route('login') }}" class="font-semibold text-blue-700 hover:text-blue-900">{{ __('ui.sign_in_title') }}</a>.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

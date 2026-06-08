@extends('layouts.app')

@section('content')
    <div class="mx-auto w-full max-w-5xl overflow-hidden rounded-[2rem] bg-white shadow-panel ring-1 ring-slate-200/80">
        <div class="grid gap-0 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="relative overflow-hidden rounded-t-[2rem] lg:rounded-l-[2rem] bg-slate-50 p-10">
                <div class="absolute -right-16 top-10 h-40 w-40 rounded-full bg-blue-200/40 blur-3xl"></div>
                <div class="absolute left-10 bottom-10 h-36 w-36 rounded-full bg-emerald-200/40 blur-3xl"></div>
                <div class="relative space-y-8">
                    <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-4 py-2 text-xs uppercase tracking-[0.35em] text-blue-700">Review growth hub</span>
                    <div class="space-y-4">
                        <h1 class="text-4xl font-semibold text-slate-900">Welcome back.</h1>
                        <p class="max-w-xl text-slate-600">Sign in to manage your locations, track recent ratings, and route customer feedback faster than ever.</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-[1.75rem] card-soft p-5">
                            <p class="text-sm uppercase tracking-[0.35em] text-slate-500">Active location</p>
                            <p class="mt-3 text-2xl font-semibold text-slate-900">Downtown</p>
                        </div>
                        <div class="rounded-[1.75rem] card-soft p-5">
                            <p class="text-sm uppercase tracking-[0.35em] text-slate-500">Today’s reviews</p>
                            <p class="mt-3 text-2xl font-semibold text-slate-900">34</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-10">
                <div class="space-y-6">
                    <div>
                        <p class="text-sm uppercase tracking-[0.35em] text-blue-700">{{ __('ui.secure_access') }}</p>
                        <h2 class="mt-3 text-3xl font-semibold text-slate-900">{{ __('ui.sign_in_revshieldra') }}</h2>
                    </div>
                    <form method="POST" action="{{ route('login') }}" class="space-y-5 rounded-[1.75rem] card-soft p-6 shadow-panel">
                        @csrf
                        <div>
                            <label class="text-sm font-medium text-slate-700">{{ __('ui.email') }}</label>
                            <input type="email" name="email" value="{{ old('email') }}" required autofocus class="input-base mt-2" />
                            @error('email')<p class="mt-2 text-sm text-rose-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">{{ __('ui.password') }}</label>
                            <input type="password" name="password" required class="input-base mt-2" />
                            @error('password')<p class="mt-2 text-sm text-rose-400">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="w-full rounded-full btn-primary px-5 py-3 text-sm font-semibold">{{ __('ui.sign_in_title') }}</button>
                    </form>
                    <p class="text-center text-sm text-slate-600">{{ __('ui.new_here') }} <a href="{{ route('register') }}" class="font-semibold text-blue-700 hover:text-blue-900">{{ __('ui.create_your_account') }}</a>.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

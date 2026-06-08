@extends('layouts.app')

@section('content')
    <section class="relative overflow-hidden rounded-[2.5rem] bg-white/95 px-6 py-12 shadow-panel ring-1 ring-slate-200/80 sm:px-10 lg:px-16">
        <div class="hero-deco"></div>
        <div class="relative mx-auto grid gap-12 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
            <div class="space-y-8">
                <div class="inline-flex items-center gap-2 rounded-full border border-blue-200/80 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 shadow-sm">
                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                    {{ __('ui.built_for_multi_location_teams') }}
                </div>
                <div class="space-y-6">
                    <p class="text-sm uppercase tracking-[0.35em] text-slate-500">RevShieldra</p>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-5xl">{{ __('ui.hero_headline') }}</h1>
                    <p class="max-w-2xl text-lg leading-8 text-slate-600">{{ __('ui.hero_subtitle') }}</p>
                </div>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-full btn-primary px-8 py-3 text-sm font-semibold shadow-glow">{{ __('ui.start_free') }}</a>
                    <a href="#features" class="inline-flex items-center justify-center rounded-full btn-secondary px-8 py-3 text-sm font-semibold">{{ __('ui.view_features') }}</a>
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-[1.75rem] card-soft p-5">
                        <p class="text-3xl font-semibold text-slate-900">24/7</p>
                        <p class="mt-2 text-sm text-slate-500">{{ __('ui.review_capture') }}</p>
                    </div>
                    <div class="rounded-[1.75rem] card-soft p-5">
                        <p class="text-3xl font-semibold text-slate-900">98%</p>
                        <p class="mt-2 text-sm text-slate-500">{{ __('ui.response_rate') }}</p>
                    </div>
                    <div class="rounded-[1.75rem] card-soft p-5">
                        <p class="text-3xl font-semibold text-slate-900">10+</p>
                        <p class="mt-2 text-sm text-slate-500">{{ __('ui.trusted_businesses') }}</p>
                    </div>
                </div>
            </div>

            <div class="relative">
                <div class="absolute -right-16 top-10 h-40 w-40 rounded-full bg-sky-200/40 blur-3xl"></div>
                <div class="absolute -left-10 bottom-10 h-44 w-44 rounded-full bg-emerald-200/40 blur-3xl"></div>
                <div class="relative rounded-[2rem] card-soft p-6">
                    <div class="mb-6 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="status-indicator"></span>
                            <div>
                                <p class="text-xs uppercase tracking-[0.35em] text-slate-500">{{ __('ui.live_overview') }}</p>
                                <h2 class="mt-2 text-xl font-semibold text-slate-900">{{ __('ui.location_insights') }}</h2>
                            </div>
                        </div>
                        <div class="rounded-full bg-slate-100 px-4 py-2 text-xs text-slate-600">{{ __('ui.updated_now') }}</div>
                    </div>
                    <div class="space-y-5">
                        <div class="rounded-[1.75rem] card-soft p-5">
                            <div class="flex items-center justify-between text-sm text-slate-500">
                                <span>{{ __('ui.downtown_branch') }}</span>
                                <span class="text-emerald-600">4.9 ⭐</span>
                            </div>
                            <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-200 shadow-[0_0_30px_rgba(59,130,246,0.08)]">
                                <div class="h-full w-4/5 rounded-full bg-gradient-to-r from-blue-500 to-cyan-400 shadow-[0_0_18px_rgba(59,130,246,0.2)]"></div>
                            </div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-[1.75rem] card-soft p-5">
                                <p class="text-sm text-slate-500">{{ __('ui.weekly_reviews') }}</p>
                                <p class="mt-4 text-2xl font-semibold text-slate-900">143</p>
                                <p class="mt-2 text-xs text-slate-500">{{ __('ui.steady_growth_this_week') }}</p>
                            </div>
                            <div class="rounded-[1.75rem] card-soft p-5">
                                <p class="text-sm text-slate-500">{{ __('ui.private_concerns') }}</p>
                                <p class="mt-4 text-2xl font-semibold text-slate-900">26</p>
                                <p class="mt-2 text-xs text-slate-500">{{ __('ui.escalated_privately') }}</p>
                            </div>
                        </div>
                        <div class="rounded-[1.75rem] bg-blue-50 p-5 ring-1 ring-blue-100">
                            <p class="text-sm text-slate-600">{{ __('ui.review_board_note') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="how-it-works" class="mt-20 grid gap-6 md:grid-cols-3">
        <div class="rounded-[2rem] card-soft p-8">
            <p class="text-3xl font-semibold text-blue-700">01</p>
            <h3 class="mt-6 text-2xl font-semibold text-slate-900">{{ __('ui.launch_your_flow') }}</h3>
            <p class="mt-4 text-slate-600">{{ __('ui.launch_your_flow_description') }}</p>
        </div>
        <div class="rounded-[2rem] card-soft p-8">
            <p class="text-3xl font-semibold text-blue-700">02</p>
            <h3 class="mt-6 text-2xl font-semibold text-slate-900">{{ __('ui.capture_every_rating') }}</h3>
            <p class="mt-4 text-slate-600">{{ __('ui.capture_every_rating_description') }}</p>
        </div>
        <div class="rounded-[2rem] card-soft p-8">
            <p class="text-3xl font-semibold text-blue-700">03</p>
            <h3 class="mt-6 text-2xl font-semibold text-slate-900">{{ __('ui.send_fast_reports') }}</h3>
            <p class="mt-4 text-slate-600">{{ __('ui.send_fast_reports_description') }}</p>
        </div>
    </section>

    <section id="features" class="mt-20 rounded-[2.5rem] bg-white/95 p-10 shadow-panel ring-1 ring-slate-200/80">
        <div class="text-center">
            <p class="text-sm uppercase tracking-[0.35em] text-slate-500">{{ __('ui.feature_spotlight') }}</p>
            <h2 class="mt-4 text-3xl font-semibold text-slate-900">{{ __('ui.every_tool_your_team_needs') }}</h2>
        </div>
        <div class="mt-12 grid gap-6 lg:grid-cols-3">
            <div class="rounded-[2rem] card-soft p-7">
                <p class="text-lg font-semibold text-slate-900">{{ __('ui.location_specific_qr_pages') }}</p>
                <p class="mt-4 text-slate-600">{{ __('ui.location_specific_qr_pages_description') }}</p>
            </div>
            <div class="rounded-[2rem] card-soft p-7">
                <p class="text-lg font-semibold text-slate-900">{{ __('ui.automated_sentiment_routing') }}</p>
                <p class="mt-4 text-slate-600">{{ __('ui.automated_sentiment_routing_description') }}</p>
            </div>
            <div class="rounded-[2rem] card-soft p-7">
                <p class="text-lg font-semibold text-slate-900">{{ __('ui.team_access_reports') }}</p>
                <p class="mt-4 text-slate-600">{{ __('ui.team_access_reports_description') }}</p>
            </div>
        </div>
    </section>

    <section id="pricing" class="mt-20">
        <div class="text-center">
            <p class="text-sm uppercase tracking-[0.35em] text-slate-500">{{ __('ui.choose_growth_plan') }}</p>
            <h2 class="mt-4 text-3xl font-semibold text-slate-900">{{ __('ui.scale_reviews_across_every_location') }}</h2>
        </div>
        <div class="mt-12 grid gap-6 lg:grid-cols-3">
            <div class="rounded-[2rem] card-soft p-8 text-center">
                <p class="text-sm uppercase tracking-[0.35em] text-slate-500">{{ __('ui.free') }}</p>
                <p class="mt-4 text-3xl font-semibold text-slate-900">1</p>
                <p class="mt-2 text-sm text-slate-500">{{ __('ui.location') }}</p>
                <ul class="mt-8 space-y-3 text-left text-slate-600">
                    <li>{{ __('ui.qr_review_flow') }}</li>
                    <li>{{ __('ui.basic_analytics') }}</li>
                </ul>
            </div>
            <div class="rounded-[2rem] border border-blue-200 bg-blue-50 p-8 text-center shadow-panel">
                <p class="text-sm uppercase tracking-[0.35em] text-blue-700">{{ __('ui.growing') }}</p>
                <p class="mt-4 text-3xl font-semibold text-slate-900">3</p>
                <p class="mt-2 text-sm text-slate-600">{{ __('ui.locations') }}</p>
                <ul class="mt-8 space-y-3 text-left text-slate-700">
                    <li>{{ __('ui.all_free_features') }}</li>
                    <li>{{ __('ui.multi_location_reports') }}</li>
                    <li>{{ __('ui.team_permissions') }}</li>
                </ul>
            </div>
            <div class="rounded-[2rem] card-soft p-8 text-center">
                <p class="text-sm uppercase tracking-[0.35em] text-slate-500">{{ __('ui.pro') }}</p>
                <p class="mt-4 text-3xl font-semibold text-slate-900">10+</p>
                <p class="mt-2 text-sm text-slate-500">{{ __('ui.locations') }}</p>
                <ul class="mt-8 space-y-3 text-left text-slate-600">
                    <li>{{ __('ui.advanced_analytics') }}</li>
                    <li>{{ __('ui.export_tools') }}</li>
                    <li>{{ __('ui.priority_support') }}</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="mt-20 rounded-[2.5rem] bg-white/95 p-10 text-center shadow-panel ring-1 ring-slate-200/80">
        <h2 class="text-3xl font-semibold text-slate-900">{{ __('ui.launch_your_first_location') }}</h2>
        <p class="mx-auto mt-4 max-w-2xl text-slate-600">{{ __('ui.use_revshieldra_to_collect_feedback') }}</p>
        <p class="mx-auto mt-4 max-w-2xl text-sm text-slate-500">{{ __('ui.no_credit_card_required') }}</p>
        <a href="{{ route('register') }}" class="mt-8 inline-flex rounded-full btn-primary px-10 py-4 text-base font-semibold">{{ __('ui.start_your_free_trial') }}</a>
    </section>
@endsection

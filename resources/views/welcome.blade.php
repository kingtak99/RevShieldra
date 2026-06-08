@extends('layouts.guest')

@section('title', 'RevShieldra | Welcome')

@section('content')
<div class="grid gap-10 lg:grid-cols-[1.05fr_0.95fr] items-start">
    <section class="space-y-8">
        <div class="inline-flex items-center gap-3 rounded-full border border-blue-200/80 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 shadow-sm">
            <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
            New site identity — clean, professional, and calm.
        </div>

        <div class="space-y-6">
            <p class="text-sm uppercase tracking-[0.35em] text-slate-500">RevShieldra</p>
            <h1 class="text-5xl font-semibold tracking-tight text-slate-900 sm:text-6xl">A sharper review experience for every team and location.</h1>
            <p class="max-w-2xl text-lg leading-8 text-slate-600">Reinvent how customers give feedback with a calm, easy-to-read interface and a dashboard designed around trust, clarity, and action.</p>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-full btn-primary px-8 py-3 text-sm font-semibold shadow-glow">Create account</a>
            <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full btn-secondary px-8 py-3 text-sm font-semibold">Sign in</a>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-[1.75rem] card-soft p-5">
                <p class="text-3xl font-semibold text-slate-900">12K+</p>
                <p class="mt-2 text-sm text-slate-500">Daily review captures</p>
            </div>
            <div class="rounded-[1.75rem] card-soft p-5">
                <p class="text-3xl font-semibold text-slate-900">4.9⭐</p>
                <p class="mt-2 text-sm text-slate-500">Average location score</p>
            </div>
            <div class="rounded-[1.75rem] card-soft p-5">
                <p class="text-3xl font-semibold text-slate-900">5+</p>
                <p class="mt-2 text-sm text-slate-500">Tools in one panel</p>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-[2rem] card-soft p-7 shadow-panel">
                <p class="text-sm uppercase tracking-[0.35em] text-slate-500">Built for teams</p>
                <p class="mt-4 text-slate-700">Invite managers and staff, assign roles, and keep every location aligned without extra complexity.</p>
            </div>
            <div class="rounded-[2rem] card-soft p-7 shadow-panel">
                <p class="text-sm uppercase tracking-[0.35em] text-slate-500">Instant insights</p>
                <p class="mt-4 text-slate-700">See rating trends, flagged complaints, and review volume from a single control center.</p>
            </div>
        </div>
    </section>

    <aside class="space-y-6 lg:sticky lg:top-10">
        <div class="relative overflow-hidden rounded-[2rem] border border-slate-200 bg-white p-6 shadow-panel">
            <div class="absolute -right-10 top-6 h-28 w-28 rounded-full bg-blue-200/30 blur-3xl"></div>
            <div class="absolute -left-10 bottom-6 h-28 w-28 rounded-full bg-emerald-200/30 blur-3xl"></div>

            <div class="relative rounded-[1.75rem] card-soft p-6">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.35em] text-slate-500">Live preview</p>
                        <h2 class="mt-2 text-xl font-semibold text-slate-900">Brand dashboard</h2>
                    </div>
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs text-emerald-700">Active</span>
                </div>

                <div class="space-y-4">
                    <div class="rounded-[1.75rem] card-soft p-5">
                        <div class="flex items-center justify-between text-sm text-slate-500">
                            <span>City Center</span>
                            <span class="text-emerald-600">4.8 ⭐</span>
                        </div>
                        <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-200">
                            <div class="h-full w-4/5 rounded-full bg-gradient-to-r from-blue-500 to-cyan-400"></div>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-[1.75rem] card-soft p-4 text-sm text-slate-700">
                            <p class="font-semibold text-slate-900">Review volume</p>
                            <p class="mt-2">1.2k this week</p>
                        </div>
                        <div class="rounded-[1.75rem] card-soft p-4 text-sm text-slate-700">
                            <p class="font-semibold text-slate-900">Private tickets</p>
                            <p class="mt-2">23 open</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-[2rem] card-soft p-6 shadow-panel">
            <p class="text-sm uppercase tracking-[0.35em] text-slate-500">Why this style</p>
            <p class="mt-4 text-slate-600">This layout uses soft light surfaces, subtle accent color, and a calm visual hierarchy so the product feels clear, trustworthy, and effortless.</p>
        </div>

        <div class="rounded-[2rem] border border-white/10 bg-gradient-to-br from-cyan-500/10 via-slate-950/60 to-amber-500/10 p-6 shadow-panel">
            <p class="text-sm uppercase tracking-[0.35em] text-slate-400">Quick links</p>
            <ul class="mt-4 space-y-3 text-slate-600">
                <li><a href="{{ route('login') }}" class="font-semibold text-blue-700 hover:text-blue-900">Sign in to explore</a></li>
                <li><a href="{{ route('register') }}" class="font-semibold text-blue-700 hover:text-blue-900">Create your first location</a></li>
                <li><a href="{{ route('home') }}" class="font-semibold text-blue-700 hover:text-blue-900">Return to landing</a></li>
            </ul>
        </div>
    </aside>
</div>
@endsection

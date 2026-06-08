<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#050817">
    <title>{{ config('app.name', 'RevShieldra') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        twilight: '#f8fafc',
                        ember: '#2563eb',
                        aurora: '#1e293b',
                        glow: '#10b981',
                        surface: '#ffffff',
                    },
                    boxShadow: {
                        glow: '0 24px 80px rgba(37, 99, 235, 0.16)',
                        panel: '0 20px 50px rgba(15, 23, 42, 0.08)',
                    },
                },
            },
        };
    </script>
    <style>
        :root {
            color-scheme: light;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f3f4f6;
            color: #1E293B;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            background-image:
                radial-gradient(circle at 12% 12%, rgba(37, 99, 235, 0.12), transparent 18%),
                radial-gradient(circle at 90% 8%, rgba(16, 185, 129, 0.12), transparent 20%),
                radial-gradient(circle at 60% 80%, rgba(99, 102, 241, 0.08), transparent 22%);
            z-index: -1;
        }

        .surface {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(148, 163, 184, 0.18);
            box-shadow: 0 24px 90px rgba(15, 23, 42, 0.08), 0 0 0 1px rgba(148, 163, 184, 0.08);
        }

        .input-base {
            width: 100%;
            border-radius: 1rem;
            border: 1px solid rgba(148, 163, 184, 0.32);
            background: #ffffff;
            color: #1E293B;
            padding: 1rem 1.1rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .input-base:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.14);
        }

        .btn-primary {
            background: #2563eb;
            color: #ffffff;
            box-shadow: 0 16px 30px rgba(37, 99, 235, 0.18);
            transition: transform 0.2s ease, filter 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
            filter: brightness(1.05);
            box-shadow: 0 20px 36px rgba(37, 99, 235, 0.22);
        }

        .btn-secondary {
            color: #1E293B;
            background: rgba(37, 99, 235, 0.08);
            border: 1px solid rgba(37, 99, 235, 0.18);
            transition: transform 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-secondary:hover {
            background: rgba(37, 99, 235, 0.12);
            transform: translateY(-1px);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.08);
        }

        .navbar-link {
            color: #1E293B;
            transition: color 0.2s ease;
        }

        .navbar-link:hover {
            color: #2563eb;
        }

        .hero-deco {
            position: absolute;
            inset: 0;
            z-index: -1;
            background-image:
                radial-gradient(circle at 10% 20%, rgba(37, 99, 235, 0.12), transparent 20%),
                radial-gradient(circle at 80% 10%, rgba(16, 185, 129, 0.1), transparent 22%),
                radial-gradient(circle at 50% 70%, rgba(99, 102, 241, 0.08), transparent 20%);
        }

        .card-soft {
            background: #ffffff;
            border: none;
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.08), 0 8px 30px rgba(37, 99, 235, 0.04);
        }

        .status-indicator {
            display: inline-flex;
            width: 0.75rem;
            height: 0.75rem;
            border-radius: 9999px;
            background: #10B981;
            box-shadow: 0 0 0 6px rgba(16, 185, 129, 0.12);
            margin-right: 0.75rem;
        }

        .info-text {
            color: #475569;
        }

        .bg-slate-900\/90,
        .bg-slate-950\/90,
        .bg-slate-950\/95,
        .bg-slate-950\/85,
        .bg-slate-900\/95,
        .bg-slate-900\/80,
        .bg-slate-950\/80 {
            background: #ffffff !important;
            border-color: rgba(148, 163, 184, 0.18) !important;
            color: #1E293B !important;
        }

        .shadow-panel {
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.08) !important;
        }

        .shadow-glow {
            box-shadow: 0 8px 30px rgba(37, 99, 235, 0.12) !important;
        }

        .border-white\/10 {
            border-color: rgba(148, 163, 184, 0.18) !important;
        }
    </style>
</head>

<body class="min-h-screen text-slate-900 antialiased">
    <div class="sticky top-0 z-40 border-b border-slate-200/70 bg-slate-50/95 backdrop-blur-xl">
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                    <img src="{{ asset('images/revshieldra-logo.png') }}" alt="RevShieldra Logo" width="180"
                        class="img-fluid">
                </a>

                <nav class="hidden items-center gap-3 text-sm text-slate-700 md:flex">
                    @auth
                        @php
                            $currentUser = Auth::user();
                            $currentBusiness = $currentUser->currentBusiness();
                        @endphp

                        @if ($currentBusiness && $currentUser->canAccessPage('dashboard', $currentBusiness))
                            <a href="{{ route('dashboard') }}" class="rounded-full px-4 py-2 transition navbar-link">{{ __('ui.dashboard') }}</a>
                        @endif

                        @if ($currentBusiness && $currentUser->canAccessPage('locations', $currentBusiness))
                            <a href="{{ route('locations.index') }}" class="rounded-full px-4 py-2 transition navbar-link">{{ __('ui.locations') }}</a>
                        @endif

                        @if ($currentBusiness && $currentUser->canAccessPage('feedback', $currentBusiness))
                            <a href="{{ route('feedback.index') }}" class="rounded-full px-4 py-2 transition navbar-link">{{ __('ui.feedback') }}</a>
                        @endif

                        @if ($currentBusiness && $currentUser->canAccessPage('reports', $currentBusiness))
                            <a href="{{ route('reports.index') }}" class="rounded-full px-4 py-2 transition navbar-link">{{ __('ui.reports') }}</a>
                        @endif

                        @if ($currentBusiness && $currentUser->isOwnerOfBusiness($currentBusiness))
                            <a href="{{ route('team.index') }}" class="rounded-full px-4 py-2 transition navbar-link">{{ __('ui.team') }}</a>
                        @endif

                        @if ($currentBusiness && $currentUser->canAccessPage('settings', $currentBusiness))
                            <a href="{{ route('settings.index') }}" class="rounded-full px-4 py-2 transition navbar-link">{{ __('ui.settings') }}</a>
                        @endif

                        <a href="{{ route('subscriptions.index') }}" class="rounded-full px-4 py-2 transition navbar-link">{{ __('ui.pricing') }}</a>
                    @else
                        <a href="{{ route('home') }}" class="rounded-full px-4 py-2 transition navbar-link">{{ __('ui.home') }}</a>
                        <a href="#features" class="rounded-full px-4 py-2 transition navbar-link">{{ __('ui.features') }}</a>
                        <a href="{{ route('subscriptions.index') }}" class="rounded-full px-4 py-2 transition navbar-link">{{ __('ui.pricing') }}</a>
                    @endauth
                </nav>

                <div class="flex items-center gap-3">
                    <div class="hidden items-center gap-2 rounded-full border border-slate-200 bg-white/90 px-2 py-1 text-sm md:flex">
                        <a href="{{ route('locale.switch', ['locale' => 'en', 'redirect' => url()->full()]) }}" class="rounded-full px-3 py-2 {{ app()->getLocale() === 'en' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">EN</a>
                        <a href="{{ route('locale.switch', ['locale' => 'ar', 'redirect' => url()->full()]) }}" class="rounded-full px-3 py-2 {{ app()->getLocale() === 'ar' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">AR</a>
                    </div>

                    <button id="mobileMenuButton" type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 transition hover:bg-slate-100 md:hidden"
                        aria-expanded="false" aria-controls="mobileMenu">
                        <span class="sr-only">Open menu</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                            <path d="M4 7h16M4 12h16M4 17h16" />
                        </svg>
                    </button>

                    @if (request()->routeIs('analysis'))
                        <span class="hidden rounded-full border border-slate-200 bg-white/90 px-4 py-2 text-sm text-slate-700 md:inline-flex">hasantak99@gmail.com</span>
                    @endif

                    @auth
                        <form method="POST" action="{{ route('logout') }}" class="inline-block">
                            @csrf
                            <button type="submit"
                                class="rounded-full btn-secondary px-4 py-2 text-sm font-medium">{{ __('ui.logout') }}</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}"
                            class="rounded-full btn-secondary px-4 py-2 text-sm font-medium">{{ __('ui.sign_in') }}</a>
                        <a href="{{ route('register') }}"
                            class="rounded-full btn-primary px-5 py-2.5 text-sm font-semibold">{{ __('ui.start_free') }}</a>
                    @endauth
                </div>
            </div>

            <div id="mobileMenu" class="hidden border-t border-slate-200/80 bg-slate-50/95 px-4 pb-4 pt-3 md:hidden">
                <nav class="flex flex-col gap-2 text-sm text-slate-700">
                    @auth
                        @php
                            $currentUser = Auth::user();
                            $currentBusiness = $currentUser->currentBusiness();
                        @endphp

                        @if ($currentBusiness && $currentUser->canAccessPage('dashboard', $currentBusiness))
                            <a href="{{ route('dashboard') }}" class="rounded-full px-4 py-2 transition navbar-link">{{ __('ui.dashboard') }}</a>
                        @endif
                        @if ($currentBusiness && $currentUser->canAccessPage('locations', $currentBusiness))
                            <a href="{{ route('locations.index') }}" class="rounded-full px-4 py-2 transition navbar-link">{{ __('ui.locations') }}</a>
                        @endif
                        @if ($currentBusiness && $currentUser->canAccessPage('feedback', $currentBusiness))
                            <a href="{{ route('feedback.index') }}" class="rounded-full px-4 py-2 transition navbar-link">{{ __('ui.feedback') }}</a>
                        @endif
                        @if ($currentBusiness && $currentUser->canAccessPage('reports', $currentBusiness))
                            <a href="{{ route('reports.index') }}" class="rounded-full px-4 py-2 transition navbar-link">{{ __('ui.reports') }}</a>
                        @endif
                        @if ($currentBusiness && $currentUser->isOwnerOfBusiness($currentBusiness))
                            <a href="{{ route('team.index') }}" class="rounded-full px-4 py-2 transition navbar-link">{{ __('ui.team') }}</a>
                        @endif
                        @if ($currentBusiness && $currentUser->canAccessPage('settings', $currentBusiness))
                            <a href="{{ route('settings.index') }}" class="rounded-full px-4 py-2 transition navbar-link">{{ __('ui.settings') }}</a>
                        @endif
                        <a href="{{ route('subscriptions.index') }}" class="rounded-full px-4 py-2 transition navbar-link">{{ __('ui.pricing') }}</a>
                    @else
                        <a href="{{ route('home') }}" class="rounded-full px-4 py-2 transition navbar-link">{{ __('ui.home') }}</a>
                        <a href="#features" class="rounded-full px-4 py-2 transition navbar-link">{{ __('ui.features') }}</a>
                        <a href="#pricing" class="rounded-full px-4 py-2 transition navbar-link">{{ __('ui.pricing') }}</a>
                    @endauth
                </nav>
                <div class="mt-4 flex flex-col gap-3">
                    <div class="flex items-center gap-2 rounded-full border border-slate-200 bg-white/90 px-2 py-1 text-sm md:hidden">
                        <a href="{{ route('locale.switch', ['locale' => 'en', 'redirect' => url()->full()]) }}" class="rounded-full px-3 py-2 {{ app()->getLocale() === 'en' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">EN</a>
                        <a href="{{ route('locale.switch', ['locale' => 'ar', 'redirect' => url()->full()]) }}" class="rounded-full px-3 py-2 {{ app()->getLocale() === 'ar' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">AR</a>
                    </div>
                    @auth
                        <form method="POST" action="{{ route('logout') }}" class="inline-block w-full">
                            @csrf
                            <button type="submit"
                                class="w-full rounded-full btn-secondary px-4 py-2 text-sm font-medium">{{ __('ui.logout') }}</button>
                        </form>
                    @else
                        @if (auth()->check() && auth()->user()->email === 'hasantak99@gmail.com' && request()->routeIs('analysis'))
                            <div class="rounded-full border border-slate-200 bg-white/90 px-4 py-2 text-sm text-slate-700">hasantak99@gmail.com</div>
                        @endif
                        <a href="{{ route('login') }}" class="inline-flex w-full items-center justify-center rounded-full btn-secondary px-4 py-2 text-sm font-medium">{{ __('ui.sign_in') }}</a>
                        <a href="{{ route('register') }}" class="inline-flex w-full items-center justify-center rounded-full btn-primary px-4 py-2 text-sm font-semibold">{{ __('ui.start_free') }}</a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <main class="space-y-10">@yield('content')</main>
    </div>

    <footer class="border-t border-slate-200/80 bg-white py-10 text-slate-600">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} RevShieldra. {{ __('ui.smart_review_growth') }}</p>
                <div class="flex flex-wrap items-center gap-6 text-slate-500">
                    <a href="{{ route('terms') }}" class="hover:text-slate-700 transition">{{ __('ui.terms') }}</a>
                    <a href="{{ route('privacy') }}" class="hover:text-slate-700 transition">{{ __('ui.privacy') }}</a>
                    <a href="{{ route('support') }}" class="hover:text-slate-700 transition">{{ __('ui.support') }}</a>
                    <a href="mailto:info.zaynix@gmail.com" class="hover:text-slate-700 transition">{{ __('ui.contact') }}</a>
                </div>
            </div>
        </div>
    </footer>

    @include('partials.chatbot-widget')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const menuButton = document.getElementById('mobileMenuButton');
            const mobileMenu = document.getElementById('mobileMenu');

            if (menuButton && mobileMenu) {
                menuButton.addEventListener('click', function () {
                    mobileMenu.classList.toggle('hidden');
                    const expanded = mobileMenu.classList.contains('hidden') ? 'false' : 'true';
                    menuButton.setAttribute('aria-expanded', expanded);
                });
            }
        });
    </script>
</body>

</html>

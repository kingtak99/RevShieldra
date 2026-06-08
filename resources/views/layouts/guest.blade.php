<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#050817">
    <title>@yield('title', 'RevShieldra')</title>
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
            border: 1px solid rgba(148, 163, 184, 0.22);
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.06);
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
    </style></head>

<body class="min-h-screen text-slate-900 antialiased">
    <div class="min-h-screen flex items-center justify-center px-4 py-10 sm:px-6">
        <div class="w-full max-w-3xl overflow-hidden rounded-[2rem] border border-white/10 bg-white shadow-panel ring-1 ring-slate-200/50">
            <header
                class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 bg-slate-50/95 px-4 py-4 sm:px-6">
                <div class="inline-flex items-center gap-3">
                    <img src="{{ asset('images/revshieldra-logo.png') }}" alt="RevShieldra Logo" width="180"
                        class="img-fluid">
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('home') }}" class="rounded-full btn-secondary px-4 py-2 text-sm font-medium">{{ __('ui.back_to_home') }}</a>
                    <div class="inline-flex rounded-full border border-slate-200 bg-white/90 p-1 text-sm">
                        <a href="{{ route('locale.switch', ['locale' => 'en', 'redirect' => url()->full()]) }}" class="rounded-full px-3 py-2 {{ app()->getLocale() === 'en' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">EN</a>
                        <a href="{{ route('locale.switch', ['locale' => 'ar', 'redirect' => url()->full()]) }}" class="rounded-full px-3 py-2 {{ app()->getLocale() === 'ar' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">AR</a>
                    </div>
                </div>
            </header>

            <div class="p-8">
                @yield('content')
            </div>
        </div>
    </div>

    @include('partials.chatbot-widget')
</body>

</html>

@extends('layouts.app')

@section('content')
    <section class="rounded-3xl border border-slate-200/80 bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800 p-10 text-white shadow-glow">
        <div class="mx-auto max-w-6xl">
            <div class="grid gap-10 lg:grid-cols-[1.4fr_0.8fr] lg:items-center">
                <div class="space-y-6">
                    <p class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-4 py-2 text-xs uppercase tracking-[0.24em] text-slate-300 shadow-sm">Admin Chatbot Intelligence</p>
                    <h1 class="text-4xl font-semibold leading-tight sm:text-5xl">Analysis for hasantak99@gmail.com</h1>
                    <p class="max-w-2xl text-slate-300">لوحة تحكم التحليل الخاصة بالمسؤول تعرض بيانات الدردشة، حالات fallback، تذاكر الدعم، والرسائل غير المفهومة مع تفاصيل المستخدم ومستوى الأداء.</p>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="rounded-3xl bg-slate-900/80 p-5 shadow-panel">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Sessions</p>
                            <p class="mt-3 text-2xl font-semibold">{{ number_format($uniqueSessions) }}</p>
                            <p class="mt-2 text-sm text-slate-300">جلسات الدردشة الفريدة المسجلة.</p>
                        </div>
                        <div class="rounded-3xl bg-slate-900/80 p-5 shadow-panel">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">User messages</p>
                            <p class="mt-3 text-2xl font-semibold">{{ number_format($totalUserMessages) }}</p>
                            <p class="mt-2 text-sm text-slate-300">رسائل المستخدمين التي تم تسجيلها.</p>
                        </div>
                        <div class="rounded-3xl bg-slate-900/80 p-5 shadow-panel">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Ticket requests</p>
                            <p class="mt-3 text-2xl font-semibold">{{ number_format($totalTicketRequests) }}</p>
                            <p class="mt-2 text-sm text-slate-300">طلبات الدعم البشري المرسلة من البوت.</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-4xl border border-white/10 bg-white/5 p-8 shadow-2xl backdrop-blur-xl">
                    <div class="space-y-5">
                        <div>
                            <p class="text-sm uppercase tracking-[0.2em] text-slate-400">Owner</p>
                            <h2 class="mt-3 text-3xl font-semibold">hasantak99@gmail.com</h2>
                            <p class="mt-2 text-sm text-slate-300">هذه الصفحة حصرية لك فقط، وتوفر تحليلاً دقيقاً لأداء chatbot وسجل التذاكر.</p>
                        </div>

                        <div class="grid gap-4">
                            <div class="rounded-3xl bg-slate-950/80 p-5">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Bot messages</p>
                                <p class="mt-3 text-2xl font-semibold">{{ number_format($totalBotMessages) }}</p>
                                <p class="mt-2 text-sm text-slate-400">الردود التي أرسلها البوت.</p>
                            </div>
                            <div class="rounded-3xl bg-slate-950/80 p-5">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Language split</p>
                                <p class="mt-3 text-2xl font-semibold">{{ count($languageSummary) }}</p>
                                <p class="mt-2 text-sm text-slate-400">لغات مصنفة في سجل المحادثة.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-10 grid gap-8 lg:grid-cols-3">
        <div class="rounded-3xl border border-slate-200/70 bg-white p-8 shadow-sm">
            <h2 class="text-xl font-semibold">Chatbot performance</h2>
            <p class="mt-4 text-sm text-slate-500">نظرة تحليلية على الفعالية العامة للدردشة ودرجة التفاعل.</p>

            <div class="mt-8 space-y-5">
                <div>
                    <p class="text-sm uppercase tracking-[0.2em] text-slate-400">User vs bot message ratio</p>
                    <div class="mt-3 h-5 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-5 rounded-full bg-emerald-500" style="width: {{ $totalUserMessages > 0 ? min(100, round($totalBotMessages / max(1, $totalUserMessages) * 100)) : 0 }}%;"></div>
                    </div>
                    <p class="mt-2 text-sm text-slate-500">{{ $totalBotMessages }} bot messages for {{ $totalUserMessages }} user messages.</p>
                </div>

                <div>
                    <p class="text-sm uppercase tracking-[0.2em] text-slate-400">Fallback / support triggers</p>
                    <div class="mt-3 h-5 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-5 rounded-full bg-rose-500" style="width: {{ $totalUserMessages > 0 ? min(100, round($totalTicketRequests / max(1, $totalUserMessages) * 100)) : 0 }}%;"></div>
                    </div>
                    <p class="mt-2 text-sm text-slate-500">{{ $totalTicketRequests }} fallback indicators out of {{ $totalUserMessages }} user messages.</p>
                </div>

                <div>
                    <p class="text-sm uppercase tracking-[0.2em] text-slate-400">Language distribution</p>
                    @foreach($languageSummary as $language => $count)
                        <div class="mt-4 text-sm text-slate-500">{{ strtoupper($language) }}: {{ $count }}</div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200/70 bg-white p-8 shadow-sm lg:col-span-2">
            <h2 class="text-xl font-semibold">Recent fallback events</h2>
            <p class="mt-4 text-sm text-slate-500">أحدث الحالات التي أرسل فيها البوت رسالة دعم أو تم تحويل المستخدم إلى نموذج التذكرة.</p>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-slate-700">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Session</th>
                            <th class="px-4 py-3 font-semibold">User email</th>
                            <th class="px-4 py-3 font-semibold">User message</th>
                            <th class="px-4 py-3 font-semibold">Bot trigger</th>
                            <th class="px-4 py-3 font-semibold">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($fallbackCases as $case)
                            <tr>
                                <td class="px-4 py-4 text-slate-700">{{ $case['session_id'] }}</td>
                                <td class="px-4 py-4 text-slate-700">{{ $case['user_email'] ?? 'anonymous' }}</td>
                                <td class="px-4 py-4 text-slate-700">{{ \Illuminate\Support\Str::limit($case['user_message'], 80) }}</td>
                                <td class="px-4 py-4 text-slate-700">{{ \Illuminate\Support\Str::limit($case['bot_message'], 80) }}</td>
                                <td class="px-4 py-4 text-slate-500">{{ $case['created_at']->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-slate-500">No fallback events detected yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="mt-10 grid gap-8 lg:grid-cols-2">
        <div class="rounded-3xl border border-slate-200/70 bg-white p-8 shadow-sm">
            <h2 class="text-xl font-semibold">Latest support tickets</h2>
            <p class="mt-3 text-sm text-slate-500">أحدث طلبات الدعم التي تم إنشاؤها من نموذج التذكرة.</p>

            <div class="mt-6 space-y-4">
                @forelse($recentTickets as $ticket)
                    <div class="rounded-3xl bg-slate-50 p-4">
                        <p class="text-sm font-semibold">{{ $ticket->user_email ?? 'anonymous' }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ \Illuminate\Support\Str::limit($ticket->message, 120) }}</p>
                        <p class="mt-2 text-xs text-slate-500">{{ $ticket->created_at->format('Y-m-d H:i') }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No ticket requests have been submitted yet.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200/70 bg-white p-8 shadow-sm">
            <h2 class="text-xl font-semibold">Recent user issues</h2>
            <p class="mt-3 text-sm text-slate-500">أحدث رسائل المستخدمين في الدردشة، بما في ذلك الحالات المحتملة التي لم يفهمها البوت.</p>

            <div class="mt-6 space-y-4">
                @forelse($recentUserMessages as $message)
                    <div class="rounded-3xl bg-slate-50 p-4">
                        <p class="text-sm font-semibold">{{ $message->user_email ?? 'anonymous' }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ \Illuminate\Support\Str::limit($message->message, 120) }}</p>
                        <p class="mt-2 text-xs text-slate-500">{{ $message->created_at->format('Y-m-d H:i') }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No recent user messages yet.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection

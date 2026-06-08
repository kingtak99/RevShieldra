@extends('layouts.app')

@section('content')
    <div class="space-y-8">
        <section class="rounded-[2rem] card-soft p-8 shadow-glow ring-1 ring-slate-200/80">
            <div>
                <p class="text-sm uppercase tracking-[0.35em] text-blue-700">{{ __('ui.feedback_title') }}</p>
                <h1 class="mt-2 text-4xl font-semibold text-slate-900">{{ __('ui.customer_responses') }}</h1>
                <p class="mt-2 text-sm text-slate-600">{{ __('ui.review_all_recent_ratings') }}</p>
            </div>
        </section>

        <section class="rounded-[2rem] card-soft p-8 shadow-glow ring-1 ring-slate-200/80">
            <h2 class="text-xl font-semibold text-slate-900">{{ __('ui.filter_feedback') }}</h2>
            <p class="mt-2 text-sm text-slate-600">{{ __('ui.filter_feedback_description') }}</p>
            <form method="GET" action="{{ route('feedback.index') }}" class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="text-sm font-medium text-slate-700">{{ __('ui.from') }}</label>
                    <input type="datetime-local" name="from" value="{{ request('from') }}" class="input-base mt-2 w-full" />
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">{{ __('ui.to') }}</label>
                    <input type="datetime-local" name="to" value="{{ request('to') }}" class="input-base mt-2 w-full" />
                </div>
                <div class="lg:col-span-2 sm:col-span-2 min-w-0">
                    <label class="text-sm font-medium text-slate-700">{{ __('ui.branches') }}</label>
                    <div class="relative mt-2">
                        <button type="button" onclick="toggleFeedbackBranchDropdown()" class="input-base flex h-12 w-full items-center justify-between gap-3 bg-white px-3 text-left text-slate-700 shadow-sm transition hover:border-slate-400">
                            <span id="feedbackBranchSummary">{{ count((array) request('locations', [])) ? count((array) request('locations', [])) . ' ' . __('ui.branch_selected') : __('ui.select_branches') }}</span>
                            <span class="text-slate-400">▾</span>
                        </button>

                        <div id="feedbackBranchDropdown" class="absolute left-0 right-0 z-50 mt-2 hidden rounded-3xl border border-slate-200 bg-white p-4 shadow-2xl">
                            <div class="mb-3">
                                <input id="feedbackBranchSearch" oninput="filterFeedbackBranches()" type="text" placeholder="{{ __('ui.search_branches') }}" class="input-base w-full px-3 py-2.5" />
                            </div>
                            <div id="feedbackBranchOptions" class="max-h-64 overflow-y-auto space-y-2 pr-2">
                                @foreach($allLocations as $locationOption)
                                    <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 transition hover:border-slate-300">
                                        <input type="checkbox" name="locations[]" value="{{ $locationOption->id }}" class="h-4 w-4 rounded border-slate-300 text-blue-600" @checked(in_array($locationOption->id, (array) request('locations', []))) onchange="updateFeedbackBranchSummary()" />
                                        <span>{{ $locationOption->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-1 flex flex-wrap gap-2">
                    <button type="submit" class="min-w-0 flex-1 rounded-full btn-primary px-4 py-2.5 text-sm font-semibold">{{ __('ui.apply_filters') }}</button>
                    <a href="{{ route('feedback.index') }}" class="min-w-0 flex-1 rounded-full border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700">{{ __('ui.reset') }}</a>
                </div>
            </form>
            <script>
                function toggleFeedbackBranchDropdown() {
                    document.getElementById('feedbackBranchDropdown').classList.toggle('hidden');
                }
                function closeFeedbackBranchDropdown() {
                    document.getElementById('feedbackBranchDropdown').classList.add('hidden');
                }
                function updateFeedbackBranchSummary() {
                    const checkboxes = Array.from(document.querySelectorAll('#feedbackBranchOptions input[type="checkbox"]'));
                    const selected = checkboxes.filter(cb => cb.checked).map(cb => cb.nextElementSibling.textContent.trim());
                    const summary = document.getElementById('feedbackBranchSummary');
                    if (selected.length === 0) {
                        summary.textContent = '{{ __('ui.select_branches') }}';
                    } else if (selected.length === 1) {
                        summary.textContent = selected[0];
                    } else {
                        summary.textContent = selected.length + ' {{ __('ui.branch_selected') }}';
                    }
                }
                function filterFeedbackBranches() {
                    const query = document.getElementById('feedbackBranchSearch').value.toLowerCase();
                    document.querySelectorAll('#feedbackBranchOptions label').forEach(label => {
                        label.style.display = label.textContent.toLowerCase().includes(query) ? 'flex' : 'none';
                    });
                }
                document.addEventListener('click', function(event) {
                    const dropdown = document.getElementById('feedbackBranchDropdown');
                    const button = event.target.closest('button[onclick="toggleFeedbackBranchDropdown()"]');
                    const isInside = event.target.closest('#feedbackBranchDropdown');
                    if (!button && !isInside) {
                        closeFeedbackBranchDropdown();
                    }
                });
                updateFeedbackBranchSummary();
            </script>
        </section>

        <section class="overflow-x-auto rounded-[2rem] card-soft shadow-glow ring-1 ring-slate-200/80">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-[0.16em] text-xs">
                    <tr>
                        <th class="px-6 py-4">{{ __('ui.date') }}</th>
                        <th class="px-6 py-4">{{ __('ui.location_feedback') }}</th>
                        <th class="px-6 py-4">{{ __('ui.rating') }}</th>
                        <th class="px-6 py-4">{{ __('ui.message') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse ($feedback as $entry)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 text-slate-700">{{ $entry->created_at->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 text-slate-700">{{ $entry->location->name }}</td>
                            <td class="px-6 py-4 text-blue-700">{{ $entry->rating }}</td>
                            <td class="px-6 py-4 text-slate-700">{{ $entry->message ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500">{{ __('ui.no_feedback_yet') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>
@endsection

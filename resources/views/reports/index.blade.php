@extends('layouts.app')

@section('content')
    <div class="space-y-8">
        <section class="rounded-[2rem] card-soft p-8 shadow-glow ring-1 ring-slate-200/80">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.35em] text-blue-700">{{ __('ui.reports') }}</p>
                    <h1 class="mt-2 text-4xl font-semibold text-slate-900">{{ __('ui.ratings_complaints') }}</h1>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('reports.export_ratings', request()->query()) }}" class="inline-flex items-center justify-center rounded-full btn-primary px-5 py-3 text-sm font-semibold shadow-xl shadow-blue-500/10">{{ __('ui.export_ratings') }}</a>
                    <a href="{{ route('reports.export_complaints', request()->query()) }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-slate-50 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">{{ __('ui.export_complaints') }}</a>
                </div>
            </div>
        </section>

        <section class="rounded-[2rem] card-soft p-8 shadow-glow ring-1 ring-slate-200/80">
            <h2 class="text-xl font-semibold text-slate-900">{{ __('ui.filter_reports') }}</h2>
            <p class="mt-2 text-sm text-slate-600">{{ __('ui.filter_reports_description') }}</p>
            <form method="GET" action="{{ route('reports.index') }}" class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
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
                        <button type="button" onclick="toggleReportBranchDropdown()" class="input-base flex h-12 w-full items-center justify-between gap-3 bg-white px-3 text-left text-slate-700 shadow-sm transition hover:border-slate-400">
                            <span id="reportBranchSummary">{{ count((array) request('locations', [])) ? count((array) request('locations', [])) . ' ' . __('ui.branch_selected') : __('ui.select_branches') }}</span>
                            <span class="text-slate-400">▾</span>
                        </button>

                        <div id="reportBranchDropdown" class="absolute left-0 right-0 z-50 mt-2 hidden rounded-3xl border border-slate-200 bg-white p-4 shadow-2xl">
                            <div class="mb-3">
                                <input id="reportBranchSearch" oninput="filterReportBranches()" type="text" placeholder="{{ __('ui.search_branches') }}" class="input-base w-full px-3 py-2.5" />
                            </div>
                            <div id="reportBranchOptions" class="max-h-64 overflow-y-auto space-y-2 pr-2">
                                @foreach($allLocations as $locationOption)
                                    <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 transition hover:border-slate-300">
                                        <input type="checkbox" name="locations[]" value="{{ $locationOption->id }}" class="h-4 w-4 rounded border-slate-300 text-blue-600" @checked(in_array($locationOption->id, (array) request('locations', []))) onchange="updateReportBranchSummary()" />
                                        <span>{{ $locationOption->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-1 flex flex-wrap gap-1">
                    <button type="submit" class="min-w-0 flex-1 rounded-full btn-primary px-4 py-2.5 text-sm font-semibold">{{ __('ui.apply_filters') }}</button>
                    <a href="{{ route('reports.index') }}" class="min-w-0 flex-1 rounded-full border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700">{{ __('ui.reset') }}</a>
                </div>
            </form>
            <script>
                function toggleReportBranchDropdown() {
                    document.getElementById('reportBranchDropdown').classList.toggle('hidden');
                }
                function closeReportBranchDropdown() {
                    document.getElementById('reportBranchDropdown').classList.add('hidden');
                }
                function updateReportBranchSummary() {
                    const checkboxes = Array.from(document.querySelectorAll('#reportBranchOptions input[type="checkbox"]'));
                    const selected = checkboxes.filter(cb => cb.checked).map(cb => cb.nextElementSibling.textContent.trim());
                    const summary = document.getElementById('reportBranchSummary');
                    const branchSelectedMultipleText = @json(__('ui.branch_selected_multiple', ['count' => ':count']));
                    if (selected.length === 0) {
                        summary.textContent = '{{ __('ui.select_branches') }}';
                    } else if (selected.length === 1) {
                        summary.textContent = selected[0];
                    } else {
                        summary.textContent = branchSelectedMultipleText.replace(':count', selected.length);
                    }
                }
                function filterReportBranches() {
                    const query = document.getElementById('reportBranchSearch').value.toLowerCase();
                    document.querySelectorAll('#reportBranchOptions label').forEach(label => {
                        label.style.display = label.textContent.toLowerCase().includes(query) ? 'flex' : 'none';
                    });
                }
                document.addEventListener('click', function(event) {
                    const dropdown = document.getElementById('reportBranchDropdown');
                    const button = event.target.closest('button[onclick="toggleReportBranchDropdown()"]');
                    const isInside = event.target.closest('#reportBranchDropdown');
                    if (!button && !isInside) {
                        closeReportBranchDropdown();
                    }
                });
                updateReportBranchSummary();
            </script>
        </section>

        <section class="grid gap-4 lg:grid-cols-3">
            <div class="rounded-[2rem] card-soft p-6 shadow-glow ring-1 ring-slate-200/80">
                <p class="text-sm uppercase tracking-[0.35em] text-slate-500">{{ __('ui.total_ratings') }}</p>
                <p class="mt-4 text-4xl font-semibold text-slate-900">{{ $feedback->count() }}</p>
            </div>
            <div class="rounded-[2rem] card-soft p-6 shadow-glow ring-1 ring-slate-200/80">
                <p class="text-sm uppercase tracking-[0.35em] text-slate-500">{{ __('ui.average_rating') }}</p>
                <p class="mt-4 text-4xl font-semibold text-slate-900">{{ number_format($feedback->avg('rating') ?: 0, 1) }}</p>
            </div>
        </section>

        <section class="rounded-[2rem] card-soft p-6 shadow-glow ring-1 ring-slate-200/80">
            <h2 class="text-2xl font-semibold text-slate-900">{{ __('ui.distribution') }}</h2>
            <div class="mt-6 grid gap-4 md:grid-cols-5">
                @foreach ([5,4,3,2,1] as $star)
                    <div class="rounded-[2rem] card-soft p-4 text-center ring-1 ring-slate-200/80">
                        <p class="text-sm uppercase tracking-[0.35em] text-slate-500">{{ $star }} {{ __('ui.star_label') }}</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $feedback->where('rating', $star)->count() }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endsection

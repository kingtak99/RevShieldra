@extends('layouts.app')

@section('content')
    <div class="space-y-8">
        <section class="rounded-[2rem] card-soft p-8 shadow-panel ring-1 ring-slate-200/80">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.35em] text-blue-700">{{ __('ui.dashboard_title') }}</p>
                    <h1 class="mt-3 text-4xl font-semibold text-slate-900">{{ __('ui.your_performance_hub') }}</h1>
                    <p class="mt-3 text-slate-600">{{ __('ui.fresh_overview') }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    @if ($locationAction)
                        <a href="{{ $locationAction }}"
                            class="rounded-full btn-primary px-5 py-3 text-sm font-semibold">{{ $locationActionLabel }}</a>
                    @endif
                    <span class="rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-600">{{ __('ui.plan_label') }}
                        {{ $business?->plan ?? 'free' }}</span>
                </div>
            </div>
        </section>

        <section class="rounded-[2rem] card-soft p-8 shadow-glow ring-1 ring-slate-200/80">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900">{{ __('ui.filter_dashboard') }}</h2>
                    <p class="mt-2 text-sm text-slate-600">{{ __('ui.filter_dashboard_description') }}</p>
                </div>
            </div>
            <form method="GET" action="{{ route('dashboard') }}" class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="text-sm font-medium text-slate-700">{{ __('ui.from') }}</label>
                    <input type="datetime-local" name="from" value="{{ request('from') }}"
                        class="input-base mt-2 w-full" />
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700">{{ __('ui.to') }}</label>
                    <input type="datetime-local" name="to" value="{{ request('to') }}"
                        class="input-base mt-2 w-full" />
                </div>
                <div class="lg:col-span-2 sm:col-span-2 min-w-0">
                    <label class="text-sm font-medium text-slate-700">{{ __('ui.branches') }}</label>
                    <div class="relative mt-2">
                        <button type="button" onclick="toggleBranchDropdown()"
                            class="input-base flex h-12 w-full items-center justify-between gap-3 bg-white px-3 text-left text-slate-700 shadow-sm transition hover:border-slate-400">
                            <span
                                id="branchSummary">{{ count((array) request('locations', [])) ? count((array) request('locations', [])) . ' ' . __('ui.branch_selected') : __('ui.select_branches') }}</span>
                            <span class="text-slate-400">▾</span>
                        </button>

                        <div id="branchDropdown"
                            class="absolute left-0 right-0 z-50 mt-2 hidden rounded-3xl border border-slate-200 bg-white p-4 shadow-2xl">
                            <div class="mb-3 flex items-center gap-3">
                                <input id="branchSearch" oninput="filterBranches()" type="text"
                                    placeholder="{{ __('ui.search_branches') }}" class="input-base w-full px-3 py-2.5" />
                            </div>
                            <div id="branchOptions" class="max-h-64 overflow-y-auto space-y-2 pr-2">
                                @foreach ($allLocations as $locationOption)
                                    <label
                                        class="flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 transition hover:border-slate-300">
                                        <input type="checkbox" name="locations[]" value="{{ $locationOption->id }}"
                                            class="h-4 w-4 rounded border-slate-300 text-blue-600"
                                            @checked(in_array($locationOption->id, (array) request('locations', []))) onchange="updateBranchSummary()" />
                                        <span>{{ $locationOption->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-1 flex flex-wrap gap-2 justify-end">
                    <button type="submit"
                        class="min-w-0 flex-1 rounded-full btn-primary px-4 py-2.5 text-sm font-semibold">{{ __('ui.apply_filters') }}</button>
                    <a href="{{ route('dashboard') }}"
                        class="min-w-0 flex-1 rounded-full border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700">{{ __('ui.reset') }}</a>
                </div>
            </form>

            <script>
                function toggleBranchDropdown() {
                    document.getElementById('branchDropdown').classList.toggle('hidden');
                }

                function closeBranchDropdown() {
                    document.getElementById('branchDropdown').classList.add('hidden');
                }

                function updateBranchSummary() {
                    const checkboxes = Array.from(document.querySelectorAll('#branchOptions input[type="checkbox"]'));
                    const selected = checkboxes.filter(cb => cb.checked).map(cb => cb.nextElementSibling.textContent.trim());
                    const summary = document.getElementById('branchSummary');
                    if (selected.length === 0) {
                        summary.textContent = '{{ __('ui.select_branches') }}';
                    } else if (selected.length === 1) {
                        summary.textContent = selected[0];
                    } else {
                        summary.textContent = selected.length + ' {{ __('ui.branch_selected') }}';
                    }
                }

                function filterBranches() {
                    const query = document.getElementById('branchSearch').value.toLowerCase();
                    document.querySelectorAll('#branchOptions label').forEach(label => {
                        label.style.display = label.textContent.toLowerCase().includes(query) ? 'flex' : 'none';
                    });
                }

                document.addEventListener('click', function(event) {
                    const dropdown = document.getElementById('branchDropdown');
                    const button = event.target.closest('button[onclick="toggleBranchDropdown()"]');
                    const isInside = event.target.closest('#branchDropdown');
                    if (!button && !isInside) {
                        closeBranchDropdown();
                    }
                });

                updateBranchSummary();
            </script>
        </section>

        <section class="grid gap-4 xl:grid-cols-4">
            <div class="rounded-[2rem] card-soft p-6 shadow-panel ring-1 ring-slate-200/80">
                <p class="text-sm uppercase tracking-[0.35em] text-slate-500">{{ __('ui.locations_title') }}</p>
                <p class="mt-4 text-4xl font-semibold text-slate-900">{{ $locationCount }}</p>
            </div>
            <div class="rounded-[2rem] card-soft p-6 shadow-panel ring-1 ring-slate-200/80">
                <p class="text-sm uppercase tracking-[0.35em] text-slate-500">{{ __('ui.scans') }}</p>
                <p class="mt-4 text-4xl font-semibold text-slate-900">{{ $totalScans }}</p>
            </div>
            <div class="rounded-[2rem] card-soft p-6 shadow-panel ring-1 ring-slate-200/80">
                <p class="text-sm uppercase tracking-[0.35em] text-slate-500">{{ __('ui.avg_rating') }}</p>
                <p class="mt-4 text-4xl font-semibold text-slate-900">{{ number_format($averageRating, 1) }}</p>
            </div>
            <div class="rounded-[2rem] card-soft p-6 shadow-panel ring-1 ring-slate-200/80">
                <p class="text-sm uppercase tracking-[0.35em] text-slate-500">{{ __('ui.positive_negative') }}</p>
                <p class="mt-4 text-4xl font-semibold text-slate-900">{{ $positiveRatings }} / {{ $negativeRatings }}</p>
            </div>
        </section>

        <section class="rounded-[2rem] card-soft p-8 shadow-panel ring-1 ring-slate-200/80">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">{{ __('ui.your_locations') }}</h2>
                    <p class="mt-2 text-slate-600">{{ __('ui.see_every_branch') }}</p>
                </div>
                <div class="rounded-full bg-slate-100 px-4 py-2 text-sm text-slate-600">{{ __('ui.updated_recently') }}</div>
            </div>
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                @forelse ($locations as $location)
                    <div class="rounded-[2rem] card-soft p-6 shadow-panel ring-1 ring-slate-200/80 overflow-hidden">
                        <div class="flex items-start justify-between gap-4">

                            <!-- IMPORTANT -->
                            <div class="min-w-0">
                                <h3 class="text-xl font-semibold text-slate-900 break-words">
                                  <strong>Location: </strong>  {{ $location->name }}
                                </h3>

                                <p class="mt-2 text-sm text-slate-600 break-words">
                                    <strong>Google Maps URL: </strong> <a href="{{ $location->google_maps_url }}" target="_blank" class="text-blue-600 hover:underline">View on Google Maps</a>
                                </p>
                            </div>

                            <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-sm text-emerald-700">
                                Live
                            </span>
                        </div>

                        <div class="mt-5 space-y-3 text-sm text-slate-600">
                            <p class="break-words">
                                <strong>Complaint email: </strong> {{ $location->complaint_email }}
                            </p>

                            <p class="font-mono break-all text-slate-700">
                                <strong>Feedback URL: </strong> <a href="{{ url('review/' . $location->feedback_url) }}" target="_blank" class="text-blue-600 hover:underline">View Feedback</a>
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="rounded-[2rem] border border-dashed border-slate-300/60 bg-slate-100 p-8 text-slate-600">
                        No locations added yet. Add a location to start collecting feedback today.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection

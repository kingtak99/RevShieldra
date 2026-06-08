@extends('layouts.app')

@section('content')
    <div class="max-w-2xl mx-auto rounded-[2rem] card-soft p-10 shadow-glow ring-1 ring-slate-200/80">
        <div class="mb-8">
            <p class="text-sm uppercase tracking-[0.35em] text-blue-700">{{ __('ui.edit_branch') }}</p>
            <h1 class="mt-2 text-4xl font-semibold text-slate-900">{{ __('ui.update_location_details') }}</h1>
            <p class="mt-3 text-sm leading-6 text-slate-600">{{ __('ui.make_sure_branch_data_correct') }}</p>
        </div>

        <form id="editLocationForm" method="POST" action="{{ route('locations.update', $location) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="text-sm font-medium text-slate-700">{{ __('ui.location_name') }}</label>
                <input type="text" name="name" value="{{ old('name', $location->name) }}" required class="input-base mt-2" />
                @error('name')<p class="mt-2 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="text-sm font-medium text-slate-700">{{ __('ui.google_maps_url') }}</label>
                <input type="url" name="google_maps_url" value="{{ old('google_maps_url', $location->google_maps_url) }}" required class="input-base mt-2" />
                @error('google_maps_url')<p class="mt-2 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="text-sm font-medium text-slate-700">{{ __('ui.complaint_email') }}</label>
                <input type="email" name="complaint_email" value="{{ old('complaint_email', $location->complaint_email) }}" required class="input-base mt-2" />
                @error('complaint_email')<p class="mt-2 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="button" onclick="openConfirmSave()" class="rounded-2xl btn-primary px-6 py-3 text-sm font-semibold shadow-xl shadow-slate-950/20">{{ __('ui.save_changes') }}</button>
                <a href="{{ route('dashboard') }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">{{ __('ui.back_to_dashboard') }}</a>
            </div>
        </form>
    </div>

    <div id="confirmSaveModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 px-4">
        <div class="w-full max-w-xl rounded-[2rem] bg-white p-8 shadow-2xl ring-1 ring-slate-200">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm uppercase tracking-[0.35em] text-blue-700">{{ __('ui.confirm_update') }}</p>
                    <h2 class="mt-2 text-3xl font-semibold text-slate-900">{{ __('ui.save_changes_to_branch') }}</h2>
                </div>
                <button type="button" onclick="closeConfirmSave()" class="text-slate-400 hover:text-slate-700">✕</button>
            </div>
            <p class="mt-6 text-slate-600">{{ __('ui.are_you_sure_apply_changes') }}</p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-end">
                <button type="button" onclick="closeConfirmSave()" class="rounded-full border border-slate-200 bg-slate-100 px-6 py-3 text-sm font-semibold text-slate-700">{{ __('ui.cancel') }}</button>
                <button type="button" onclick="document.getElementById('editLocationForm').submit()" class="rounded-full bg-blue-600 px-6 py-3 text-sm font-semibold text-white hover:bg-blue-700">{{ __('ui.yes_save') }}</button>
            </div>
        </div>
    </div>

    <script>
        function openConfirmSave() {
            document.getElementById('confirmSaveModal').classList.remove('hidden');
        }

        function closeConfirmSave() {
            document.getElementById('confirmSaveModal').classList.add('hidden');
        }
    </script>
@endsection

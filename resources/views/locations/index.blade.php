@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.2em] text-slate-500">{{ __('ui.locations_title') }}</p>
                <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ __('ui.manage_branches') }}</h1>
            </div>
            @if(Auth::user()->isOwnerOfBusiness($business))
                <a href="{{ route('locations.create') }}" class="inline-flex items-center justify-center rounded-md bg-slate-900 px-6 py-3 text-sm font-semibold text-white">{{ __('ui.add_location') }}</a>
            @endif
        </div>

        @if(session('success'))
            <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5 text-emerald-900">{{ session('success') }}</div>
        @endif

        @if(session('upgrade_prompt'))
            <div class="rounded-3xl bg-amber-100 p-5 text-amber-900">Your plan limit has been reached. Upgrade to add more locations.</div>
        @endif

        <div class="grid gap-4 md:grid-cols-2">
            @forelse ($locations as $location)
                <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="text-xl font-semibold text-slate-900">{{ $location->name }}</h2>
                            <p class="mt-2 text-sm text-slate-500">{{ $location->feedback_count }} {{ __('ui.feedback_entries') }}</p>
                        </div>
                        @if(Auth::user()->isOwnerOfBusiness($business))
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('locations.edit', $location) }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">{{ __('ui.edit') }}</a>
                                <button type="button" onclick="openDeleteModal({{ $location->id }}, '{{ addslashes($location->name) }}')" class="inline-flex items-center justify-center rounded-full bg-rose-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-rose-700">{{ __('ui.delete') }}</button>
                            </div>
                        @endif
                    </div>
                    
                    @if($location->qr_code)
                        <div class="mt-4 bg-slate-100 p-4 rounded-lg text-center">
                            <img src="{{ $location->getQrCodePath() }}" alt="QR Code" class="mx-auto h-40 w-40" />
                            <a href="{{ $location->getQrCodePath() }}" download="{{ Str::slug($location->name) }}-qr.png" class="mt-3 inline-flex items-center justify-center rounded-md bg-slate-900 px-4 py-2 text-sm font-semibold text-white">{{ __('ui.download_qr') }}</a>
                        </div>
                    @endif
                    
                    <p class="mt-4 text-sm text-slate-600">{{ __('ui.google_maps') }}: <a href="{{ $location->google_maps_url }}" class="text-blue-600 underline" target="_blank">{{ __('ui.open') }}</a></p>
                    <p class="mt-2 text-sm text-slate-600">{{ __('ui.email_label') }} {{ $location->complaint_email }}</p>
                    
                    <div class="mt-4 p-3 bg-slate-50 rounded">
                        <p class="text-xs text-slate-500 mb-1">{{ __('ui.feedback_url_label') }}</p>
                        <p class="text-sm text-slate-700 break-all font-mono">{{ url('review/' . $location->feedback_url) }}</p>
                        <button onclick="navigator.clipboard.writeText('{{ url('review/' . $location->feedback_url) }}')" class="mt-2 text-xs text-blue-600 hover:underline">{{ __('ui.copy_link') }}</button>
                    </div>
                </div>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-300 p-6 text-slate-600">{{ __('ui.no_locations_yet') }}</div>
            @endforelse
        </div>
    </div>

    @if(Auth::user()->isOwnerOfBusiness($business))
        <div id="deleteModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-950/50 px-4">
            <div class="w-full max-w-xl rounded-[2rem] bg-white p-8 shadow-2xl ring-1 ring-slate-200">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm uppercase tracking-[0.35em] text-rose-700">{{ __('ui.confirm_delete') }}</p>
                        <h2 id="deleteModalTitle" class="mt-2 text-3xl font-semibold text-slate-900">{{ __('ui.delete_branch') }}</h2>
                    </div>
                    <button type="button" onclick="closeDeleteModal()" class="text-slate-400 hover:text-slate-700">✕</button>
                </div>
                <p class="mt-6 text-slate-600">{{ __('ui.delete_confirmation_text') }}</p>
                <p class="mt-4 rounded-3xl bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ __('ui.cannot_be_undone') }}</p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-end">
                    <button type="button" onclick="closeDeleteModal()" class="rounded-full border border-slate-200 bg-slate-100 px-6 py-3 text-sm font-semibold text-slate-700">{{ __('ui.cancel') }}</button>
                    <form id="deleteLocationForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-full bg-rose-600 px-6 py-3 text-sm font-semibold text-white hover:bg-rose-700">{{ __('ui.delete_branch') }}</button>
                    </form>
                </div>
            </div>
        </div>

        <script>
            function openDeleteModal(id, name) {
                var form = document.getElementById('deleteLocationForm');
                form.action = '/locations/' + id;
                document.getElementById('deleteModalTitle').textContent = 'Delete "' + name + '"?';
                document.getElementById('deleteModal').classList.remove('hidden');
            }

            function closeDeleteModal() {
                document.getElementById('deleteModal').classList.add('hidden');
            }
        </script>
    @endif
@endsection

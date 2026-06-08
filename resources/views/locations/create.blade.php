@extends('layouts.app')

@section('content')
    <div class="max-w-2xl mx-auto rounded-[2rem] card-soft p-10 shadow-glow ring-1 ring-slate-200/80">
        <div class="mb-8">
            <p class="text-sm uppercase tracking-[0.35em] text-blue-700">{{ __('ui.new_location') }}</p>
            <h1 class="mt-2 text-4xl font-semibold text-slate-900">{{ __('ui.add_a_branch') }}</h1>
            <p class="mt-3 text-sm leading-6 text-slate-600">{{ __('ui.create_a_new_location') }}</p>
        </div>

        <form method="POST" action="{{ route('locations.store') }}" class="space-y-6">
            @csrf
            <div>
                <label class="text-sm font-medium text-slate-700">{{ __('ui.location_name') }}</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="input-base mt-2" />
                @error('name')<p class="mt-2 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="text-sm font-medium text-slate-700">{{ __('ui.google_maps_url') }}</label>
                <input type="url" name="google_maps_url" value="{{ old('google_maps_url') }}" required class="input-base mt-2" />
                @error('google_maps_url')<p class="mt-2 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="text-sm font-medium text-slate-700">{{ __('ui.complaint_email') }}</label>
                <input type="email" name="complaint_email" value="{{ old('complaint_email') }}" required class="input-base mt-2" />
                @error('complaint_email')<p class="mt-2 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="rounded-2xl btn-primary px-6 py-3 text-sm font-semibold shadow-xl shadow-slate-950/20">{{ __('ui.create_location') }}</button>
        </form>
    </div>
@endsection

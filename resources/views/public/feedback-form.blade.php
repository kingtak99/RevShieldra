@extends('layouts.guest')

@section('title', __('ui.tell_us_what_went_wrong'))

@section('content')
    <div class="rounded-[2rem] card-soft p-8 shadow-glow ring-1 ring-slate-200/80">
        <div class="text-center space-y-5">
            <p class="text-sm uppercase tracking-[0.35em] text-blue-700">{{ __('ui.we_re_sorry') }}</p>
            <h1 class="text-3xl font-semibold text-slate-900">{{ __('ui.tell_us_what_went_wrong') }}</h1>
            <p class="text-slate-600">{{ __('ui.at_location', ['location' => $location->name]) }}</p>
            <div class="flex justify-center gap-2">
                @for ($i = 1; $i <= 5; $i++)
                    <svg class="h-6 w-6 {{ $i <= $rating ? 'text-yellow-400' : 'text-slate-600' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                @endfor
            </div>
        </div>

        <form method="POST" action="{{ route('public.feedback.submit', ['feedbackUrl' => $location->feedback_url]) }}" enctype="multipart/form-data" class="mt-8 space-y-6">
            @csrf
            <input type="hidden" name="rating" value="{{ $rating }}">

            <div>
                <label class="text-sm font-medium text-slate-700">{{ __('ui.your_name_optional') }}</label>
                <input type="text" name="name" value="{{ old('name') }}" class="input-base mt-2" />
            </div>

            <div>
                <label class="text-sm font-medium text-slate-700">{{ __('ui.email_optional') }}</label>
                <input type="text" name="email" value="{{ old('email') }}" class="input-base mt-2" />
            </div>

            <div>
                <label class="text-sm font-medium text-slate-700">{{ __('ui.what_went_wrong_required') }}</label>
                <textarea name="message" rows="4" required placeholder="{{ __('ui.form_placeholder') }}" class="input-base mt-2 min-h-[140px]">{{ old('message') }}</textarea>
                @error('message')<p class="mt-2 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="text-sm font-medium text-slate-700">{{ __('ui.attach_media_optional') }}</label>
                <input type="file" name="attachment" accept="image/*,video/*" class="input-base mt-2" />
                <p class="mt-2 text-sm text-slate-600">{{ __('ui.attach_media_help') }}</p>
                @error('attachment')<p class="mt-2 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-3">
                <button type="submit" name="action" value="send" class="w-full rounded-full btn-primary px-4 py-3 text-sm font-semibold shadow-xl shadow-blue-500/10">{{ __('ui.send_to_owner') }}</button>
                <button type="submit" name="action" value="google" class="w-full rounded-full border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition">{{ __('ui.rate_on_google') }}</button>
            </div>
        </form>
    </div>
@endsection

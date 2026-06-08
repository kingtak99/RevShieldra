@extends('layouts.guest')

@section('title', __('ui.how_was_your_experience'))

@section('content')
    <div class="mx-auto w-full max-w-xl rounded-[2rem] border border-slate-200 bg-white p-6 shadow-panel ring-1 ring-slate-200/80 sm:p-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-5 text-center sm:text-left">
                <p class="text-sm uppercase tracking-[0.35em] text-sky-600">{{ __('ui.quick_review') }}</p>
                <h1 class="text-3xl font-semibold text-slate-900">{{ __('ui.how_was_your_experience') }}</h1>
                <p class="text-slate-600">{{ __('ui.at_location', ['location' => $location->name]) }}</p>
            </div>

            <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white/90 p-1 text-sm">
                <a href="{{ route('locale.switch', ['locale' => 'en', 'redirect' => url()->full()]) }}" class="rounded-full px-3 py-2 {{ app()->getLocale() === 'en' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">EN</a>
                <a href="{{ route('locale.switch', ['locale' => 'ar', 'redirect' => url()->full()]) }}" class="rounded-full px-3 py-2 {{ app()->getLocale() === 'ar' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">AR</a>
            </div>
        </div>

        <form method="POST" action="{{ route('public.feedback.rate', ['feedbackUrl' => $location->feedback_url]) }}" class="mt-10 space-y-6">
            @csrf

            <div class="grid grid-cols-5 gap-2" id="ratingStars" data-place-id="{{ $location->google_place_id ?? '' }}">
                @for ($i = 1; $i <= 5; $i++)
                    <button type="submit" name="rating" value="{{ $i }}" data-rating="{{ $i }}" class="rating-button group flex aspect-square min-h-0 min-w-0 items-center justify-center rounded-[1.5rem] bg-slate-100 p-2 text-slate-700 transition hover:bg-slate-200 focus:outline-none sm:p-4" title="{{ $i }} {{ $i === 1 ? __('ui.star') : __('ui.stars') }}" aria-label="{{ __('ui.give_rating', ['rating' => $i]) }}">
                        <svg class="h-10 w-10 sm:h-14 sm:w-14 text-slate-500 transition" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </button>
                @endfor
            </div>

            <div class="grid gap-2 text-center text-xs uppercase tracking-[0.35em] text-slate-500 sm:grid-cols-2 sm:text-left">
                <span>{{ __('ui.one_star_message') }}</span>
                <span>{{ __('ui.five_star_message') }}</span>
            </div>

            <div id="ratingHint" class="text-center text-sm text-slate-600">{{ __('ui.hover_over_stars_to_preview') }}</div>
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const buttons = document.querySelectorAll('.rating-button');
                const hint = document.getElementById('ratingHint');
                const messages = {
                    hover: @json(__('ui.hover_over_stars_to_preview')),
                    stars: @json(__('ui.stars')),
                    star: @json(__('ui.star')),
                    redirect: @json(__('ui.you_will_be_redirected')),
                    feedback: @json(__('ui.you_can_send_feedback_directly')),
                };

                const setHoverState = (rating) => {
                    buttons.forEach((button) => {
                        const star = Number(button.dataset.rating);
                        const svg = button.querySelector('svg');
                        if (star <= rating) {
                            button.classList.add('bg-amber-500/15');
                            svg.classList.add('text-yellow-400');
                            svg.classList.remove('text-slate-300');
                        } else {
                            button.classList.remove('bg-amber-500/15');
                            svg.classList.remove('text-yellow-400');
                            svg.classList.add('text-slate-300');
                        }
                    });

                    if (rating > 0) {
                        const label = rating === 1 ? messages.star : messages.stars;
                        const action = rating >= 4 ? messages.redirect : messages.feedback;
                        hint.textContent = `${rating} ${label} ${action}`;
                    } else {
                        hint.textContent = messages.hover;
                    }
                };

                const starRedirectPlaceId = document.getElementById('ratingStars').dataset.placeId;

                buttons.forEach((button) => {
                    const starValue = Number(button.dataset.rating);
                    button.addEventListener('mouseenter', () => setHoverState(starValue));
                    button.addEventListener('focus', () => setHoverState(starValue));
                    button.addEventListener('mouseleave', () => setHoverState(0));
                    button.addEventListener('blur', () => setHoverState(0));

                    button.addEventListener('click', function (event) {
                        if (starValue >= 4 && starRedirectPlaceId) {
                            event.preventDefault();
                            window.location.href = `https://search.google.com/local/writereview?placeid=${encodeURIComponent(starRedirectPlaceId)}`;
                        }
                    });
                });
            });
        </script>
    </div>
@endsection

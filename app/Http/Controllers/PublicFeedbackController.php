<?php

namespace App\Http\Controllers;

use App\Mail\FeedbackReceived;
use App\Models\Feedback;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;

class PublicFeedbackController extends Controller
{
    protected function ensureLocationFeedbackAvailable(Location $location)
    {
        $business = $location->business;
        $owner = $business?->owner;
        $subscription = $owner?->subscription;

        if (! $business || ! $owner || ! $subscription) {
            return false;
        }

        if ($subscription->isExpired() || $subscription->status !== 'active') {
            return false;
        }

        return true;
    }

    protected function feedbackUnavailableView(Location $location)
    {
        $isOwner = auth()->check() && auth()->id() === $location->business?->owner_id;

        return view('public.feedback-unavailable', [
            'isOwner' => $isOwner,
        ]);
    }

    public function show(string $feedbackUrl)
    {
        session()->forget('public_feedback_rating');

        $location = Location::with('business.owner.subscription')->where('feedback_url', $feedbackUrl)->firstOrFail();

        if (! $this->ensureLocationFeedbackAvailable($location)) {
            return $this->feedbackUnavailableView($location);
        }

        return view('public.feedback', [
            'location' => $location,
        ]);
    }

    public function rateForm(string $feedbackUrl, Request $request)
    {
        $location = Location::with('business.owner.subscription')->where('feedback_url', $feedbackUrl)->firstOrFail();

        if (! $this->ensureLocationFeedbackAvailable($location)) {
            return $this->feedbackUnavailableView($location);
        }

        $location = Location::where('feedback_url', $feedbackUrl)->firstOrFail();
        $rating = $request->input('rating') ?? session('public_feedback_rating');

        if (!$rating || $rating < 1 || $rating > 5) {
            session()->forget('public_feedback_rating');
            return redirect()->route('public.feedback', ['feedbackUrl' => $feedbackUrl]);
        }

        session(['public_feedback_rating' => $rating]);

        if ($rating >= 4) {
            session()->forget('public_feedback_rating');

            Feedback::create([
                'location_id' => $location->id,
                'rating' => $rating,
                'name' => null,
                'email' => null,
                'message' => null,
            ]);

            return redirect()->away($location->google_maps_url);
        }

        return view('public.feedback-form', [
            'location' => $location,
            'rating' => $rating,
        ]);
    }

    public function submit(Request $request, string $feedbackUrl)
    {
        session()->forget('public_feedback_rating');
        $location = Location::with('business.owner.subscription')->where('feedback_url', $feedbackUrl)->firstOrFail();

        if (! $this->ensureLocationFeedbackAvailable($location)) {
            return view('public.feedback-unavailable');
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:4000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,mp4,mov,webm', 'max:10240'],
            'action' => ['required', 'in:send,google'],
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
            $attachmentPath = $request->file('attachment')->store('feedback-attachments', 'public');
        }

        $feedback = Feedback::create([
            'location_id' => $location->id,
            'rating' => $data['rating'],
            'name' => $data['name'],
            'email' => $data['email'],
            'message' => $data['message'],
            'attachment_path' => $attachmentPath,
        ]);

        Log::info('Public feedback received', ['location_id' => $location->id, 'rating' => $feedback->rating]);

        if ($data['action'] === 'send') {
            $sent = $this->sendComplaintEmail($location, $feedback);

            return view('public.feedback-success', [
                'location' => $location,
                'message' => $sent
                    ? __('ui.feedback_sent_message')
                    : __('ui.feedback_saved_but_email_failed'),
            ]);
        } else {
            return redirect()->away($location->google_maps_url);
        }
    }

    private function sendComplaintEmail(Location $location, Feedback $feedback): bool
    {
        if (!$location->complaint_email) {
            Log::warning('Complaint email is not configured for location', ['location_id' => $location->id]);
            return false;
        }

        try {
            $location->loadMissing('business');
            $emailLocale = $location->business?->email_locale ?? config('app.locale');

            $mailable = (new FeedbackReceived($location, $feedback, $feedback->attachment_path))
                ->locale($emailLocale);

            Mail::mailer(config('mail.default'))
                ->to($location->complaint_email)
                ->send($mailable);

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send complaint email', ['error' => $e->getMessage(), 'location_id' => $location->id]);
            return false;
        }
    }
}

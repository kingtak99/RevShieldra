<?php

namespace App\Mail;

use App\Models\Feedback;
use App\Models\Location;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FeedbackReceived extends Mailable
{
    use Queueable, SerializesModels;

    public Location $location;
    public Feedback $feedback;
    public ?string $attachmentPath;
    public string $logoPath;
    public bool $rtl;
    public string $dashboardUrl;

    public function __construct(Location $location, Feedback $feedback, ?string $attachmentPath = null)
    {
        $this->location = $location;
        $this->feedback = $feedback;
        $this->attachmentPath = $attachmentPath;
        $this->logoPath = public_path('images/revshieldra-logo-bright.png');
        $this->rtl = preg_match('/\p{Arabic}/u', $location->name) === 1;
        $this->dashboardUrl = route('dashboard');
    }

    public function build()
    {
        $mail = $this->subject(__('emails.feedback_received.subject', [
                        'location' => $this->location->name,
                        'rating' => $this->feedback->rating,
                    ]))
                    ->view('emails.feedback-received');

        if ($this->attachmentPath) {
            $attachmentFile = storage_path('app/public/' . $this->attachmentPath);
            if (file_exists($attachmentFile)) {
                $mail->attach($attachmentFile);
            }
        }

        return $mail;
    }
}

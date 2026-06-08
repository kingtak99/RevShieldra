<?php

namespace App\Mail;

use App\Models\Business;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrialNotification extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public Subscription $subscription;
    public Business $business;
    public string $messageType;

    public function __construct(User $user, Subscription $subscription, Business $business, string $messageType)
    {
        $this->user = $user;
        $this->subscription = $subscription;
        $this->business = $business;
        $this->messageType = $messageType;
    }

    public function build()
    {
        $subject = match ($this->messageType) {
            'welcome' => 'Welcome to RevShieldra — Your 30-Day Free Trial Is Active',
            'seven_day' => 'Your RevShieldra trial ends in 7 days',
            'one_day' => 'Your RevShieldra trial ends tomorrow',
            'expired' => 'Your RevShieldra subscription has ended',
            default => 'Your RevShieldra account update',
        };

        return $this->subject($subject)
            ->view('emails.trial_notification')
            ->with([
                'subject' => $subject,
                'messageType' => $this->messageType,
                'user' => $this->user,
                'business' => $this->business,
                'subscription' => $this->subscription,
                'daysRemaining' => $this->subscription->daysRemaining(),
            ]);
    }
}

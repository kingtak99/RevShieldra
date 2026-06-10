<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Mail\Traits\SendsViaBrevo;

class SupportTicketMail extends Mailable
{
    use Queueable, SerializesModels, SendsViaBrevo;

    public $ticketData;

    public function __construct(array $ticketData)
    {
        $this->ticketData = $ticketData;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🚨 RevShieldra Chatbot: New Support Ticket Received',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.support_ticket',
        );
    }
}

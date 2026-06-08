<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportTicketMail extends Mailable
{
    use Queueable, SerializesModels;

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

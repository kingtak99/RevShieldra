<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Markdown;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

    /**
     * Send this mailable using Brevo (Transactional Emails) API instead of the local Mail transport.
     *
     * @param string $recipientEmail
     * @return void
     * @throws \Exception
     */
    public function sendViaBrevo(string $recipientEmail): void
    {
        $apiKey = env('BREVO_API_KEY');

        if (empty($apiKey)) {
            throw new \RuntimeException('BREVO_API_KEY is not configured.');
        }

        // Render the markdown Blade to final HTML using Laravel's Markdown renderer.
        // If that yields empty output, fall back to the view renderer.
        $markdown = app(Markdown::class);
        $html = (string) $markdown->render('emails.support_ticket', ['ticketData' => $this->ticketData]);
        if (trim($html) === '') {
            $html = view('emails.support_ticket', ['ticketData' => $this->ticketData])->render();
        }

        // Final fallback simple HTML if render somehow produced empty content
        if (trim($html) === '') {
            $html = '<p>New support ticket received.</p>' .
                '<p>Name: ' . htmlspecialchars($this->ticketData['name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') . '</p>' .
                '<p>Email: ' . htmlspecialchars($this->ticketData['email'] ?? '', ENT_QUOTES, 'UTF-8') . '</p>' .
                '<p>Issue: ' . nl2br(htmlspecialchars($this->ticketData['issue'] ?? '', ENT_QUOTES, 'UTF-8')) . '</p>';
        }

        $senderEmail = env('MAIL_FROM_ADDRESS', 'hasantak99@gmail.com');
        $senderName = env('MAIL_FROM_NAME', 'RevShieldra');

        $payload = [
            'sender' => [
                'email' => $senderEmail,
                'name' => $senderName,
            ],
            'to' => [
                [
                    'email' => $recipientEmail,
                    'name' => $this->ticketData['name'] ?? null,
                ],
            ],
            'subject' => $this->envelope()->subject ?? 'Support Ticket',
            'htmlContent' => $html,
            'textContent' => strip_tags($html),
        ];

        // Set reply-to if available
        if (!empty($this->ticketData['email'])) {
            $payload['replyTo'] = [
                'email' => $this->ticketData['email'],
                'name' => $this->ticketData['name'] ?? null,
            ];
        }

        try {
            $response = Http::withHeaders([
                'api-key' => $apiKey,
                'Accept' => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', $payload);

            if (!$response->successful()) {
                Log::error('Brevo email send failed', ['status' => $response->status(), 'body' => $response->body(), 'payload_preview' => substr(json_encode($payload), 0, 1000)]);
                throw new \RuntimeException('Brevo email send failed: HTTP ' . $response->status());
            }
        } catch (\Throwable $e) {
            Log::error('Brevo email send exception: ' . $e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }
}

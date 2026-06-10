<?php

namespace App\Mail\Traits;

use Illuminate\Mail\Markdown;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait SendsViaBrevo
{
    public function sendViaBrevo(string $recipientEmail, ?string $recipientName = null, ?string $replyToEmail = null, ?string $replyToName = null): void
    {
        $apiKey = trim(env('BREVO_API_KEY'));

        if (empty($apiKey)) {
            throw new \RuntimeException('BREVO_API_KEY is not configured.');
        }

        $html = $this->renderBrevoHtmlContent();
        if (trim($html) === '') {
            $html = $this->renderBrevoFallbackHtml();
        }

        if (trim($html) === '') {
            throw new \RuntimeException('Brevo email send failed: valid htmlContent is required.');
        }

        $payload = [
            'sender' => [
                'email' => env('MAIL_FROM_ADDRESS', 'no-reply@example.com'),
                'name' => env('MAIL_FROM_NAME', 'RevShieldra'),
            ],
            'to' => [
                [
                    'email' => $recipientEmail,
                    'name' => $recipientName,
                ],
            ],
            'subject' => $this->getBrevoSubject(),
            'htmlContent' => $html,
            'textContent' => strip_tags($html),
        ];

        if (!empty($replyToEmail)) {
            $payload['replyTo'] = [
                'email' => $replyToEmail,
                'name' => $replyToName,
            ];
        }

        if (method_exists($this, 'brevoAttachments')) {
            $attachments = $this->brevoAttachments();
            if (!empty($attachments) && is_array($attachments)) {
                $payload['attachment'] = [];
                foreach ($attachments as $attachment) {
                    $path = $attachment['path'] ?? null;
                    $name = $attachment['name'] ?? null;

                    if (empty($path) || !file_exists($path)) {
                        continue;
                    }

                    $payload['attachment'][] = [
                        'name' => $name ?? basename($path),
                        'content' => base64_encode(file_get_contents($path)),
                    ];
                }
            }
        }

        try {
            $response = Http::withHeaders([
                'api-key' => $apiKey,
                'Accept' => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', $payload);

            if (!$response->successful()) {
                Log::error('Brevo email send failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'payload_preview' => substr(json_encode($payload), 0, 1000),
                ]);

                throw new \RuntimeException('Brevo email send failed: HTTP ' . $response->status() . ' - ' . $response->body());
            }
        } catch (\Throwable $e) {
            Log::error('Brevo email send exception: ' . $e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    public function renderBrevoHtmlContent(): string
    {
        if (!empty($this->markdown)) {
            return (string) app(Markdown::class)->render($this->markdown, $this->getBrevoViewData());
        }

        if (!empty($this->htmlView)) {
            return view($this->htmlView, $this->getBrevoViewData())->render();
        }

        if (!empty($this->view)) {
            return view($this->view, $this->getBrevoViewData())->render();
        }

        if (!empty($this->html)) {
            return (string) $this->html;
        }

        if (!empty($this->textView)) {
            return nl2br(view($this->textView, $this->getBrevoViewData())->render());
        }

        return '';
    }

    public function renderBrevoFallbackHtml(): string
    {
        if (!empty($this->subject)) {
            return '<p>' . e($this->subject) . '</p>';
        }

        return '<p>A new email has been generated.</p>';
    }

    public function getBrevoSubject(): string
    {
        if (!empty($this->subject)) {
            return $this->subject;
        }

        if (method_exists($this, 'envelope')) {
            $envelope = $this->envelope();
            if (!empty($envelope?->subject)) {
                return $envelope->subject;
            }
        }

        return 'Notification from RevShieldra';
    }

    protected function getBrevoViewData(): array
    {
        return [];
    }
}

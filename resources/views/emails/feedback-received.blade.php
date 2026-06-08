<!DOCTYPE html>
<html lang="en" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ __('emails.feedback_received.subject', ['location' => $location->name, 'rating' => $feedback->rating]) }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f6fb;color:#1f2937;font-family:Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#f4f6fb;padding:32px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff;border-radius:24px;overflow:hidden;box-shadow:0 24px 64px rgba(15,23,42,0.08);">
                    <tr>
                        <td style="background:#0f172a;padding:24px 32px;text-align:center;">
                            <img src="{{ $message->embed($logoPath) }}" alt="RevShieldra" width="120" style="display:block;margin:0 auto 16px;max-width:120px;height:auto;" />
                            <h1 style="margin:0;color:#ffffff;font-size:22px;letter-spacing:0.02em;font-weight:700;">{{ __('emails.feedback_received.title') }}</h1>
                            <p style="margin:8px 0 0;color:#cbd5e1;font-size:14px;">{{ __('emails.feedback_received.summary') }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:26px 32px;">
                            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                                <div style="flex:1;min-width:240px;">
                                    <p style="margin:0 0 6px;font-size:14px;color:#64748b;text-transform:uppercase;letter-spacing:0.14em;">{{ __('emails.feedback_received.summary') }}</p>
                                    <h2 style="margin:0;font-size:24px;color:#0f172a;">{{ $location->name }}</h2>
                                </div>
                                <div style="flex:1;min-width:220px;text-align:{{ $rtl ? 'right' : 'left' }};">
                                    @php
                                        $rating = (int) $feedback->rating;
                                        $filled = str_repeat('★', $rating);
                                        $empty = str_repeat('☆', 5 - $rating);
                                        $ratingColor = $rating >= 4 ? '#10b981' : ($rating === 3 ? '#f59e0b' : '#ef4444');
                                    @endphp
                                    <div style="display:inline-block;background:{{ $ratingColor }}10;border:1px solid {{ $ratingColor }};border-radius:999px;padding:10px 16px;">
                                        <span style="font-size:16px;font-weight:600;color:{{ $ratingColor }};letter-spacing:0.03em;">{{ $filled }}{{ $empty }}</span>
                                        <span style="display:block;margin-top:8px;font-size:13px;color:{{ $ratingColor }};">Rating: {{ $rating }}/5</span>
                                    </div>
                                </div>
                            </div>

                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-top:24px;border-collapse:separate;">
                                <tr>
                                    <td style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:18px;padding:22px;">
                                        <h3 style="margin:0 0 12px;font-size:18px;color:#0f172a;">{{ __('emails.feedback_received.customer_details') }}</h3>
                                        <p style="margin:0 0 8px;font-size:14px;color:#334155;"><strong>{{ __('emails.feedback_received.name') }}:</strong> {{ $feedback->name ?? 'Anonymous' }}</p>
                                        <p style="margin:0 0 8px;font-size:14px;color:#334155;"><strong>{{ __('emails.feedback_received.email') }}:</strong> {{ $feedback->email ?? 'Not provided' }}</p>
                                        <p style="margin:0 0 8px;font-size:14px;color:#334155;"><strong>{{ __('emails.feedback_received.received_at') }}:</strong> {{ $feedback->created_at->format('Y-m-d H:i:s') }}</p>
                                    </td>
                                </tr>
                            </table>

                            <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:18px;padding:22px;margin-top:24px;">
                                <h3 style="margin:0 0 12px;font-size:18px;color:#0f172a;">{{ __('emails.feedback_received.message') }}</h3>
                                    <p style="margin:0;font-size:15px;line-height:1.7;color:#334155;">{!! nl2br(e($feedback->message)) !!}</p>
                            @if ($feedback->attachment_path)
                                <div style="background:#f8fafc;border-radius:18px;border:1px solid #e2e8f0;padding:20px;margin-top:24px;">
                                    <p style="margin:0 0 10px;font-size:14px;color:#475569;font-weight:600;">{{ __('emails.feedback_received.attachment_included') }}</p>
                                    <p style="margin:0 0 16px;font-size:13px;color:#64748b;">{{ __('emails.feedback_received.attachment_help') }}</p>
                                    <a href="{{ url('storage/' . $feedback->attachment_path) }}" style="display:inline-block;padding:12px 22px;background:#0f172a;color:#ffffff;border-radius:999px;font-size:14px;text-decoration:none;">{{ __('emails.feedback_received.download_attachment') }}</a>
                                </div>
                            @endif

                            <div style="margin-top:28px;text-align:center;">
                                <a href="{{ $dashboardUrl }}" style="display:inline-block;padding:14px 28px;background:#0f172a;color:#ffffff;border-radius:999px;font-size:15px;text-decoration:none;">{{ __('emails.feedback_received.view_dashboard') }}</a>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="background:#f8fafc;padding:18px 32px;text-align:center;color:#64748b;font-size:12px;">
                            <p style="margin:0;">{{ __('emails.feedback_received.footer_line') }}</p>
                            <p style="margin:8px 0 0;">{{ __('emails.feedback_received.footer_subline') }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

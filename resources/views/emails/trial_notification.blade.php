<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
</head>
<body style="font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f7fb; color: #0f172a; margin: 0; padding: 0;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background: #f5f7fb; padding: 32px 0; width: 100%;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="background: #ffffff; border-radius: 24px; box-shadow: 0 30px 60px rgba(15, 23, 42, 0.08); overflow: hidden;">
                    <tr>
                        <td style="padding: 32px 36px; text-align: center;">
                            <p style="margin: 0; color: #2563eb; font-size: 14px; letter-spacing: 0.16em; text-transform: uppercase;">RevShieldra</p>
                            <h1 style="margin: 16px 0 0; font-size: 28px; line-height: 1.2; color: #0f172a;">{{ $subject }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0 36px 32px; color: #334155; font-size: 16px; line-height: 1.75;">
                            @if($messageType === 'welcome')
                                <p>Hello {{ $user->name }},</p>
                                <p>Welcome to RevShieldra. Your 30-day free trial is now active for <strong>{{ $business->name }}</strong>.</p>
                                <p>Over the next month you can:</p>
                                <ul style="margin: 0 0 24px 20px; padding: 0;">
                                    <li>Capture reviews and feedback for every branch.</li>
                                    <li>Route happy customers to public review pages.</li>
                                    <li>Keep sensitive complaints private and resolve them fast.</li>
                                </ul>
                                <p>Your trial ends on <strong>{{ $subscription->current_period_end->toFormattedDateString() }}</strong>.</p>
                                <p style="margin: 24px 0 0;"><strong>What happens next?</strong></p>
                                <ul style="margin: 0 0 24px 20px; padding: 0;">
                                    <li>One week before the trial ends, we’ll remind you by email.</li>
                                    <li>One day before expiration, we’ll remind you again.</li>
                                    <li>If your trial ends, your plan will be paused and your dashboard access will be limited until you renew.</li>
                                </ul>
                            @elseif($messageType === 'seven_day')
                                <p>Hello {{ $user->name }},</p>
                                <p>Your RevShieldra free trial for <strong>{{ $business->name }}</strong> ends in <strong>7 days</strong>.</p>
                                <p>During these final days you can still use the full dashboard and assess how RevShieldra improves your branch reputation flow.</p>
                                <p>Renew now to keep capturing reviews and private feedback without interruption.</p>
                            @elseif($messageType === 'one_day')
                                <p>Hello {{ $user->name }},</p>
                                <p>Your RevShieldra free trial for <strong>{{ $business->name }}</strong> ends tomorrow.</p>
                                <p>If you want to keep collecting ratings and reviews automatically, renew today and avoid any pause in access.</p>
                            @elseif($messageType === 'expired')
                                <p>Hello {{ $user->name }},</p>
                                <p>Your RevShieldra trial for <strong>{{ $business->name }}</strong> has ended.</p>
                                <p>At this point, your access is limited and review capture will stop until you renew your plan.</p>
                                <p>Renew now to restore your dashboard, branch feedback flow, and review routing.</p>
                            @else
                                <p>Hello {{ $user->name }},</p>
                                <p>There’s an update on your RevShieldra account. Please visit your settings to review your subscription status.</p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 0 36px 40px;">
                            <a href="{{ url('/') }}" style="display: inline-block; padding: 14px 26px; background: #2563eb; color: #ffffff; text-decoration: none; border-radius: 999px; font-weight: 600;">View your dashboard</a>
                        </td>
                    </tr>
                </table>
                <p style="margin: 24px 0 0; color: #64748b; font-size: 13px; max-width: 560px; line-height: 1.65; text-align: center;">If you have any questions, reply to this email and our team will help you get started.</p>
            </td>
        </tr>
    </table>
</body>
</html>

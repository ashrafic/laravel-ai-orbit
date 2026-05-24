<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $test ? 'Budget Alert Test' : 'Budget Alert' }}</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;color:#111827;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f4f6;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#ffffff;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
                    <tr>
                        <td style="padding:24px 28px;background:#111827;color:#f9fafb;">
                            <div style="font-size:13px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#a5b4fc;">Laravel AI Orbit</div>
                            <h1 style="margin:10px 0 0;font-size:24px;line-height:1.25;font-weight:700;">
                                {{ $test ? 'Budget alert test email' : 'AI budget threshold exceeded' }}
                            </h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            @if($test)
                                <p style="margin:0 0 18px;font-size:15px;line-height:1.6;color:#374151;">
                                    This is a test notification for your {{ $alert->period }} AI budget alert.
                                </p>
                            @else
                                <p style="margin:0 0 18px;font-size:15px;line-height:1.6;color:#374151;">
                                    Your {{ $alert->period }} AI spending has crossed the configured threshold.
                                </p>
                            @endif

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 22px;border-collapse:collapse;">
                                <tr>
                                    <td style="padding:14px 16px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;">
                                        <div style="font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#6b7280;">Current Spend</div>
                                        <div style="margin-top:4px;font-size:28px;line-height:1.2;font-weight:800;color:#111827;">{{ $currencySymbol }}{{ number_format($currentSpend, 6) }}</div>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 22px;border-collapse:collapse;">
                                <tr>
                                    <td style="padding:12px 0;border-bottom:1px solid #e5e7eb;color:#6b7280;font-size:14px;">Threshold</td>
                                    <td align="right" style="padding:12px 0;border-bottom:1px solid #e5e7eb;color:#111827;font-size:14px;font-weight:600;">{{ $currencySymbol }}{{ number_format((float) $alert->threshold_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 0;border-bottom:1px solid #e5e7eb;color:#6b7280;font-size:14px;">Period</td>
                                    <td align="right" style="padding:12px 0;border-bottom:1px solid #e5e7eb;color:#111827;font-size:14px;font-weight:600;text-transform:capitalize;">{{ $alert->period }}</td>
                                </tr>
                            </table>

                            @if($hasUnpricedUsage)
                                <div style="margin:0 0 22px;padding:14px 16px;border-radius:8px;background:#fffbeb;border:1px solid #f59e0b;color:#92400e;font-size:14px;line-height:1.5;">
                                    Some observed AI usage has no matching pricing rule, so actual spend may be higher.
                                </div>
                            @endif

                            <a href="{{ $dashboardUrl }}" style="display:inline-block;padding:11px 16px;border-radius:6px;background:#4f46e5;color:#ffffff;text-decoration:none;font-size:14px;font-weight:700;">
                                View Orbit Dashboard
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 28px;background:#f9fafb;border-top:1px solid #e5e7eb;color:#6b7280;font-size:12px;line-height:1.5;">
                            This email was sent by Laravel AI Orbit using your application's configured Laravel mailer.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

Laravel AI Orbit

{{ $test ? 'Budget alert test email' : 'AI budget threshold exceeded' }}

@if($test)
This is a test notification for your {{ $alert->period }} AI budget alert.
@else
Your {{ $alert->period }} AI spending has crossed the configured threshold.
@endif

Current spend: {{ $currencySymbol }}{{ number_format($currentSpend, 6) }}
Threshold: {{ $currencySymbol }}{{ number_format((float) $alert->threshold_amount, 2) }}
Period: {{ $alert->period }}

@if($hasUnpricedUsage)
Some observed AI usage has no matching pricing rule, so actual spend may be higher.
@endif

View Orbit Dashboard: {{ $dashboardUrl }}

This email was sent by Laravel AI Orbit using your application's configured Laravel mailer.

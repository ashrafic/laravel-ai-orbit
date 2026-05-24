# Budget Alerts

Budget Alerts help you stay on top of AI spending by sending notifications when your costs exceed configurable thresholds. Alerts are dispatched via Laravel's queue system, so they never slow down requests.

## Access

Navigate to `/ai-orbit/usage/alerts`.

## Creating an Alert

Click **"New Alert"** and configure:

| Field | Description | Options |
|:---|:---|:---|
| **Threshold Amount** | The spending limit that triggers the alert | Any positive number |
| **Period** | The time window for measuring spending | Daily, Weekly, Monthly |
| **Channels** | How you want to be notified | Mail |
| **Recipients** | Email addresses for this specific alert | Comma-separated list |
| **Enabled** | Whether the alert is active | On/Off |

### Example Alert

```
Threshold:   $500.00
Period:      Monthly
Channels:    Mail
Enabled:     Yes
```

This sends an email when monthly AI spending reaches $500.

## How Alerts Work

1. Orbit calculates current spending for the alert's period
2. If spending >= threshold, a notification is dispatched
3. The notification is sent via the configured channels
4. The alert's `last_triggered_at` is updated to prevent spam

### Throttling

Notifications are throttled by period to prevent spam:

| Period | Minimum Interval |
|:---|:---|
| Daily | Once per day |
| Weekly | Once per week |
| Monthly | Once per month |

This means if your spending stays above the threshold, you'll get one notification per period — not a constant stream.

## Spending Calculation

Current spending is calculated by:

1. Aggregating token usage for the period via `TokenAggregator` from both SDK conversations and `orbit_ai_runs`
2. Applying provider+model-specific pricing rules via `CostCalculator`
3. Summing total costs

```php
$aggregator = app(TokenAggregator::class);
$stats = $aggregator->periodStats('month');

$calculator = app(CostCalculator::class);
$cost = $calculator->calculate('gpt-4', $stats['input_tokens'], $stats['output_tokens']);

$currentSpend = $cost['total'];
```

If no pricing rule exists for a specific provider+model combination, cost is tracked as missing pricing so you can identify gaps in your pricing matrix.

## Notification Channels

### Mail

Notifications are sent to the alert's configured recipients (or the default mail from address if none are set):

```php
Notification::route('mail', $alert->recipients ?? config('mail.from.address'))
    ->notify(new BudgetExceeded($alert, $currentSpend));
```

The email includes:
- The period (daily/weekly/monthly)
- Current spending amount
- The threshold amount
- A link to the Orbit dashboard

Orbit ships with both HTML and plain-text email templates for budget alerts. You can test delivery for any alert directly from the dashboard using the **"Test Email"** action.

## Programmatic Access

```php
use Ashrafic\AiOrbit\Models\BudgetAlert;
use Ashrafic\AiOrbit\Services\BudgetMonitor;

// Create an alert
BudgetAlert::create([
    'threshold_amount' => 500.00,
    'period' => 'monthly',
    'channels' => ['mail'],
    'enabled' => true,
]);

// Check all alerts
$monitor = app(BudgetMonitor::class);
$monitor->check('monthly');
```

## Disabling Alerts

Toggle the **Enabled** switch on any alert to disable it without deleting it.

To disable the entire budget alert system:

```php
// config/ai-orbit.php
'budget' => [
    'enabled' => false,
],
```

Or via `.env`:

```env
ORBIT_BUDGET_ENABLED=false
```

## Best Practices

1. **Set realistic thresholds** — Base them on your expected monthly AI budget
2. **Use monthly alerts for budgeting** — Daily alerts are better for spike detection
3. **Monitor the alert history** — Check `last_triggered_at` to see when alerts fired
4. **Combine with Provider Health** — If costs spike, check if a provider is failing and retrying

## Customization

Override the budget alerts view:

```bash
php artisan vendor:publish --tag=ai-orbit-views
```

Then edit `resources/views/vendor/laravel-ai-orbit/livewire/budget-alerts.blade.php`.

To customize the notification email, extend the `BudgetExceeded` notification class:

```php
use Ashrafic\AiOrbit\Notifications\BudgetExceeded;

class CustomBudgetExceeded extends BudgetExceeded
{
    public function toMail(object $notifiable): MailMessage
    {
        return parent::toMail($notifiable)
            ->cc('finance@company.com');
    }
}
```

You can also override the built-in email templates by publishing views:

```bash
php artisan vendor:publish --tag=ai-orbit-views
```

Then edit:
- `resources/views/vendor/laravel-ai-orbit/emails/budget-exceeded.blade.php` — HTML email
- `resources/views/vendor/laravel-ai-orbit/emails/budget-exceeded-text.blade.php` — Plain-text email

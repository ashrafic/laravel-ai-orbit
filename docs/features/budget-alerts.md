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
| **Channels** | How you want to be notified | Mail (Slack, etc. configurable) |
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

1. Aggregating token usage for the period via `TokenAggregator`
2. Applying pricing rules via `CostCalculator`
3. Summing total costs

```php
$aggregator = app(TokenAggregator::class);
$stats = $aggregator->todayStats('month');

$calculator = app(CostCalculator::class);
$cost = $calculator->calculate('gpt-4', $stats['input_tokens'], $stats['output_tokens']);

$currentSpend = $cost['total'];
```

## Notification Channels

### Mail

By default, notifications are sent to the configured mail from address:

```php
Notification::route('mail', config('mail.from.address'))
    ->notify(new BudgetExceeded($alert, $currentSpend));
```

The email includes:
- The period (daily/weekly/monthly)
- Current spending amount
- The threshold amount
- A link to the Orbit dashboard

### Custom Channels

Configure additional channels in `config/ai-orbit.php`:

```php
'budget' => [
    'enabled' => true,
    'notification_channels' => ['mail', 'slack'],
],
```

To add Slack support, create a custom notification channel or use Laravel's Slack notification routing.

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

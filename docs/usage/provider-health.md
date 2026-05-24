# Provider Health

Provider Health monitors the reliability and performance of each AI provider you use. Track success rates, error counts, rate limit hits, and average latency — all in one place.

## Access

Navigate to `/ai-orbit/usage/health`.

## Health Metrics

For each provider, Orbit displays:

| Metric | Description | Healthy Threshold |
|:---|:---|:---|
| **Total Requests** | Number of API calls in the period | N/A |
| **Success Rate** | Percentage of requests without errors | >= 95% |
| **Error Count** | Total failed requests | 0 ideal |
| **Rate Limit Count** | 429 / rate limit errors | 0 ideal |
| **Avg Latency** | Mean response time in milliseconds | < 2000ms |
| **P50 / P95 / P99** | Latency percentiles | < 2000ms |
| **Status** | Overall health badge | Healthy / Degraded / Unhealthy |

### Status Levels

| Status | Success Rate | Color |
|:---|:---|:---|
| **Healthy** | >= 95% | Green |
| **Degraded** | 80% – 94% | Yellow |
| **Unhealthy** | < 80% | Red |

## Time Periods

Switch between analysis windows:

- **Last 24 Hours** — Immediate issue detection
- **Last 7 Days** — Weekly trend analysis
- **Last 30 Days** — Monthly reliability review

## How It Works

### Data Source

Provider Health merges data from two sources for complete coverage:

1. **`orbit_ai_runs`** — One-off SDK calls with provider, model, latency, and status stored directly in typed columns
2. **`agent_conversation_messages`** — Multi-turn conversation data with provider information extracted from the `meta` JSON column

Both sources store identical provider values (e.g., `'openai'`). Runs store the provider in a `VARCHAR` column, while conversations store it as a JSON string inside the `meta` `TEXT` column.

Example conversation metadata:

```json
{
  "provider": "openai",
  "model": "gpt-4",
  "latency_ms": 1240,
  "error": null
}
```

### Error Detection

Errors are detected by:

1. Messages with `role = 'tool'` (tool execution failures)
2. Messages where `meta.error` is not null
3. Rate limits detected via error message patterns (`rate limit`, `429`, `too many`)

### Latency Calculation

Average latency and percentiles are calculated from both data sources:

- **From runs**: Direct `latency_ms` column on `orbit_ai_runs`
- **From conversations**: `JSON_EXTRACT(meta, '$.latency_ms')` on `agent_conversation_messages`

Percentiles (P50, P95, P99) are computed from the merged latency distribution. If latency data is not available, it shows as 0.

### Provider Extraction

Provider names are extracted from both sources:

- **Runs**: Direct `provider` column
- **Conversations**: `REPLACE(JSON_EXTRACT(meta, '$.provider'), '"', '')`

NULL or empty providers are filtered out to ensure clean aggregation.

## Programmatic Access

```php
use Ashrafic\AiOrbit\Services\ProviderHealthChecker;

$checker = app(ProviderHealthChecker::class);

// Get health metrics for the last 7 days
$metrics = $checker->getHealthMetrics('7d');

foreach ($metrics as $metric) {
    $metric['provider'];        // 'openai'
    $metric['total_requests'];  // 1523
    $metric['success_rate'];    // 98.5
    $metric['error_count'];     // 23
    $metric['rate_limit_count'];// 2
    $metric['avg_latency_ms'];  // 1240.50
    $metric['status'];          // 'healthy'
}
```

## Use Cases

### Provider Downtime Detection
Check the 24-hour view to see if a provider is currently experiencing issues. Switch to an alternative provider if success rates drop.

### Latency Comparison
Compare average latency across providers to identify the fastest option for your use case.

### Rate Limit Monitoring
If rate limit counts are high, consider implementing request throttling or upgrading your provider plan.

### Historical Reliability
Use the 30-day view to choose a primary provider based on long-term reliability rather than short-term performance.

### Alert Integration
Combine Provider Health with Budget Alerts — if error rates spike, your costs may also spike due to retries.

## Notifications

Orbit includes a `ProviderDown` notification that can be integrated into your monitoring:

```php
use Ashrafic\AiOrbit\Notifications\ProviderDown;

// Send notification when a provider's error rate exceeds a threshold
$errorRate = 15.0; // 15%
Notification::route('mail', config('mail.from.address'))
    ->notify(new ProviderDown('openai', $errorRate));
```

The email includes:
- Provider name
- Current error rate
- A link to the Provider Health page

## Customization

Override the provider health view:

```bash
php artisan vendor:publish --tag=ai-orbit-views
```

Then edit `resources/views/vendor/ai-orbit/livewire/provider-health.blade.php`.

## Best Practices

1. **Check daily** — Review the 24-hour view each morning
2. **Set up alerts** — Integrate `ProviderDown` notifications with your monitoring stack
3. **Track trends** — Use the 7-day and 30-day views to identify degrading providers
4. **Compare providers** — Use latency and success rate to choose the best provider for each agent
5. **Investigate rate limits** — High rate limit counts indicate you need throttling or a plan upgrade

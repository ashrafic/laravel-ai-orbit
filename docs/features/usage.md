# Usage & Analytics

Orbit's Usage & Analytics section gives you complete visibility into your AI spending, token consumption, and operational trends. Make data-driven decisions about model selection, agent optimization, and budget planning.

## Today's Stats

Access at `/ai-orbit/usage`. A quick overview of today's activity with the same stats cards as the dashboard.

## Full Analytics Dashboard

Access at `/ai-orbit/usage/dashboard`. The full analytics dashboard provides:

### Time Period Selection

Analyze data across different time windows:

- **Last 7 Days** — Short-term trends
- **Last 30 Days** — Monthly analysis
- **This Month** — Calendar-month view
- **All Time** — Complete historical data

### Grouping Options

Break down data by different dimensions:

| Group By | What You See |
|:---|:---|
| **Model** | Token and cost breakdown per AI model |
| **Provider** | Usage split across AI providers (OpenAI, Anthropic, etc.) |
| **Agent** | Per-agent consumption and cost |

### Cost Calculation

Costs are calculated using the Pricing Matrix (see [Pricing Matrix](/features/pricing-matrix)). If no pricing rule exists for a model, cost shows as $0.

The total cost is the sum of:

```
(input_tokens / 1,000,000) × input_cost_per_1m
+
(output_tokens / 1,000,000) × output_cost_per_1m
```

### Visual Charts

Interactive charts display:

- **Token usage over time** — Input vs. output tokens per day
- **Cost over time** — Daily spending trends
- **Agent distribution** — Pie chart of agent usage
- **Model comparison** — Bar chart comparing model efficiency

Charts are rendered via CDN-loaded chart libraries (ApexCharts or Chart.js) — no build step required.

## How It Works

### TokenAggregator

The `TokenAggregator` service powers all usage analytics:

```php
use Ashrafic\AiOrbit\Services\TokenAggregator;

$aggregator = app(TokenAggregator::class);

// Get stats for a period
$stats = $aggregator->todayStats('7d');
// Returns: total_conversations, total_messages, input_tokens, output_tokens, provider_count, agent_count

// Get breakdown by dimension
$breakdown = $aggregator->agentBreakdown('30d', groupBy: 'model');
// Returns collection of: agent, message_count, model, input_tokens, output_tokens, total
```

### CostCalculator

The `CostCalculator` converts tokens to currency:

```php
use Ashrafic\AiOrbit\Services\CostCalculator;

$calculator = app(CostCalculator::class);

// Calculate cost for a specific model
$cost = $calculator->calculate('gpt-4', inputTokens: 156, outputTokens: 89);
// Returns: input_cost, output_cost, total, currency

// Calculate total cost for a set of conversations
$total = $calculator->calculateForConversations($conversations);
```

### Safe Querying

Orbit safely checks for table and column existence:

- If `agent_conversations` doesn't exist, conversation count returns 0
- If `agent_conversation_messages` doesn't exist, message count returns 0
- If the `usage` column doesn't exist, token counts return 0
- If the `agent` column doesn't exist, agent breakdown returns empty
- If the `meta` column doesn't exist, provider count returns 0

## Use Cases

### Monthly Cost Review
Switch to "This Month" view, group by model, and review which models are driving costs. Consider switching high-volume agents to cheaper models.

### Provider Comparison
Group by provider to see usage distribution across OpenAI, Anthropic, Google, etc. Identify opportunities to consolidate or diversify.

### Agent Optimization
Group by agent to find your most expensive agents. Investigate whether prompt engineering or model downgrading could reduce costs.

### Capacity Planning
Use "All Time" view to identify growth trends and forecast future spending.

### ROI Analysis
Combine cost data with business metrics (conversions, support tickets resolved) to calculate per-interaction ROI.

## Customization

Override the analytics views:

```bash
php artisan vendor:publish --tag=ai-orbit-views
```

Then edit:
- `resources/views/vendor/laravel-ai-orbit/usage/index.blade.php` — Today's stats
- `resources/views/vendor/laravel-ai-orbit/usage/dashboard.blade.php` — Full analytics
- `resources/views/vendor/laravel-ai-orbit/usage/dashboard-livewire.blade.php` — Livewire component

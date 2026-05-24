# Pricing Matrix

The Pricing Matrix lets you define and manage per-model token pricing. Orbit uses these rules to calculate costs across the dashboard, analytics, and budget alerts.

## Access

Navigate to `/ai-orbit/usage/pricing`.

## Pricing Rules

Each pricing rule defines the cost for a specific model:

| Field | Description | Example |
|:---|:---|:---|
| **Model** | The model identifier | `gpt-4`, `claude-3-opus` |
| **Provider** | Optional provider name | `openai`, `anthropic` |
| **Input Cost** | Cost per 1 million input tokens | `30.00` for $30/1M tokens |
| **Output Cost** | Cost per 1 million output tokens | `60.00` for $60/1M tokens |
| **Currency** | ISO currency code | `USD`, `EUR`, `GBP` |

## Adding a Rule

Click **"Add Rule"** and fill in the form:

```
Model:        gpt-4-turbo
Provider:     openai
Input Cost:   10.00
Output Cost:  30.00
Currency:     USD
```

The rule is saved immediately and used for all subsequent cost calculations.

## Cost Calculation

When calculating costs, Orbit:

1. Looks for an exact model match
2. Falls back to a partial match (LIKE query)
3. Returns $0 if no rule is found

```php
$cost = $calculator->calculate('gpt-4', inputTokens: 156, outputTokens: 89);
// With gpt-4 at $30/1M in, $60/1M out:
// input_cost = 156 / 1,000,000 * 30 = $0.00468
// output_cost = 89 / 1,000,000 * 60 = $0.00534
// total = $0.01002
```

## Editing Rules

Click the edit icon on any rule to modify it. Changes apply retroactively to analytics views (they recalculate on each page load).

## Deleting Rules

Click the delete icon to remove a rule. Conversations that previously used this rule will show $0 cost until a new rule is added.

## Importing Provider Pricing

Orbit does not ship with pre-configured pricing. We recommend adding rules for all models you use. You can find current pricing on your provider's documentation:

- [OpenAI Pricing](https://openai.com/pricing)
- [Anthropic Pricing](https://www.anthropic.com/pricing)
- [Google AI Pricing](https://ai.google.dev/pricing)

## Programmatic Access

Manage pricing rules programmatically:

```php
use Ashrafic\AiOrbit\Models\PricingRule;

// Create a rule
PricingRule::create([
    'model' => 'gpt-4',
    'provider' => 'openai',
    'input_cost_per_1m' => 30.00,
    'output_cost_per_1m' => 60.00,
    'currency' => 'USD',
]);

// Find a rule
$rule = PricingRule::where('model', 'gpt-4')->first();

// Calculate cost
$calculator = app(\Ashrafic\AiOrbit\Services\CostCalculator::class);
$cost = $calculator->calculate('gpt-4', 156, 89);
```

## Multi-Currency Support

Each rule has its own currency. The dashboard displays costs in the currency symbol configured in `config/ai-orbit.php`:

```php
'currency_symbol' => '$',
```

If you use multiple currencies, consider standardizing to one currency for consistent reporting.

## Best Practices

1. **Add rules for all models** before starting cost tracking — otherwise costs show as $0
2. **Update rules when provider pricing changes** — Orbit does not auto-update pricing
3. **Use consistent model names** — match the identifiers your agents use in metadata
4. **Set currency per provider** if you work with providers billing in different currencies

## Customization

Override the pricing matrix view:

```bash
php artisan vendor:publish --tag=ai-orbit-views
```

Then edit `resources/views/vendor/ai-orbit/livewire/pricing-matrix.blade.php`.

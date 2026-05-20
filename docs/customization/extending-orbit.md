# Extending Orbit

Orbit is designed to be extended. Use contracts, service bindings, and custom implementations to tailor the dashboard to your needs.

## Contracts

Orbit defines contracts (interfaces) for core services:

### AgentRegistryContract

```php
use Ashrafic\AiOrbit\Contracts\AgentRegistryContract;

interface AgentRegistryContract
{
    public function all(): Collection;
    public function find(string $class): ?array;
    public function refresh(): void;
}
```

Bind your custom implementation:

```php
// In AppServiceProvider
use Ashrafic\AiOrbit\Contracts\AgentRegistryContract;
use App\Services\CustomAgentRegistry;

public function register(): void
{
    $this->app->singleton(AgentRegistryContract::class, CustomAgentRegistry::class);
}
```

### Custom Agent Registry Example

```php
namespace App\Services;

use Ashrafic\AiOrbit\Contracts\AgentRegistryContract;
use Illuminate\Support\Collection;

class CustomAgentRegistry implements AgentRegistryContract
{
    public function all(): Collection
    {
        // Load agents from your own source (database, API, etc.)
        return collect([
            'App\AI\Agents\SupportAgent',
            'App\AI\Agents\SalesAgent',
        ]);
    }

    public function find(string $class): ?array
    {
        // Return custom metadata
        return [
            'class' => $class,
            'instructions' => 'Custom instructions',
            'tools' => [],
            'has_schema' => false,
        ];
    }

    public function refresh(): void
    {
        // Clear any caches
    }
}
```

## Extending Services

### Custom Cost Calculator

```php
use Ashrafic\AiOrbit\Services\CostCalculator;

class CustomCostCalculator extends CostCalculator
{
    public function calculate(string $model, int $inputTokens, int $outputTokens): array
    {
        // Add your own pricing logic
        if ($model === 'custom-model') {
            return [
                'input_cost' => 0,
                'output_cost' => 0,
                'total' => 0,
                'currency' => 'USD',
            ];
        }

        return parent::calculate($model, $inputTokens, $outputTokens);
    }
}
```

Bind it:

```php
app()->singleton(CostCalculator::class, CustomCostCalculator::class);
```

### Custom Export Service

```php
use Ashrafic\AiOrbit\Services\ExportService;

class CustomExportService extends ExportService
{
    public function toPest(string $conversationId): string
    {
        $content = parent::toPest($conversationId);
        
        // Add custom assertions or setup
        $content .= "\n    // Custom assertion\n";
        $content .= "    expect(true)->toBeTrue();\n";
        
        return $content;
    }
    
    public function toXml(string $conversationId): string
    {
        // Add a completely new export format
        $conversation = $this->repository->find($conversationId);
        // ... XML generation logic
    }
}
```

## Custom Livewire Components

Create entirely new Livewire components that integrate with Orbit:

```php
namespace App\Http\Livewire;

use Ashrafic\AiOrbit\Services\ConversationRepository;
use Livewire\Component;

class CustomAnalytics extends Component
{
    public function render()
    {
        $repository = app(ConversationRepository::class);
        
        // Your custom analytics logic
        $stats = $this->calculateCustomStats();
        
        return view('livewire.custom-analytics', [
            'stats' => $stats,
        ]);
    }
}
```

Register it:

```php
use Livewire\Livewire;
use App\Http\Livewire\CustomAnalytics;

Livewire::component('custom-analytics', CustomAnalytics::class);
```

Use it in a published view:

```blade
<livewire:custom-analytics />
```

## Custom Middleware

Add custom middleware to Orbit routes by extending the middleware stack:

```php
// config/ai-orbit.php
'middleware' => ['web', 'auth', 'custom-middleware'],
```

## Custom Notifications

Extend Orbit's notifications:

```php
use Ashrafic\AiOrbit\Notifications\BudgetExceeded;

class SlackBudgetExceeded extends BudgetExceeded
{
    public function via(object $notifiable): array
    {
        return ['mail', 'slack'];
    }
    
    public function toSlack(object $notifiable): SlackMessage
    {
        return (new SlackMessage)
            ->content("Budget alert: {$this->alert->period} threshold exceeded!");
    }
}
```

## Events

Orbit dispatches events that you can listen to:

### Override Events

Listen for Livewire events in your components:

```php
// In your custom component
#[On('tool-call-counts-updated')]
public function handleToolCallCounts(array $counts): void
{
    // React to tool call updates
}
```

## Best Practices

1. **Extend, don't replace** — Where possible, extend existing services rather than replacing them entirely
2. **Use contracts** — Program against interfaces (`AgentRegistryContract`) rather than concrete classes
3. **Test extensions** — Write tests for your custom implementations
4. **Document changes** — Keep a record of what you've extended for future maintainers
5. **Watch for updates** — Major Orbit updates may change service signatures; test after updating

# Routes

Complete reference for all Orbit dashboard routes.

## Route Configuration

All routes are prefixed with the configured path (default: `ai-orbit`) and protected by the `Authorize` middleware.

```php
Route::group([
    'domain' => config('ai-orbit.domain'),
    'prefix' => config('ai-orbit.path', 'ai-orbit'),
    'middleware' => ['web', Authorize::class],
], function () {
    // Orbit routes...
});
```

## Route List

### Dashboard

| Method | URI | Name | Description |
|:---|:---|:---|:---|
| GET | `/` | `orbit.dashboard` | Main dashboard with stats and breakdowns |

### Conversations

| Method | URI | Name | Description |
|:---|:---|:---|:---|
| GET | `/conversations` | `orbit.conversations.index` | Thread explorer with filters |
| GET | `/conversations/{id}` | `orbit.conversations.show` | Message timeline for a conversation |

### Playground

| Method | URI | Name | Description |
|:---|:---|:---|:---|
| GET | `/playground` | `orbit.playground.index` | List of discovered agents |
| GET | `/playground/{agent}` | `orbit.playground.show` | Sandbox for a specific agent |

### Traces

| Method | URI | Name | Description |
|:---|:---|:---|:---|
| GET | `/traces/{id}` | `orbit.traces.show` | Execution trace for a conversation |

### Runs

| Method | URI | Name | Description |
|:---|:---|:---|:---|
| GET | `/runs` | `orbit.runs.index` | Run Explorer with search, filters, and pagination |
| GET | `/runs/{id}` | `orbit.runs.show` | Run detail view with metadata and payload inspector |

### Usage & Analytics

| Method | URI | Name | Description |
|:---|:---|:---|:---|
| GET | `/usage` | `orbit.usage.index` | Unified usage dashboard with stats, charts, and links |
| GET | `/usage/pricing` | `orbit.usage.pricing` | Pricing matrix management |
| GET | `/usage/alerts` | `orbit.usage.alerts` | Budget alert configuration |
| GET | `/usage/health` | `orbit.usage.health` | Provider health monitoring |

### Prompt Lab

| Method | URI | Name | Description |
|:---|:---|:---|:---|
| GET | `/prompt-lab` | `orbit.prompt-lab.index` | Prompt Lab session list |
| GET | `/prompt-lab/session/{id}` | `orbit.prompt-lab.show` | View a specific session |

### Audit

| Method | URI | Name | Description |
|:---|:---|:---|:---|
| GET | `/audit` | `orbit.audit.index` | Audit dashboard with PII scanner |

### Exports

| Method | URI | Name | Description |
|:---|:---|:---|:---|
| POST | `/export/pest/{id}` | `orbit.export.pest` | Export conversation as Pest test |
| POST | `/export/json/{id}` | `orbit.export.json` | Export conversation as JSONL |

### Prompts

| Method | URI | Name | Description |
|:---|:---|:---|:---|
| GET | `/prompts` | `orbit.prompts.index` | Prompt Library |

## Custom Domains

If you've configured a custom domain in `config/ai-orbit.php`:

```php
'domain' => 'orbit.myapp.test',
```

Routes will be available at:

```
http://orbit.myapp.test/
http://orbit.myapp.test/conversations
http://orbit.myapp.test/playground
```

## Generating URLs

Use route names to generate URLs programmatically:

```php
// Named routes
route('orbit.dashboard');           // /ai-orbit
route('orbit.conversations.show', ['id' => 'abc-123']); // /ai-orbit/conversations/abc-123
route('orbit.playground.show', ['agent' => 'App\AI\Agents\SupportAgent']);
route('orbit.traces.show', ['id' => 'abc-123']);
```

## Customizing Routes

To add custom routes to the Orbit route group, create a service provider that loads additional routes:

```php
// app/Providers/OrbitRouteServiceProvider.php
namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class OrbitRouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::group([
            'prefix' => config('ai-orbit.path', 'ai-orbit'),
            'middleware' => array_merge(
                config('ai-orbit.middleware', ['web']),
                [\Ashrafic\AiOrbit\Http\Middleware\Authorize::class]
            ),
        ], function () {
            Route::get('/custom-report', [CustomReportController::class, 'index']);
        });
    }
}
```

## Route Middleware

All routes automatically include:

1. **Configured middleware** — From `config/ai-orbit.php` (`middleware` key)
2. **Authorize middleware** — Automatically appended; checks the `viewAiOrbit` Gate

To add additional middleware, update the config:

```php
'middleware' => ['web', 'auth', 'verified'],
```

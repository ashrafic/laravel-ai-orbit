# Laravel AI Orbit

Observability, management, and developer playground for the [Laravel AI SDK](https://github.com/laravel/ai).

## Features

- **Dashboard** — Today's conversation and token statistics with agent breakdown charts
- **Conversations** — Searchable thread explorer and chat-style message timeline with raw payload inspection
- **Playground** — Interactive agent sandbox with inspector sidebar showing instructions, tools, and schema
- **Traces** — Execution timeline with per-step latency tracking and tool call details
- **Usage** — Token consumption metrics with agent attribution
- **Dark Mode** — Polished dark and light themes with localStorage persistence (dark by default)

## Requirements

- PHP 8.2+
- Laravel 11+ / 12+ / 13+
- [Laravel AI SDK](https://github.com/laravel/ai) ^0.6

## Installation

```bash
composer require ashraful19/laravel-ai-orbit
```

The package auto-discovers via Laravel's package discovery. No manual service provider registration needed.

Run the Laravel AI SDK migrations if you haven't already:

```bash
php artisan vendor:publish --tag=ai-migrations
php artisan migrate
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=ai-orbit-config
```

Available options:
- `path` — Dashboard URI prefix (default: `ai-orbit`)
- `auth_guard` — Auth guard for access control (default: `web`)
- `middleware` — Route middleware stack (default: `['web', 'auth']`)
- `domain` — Custom subdomain (default: `null`)
- `agent_directories` — Directories scanned for agent classes
- `registry_cache_ttl` — Agent discovery cache duration (seconds)

## Authorization

By default, the Orbit dashboard is accessible in the `local` environment only.
For production, define a Gate in your `AppServiceProvider`:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('viewAiOrbit', function ($user) {
    return $user->isAdmin();
});
```

## Routes

| Method | URI                          | Description         |
|--------|------------------------------|---------------------|
| GET    | `/ai-orbit`                  | Dashboard           |
| GET    | `/ai-orbit/conversations`    | Thread Explorer     |
| GET    | `/ai-orbit/conversations/{id}` | Message Timeline    |
| GET    | `/ai-orbit/playground`       | Agent List          |
| GET    | `/ai-orbit/playground/{agent}` | Agent Sandbox       |
| GET    | `/ai-orbit/traces/{id}`      | Execution Trace     |
| GET    | `/ai-orbit/usage`            | Today's Stats       |

## Publishing Assets

```bash
# Blade views (customize the UI)
php artisan vendor:publish --tag=ai-orbit-views

# CSS/JS assets (if needed)
php artisan vendor:publish --tag=ai-orbit-assets
```

## Creating Agents

Place your agent classes in `app/AI/Agents/` (configurable). They must implement `Laravel\Ai\Contracts\Agent`:

```php
namespace App\AI\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Laravel\Ai\Concerns\RemembersConversations;

class SupportAgent implements Agent
{
    use Promptable;
    use RemembersConversations;

    public function instructions(): string
    {
        return 'You are a helpful support agent...';
    }
}
```

## Pro

For advanced features, check out [Laravel AI Orbit Pro](https://anystack.com/products/ashraful19/laravel-ai-orbit-pro):

- **Arena** — Side-by-side model comparison
- **Step-Through Debugger** — Pause on tool calls, edit args, mock responses
- **Full Analytics** — Historical charts (daily/weekly/monthly)
- **Pricing Matrix** — Editable cost per model
- **Budget Alerts** — Threshold notifications via email/Slack/webhook
- **Audit Log** — Security and compliance tracking

## Development

```bash
composer install
composer test        # Run Pest test suite
./vendor/bin/pint    # Fix code style (PSR-12)
./vendor/bin/pint --test  # Check style only
./vendor/bin/phpstan analyse  # Static analysis (level 8)
```

## License

MIT License. See [LICENSE](LICENSE) for details.

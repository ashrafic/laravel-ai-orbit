<p align="center">
    <img src="https://raw.githubusercontent.com/ashraful19/laravel-ai-orbit/main/art/logo.svg" alt="Laravel AI Orbit" width="400">
</p>

<p align="center">
    <a href="https://packagist.org/packages/ashraful19/laravel-ai-orbit"><img src="https://img.shields.io/packagist/v/ashraful19/laravel-ai-orbit.svg?style=flat-square" alt="Latest Version"></a>
    <a href="https://packagist.org/packages/ashraful19/laravel-ai-orbit"><img src="https://img.shields.io/packagist/dt/ashraful19/laravel-ai-orbit.svg?style=flat-square" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/ashraful19/laravel-ai-orbit"><img src="https://img.shields.io/packagist/php-v/ashraful19/laravel-ai-orbit.svg?style=flat-square" alt="PHP Version"></a>
    <a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="License"></a>
</p>

# Laravel AI Orbit

**Observability, management, and developer playground for the [Laravel AI SDK](https://github.com/laravel/ai).**

Orbit gives you a Telescope-style dashboard to see everything your AI agents are doing — conversations, execution traces, token usage, and an interactive sandbox to test agents without writing code.

---

## Quick Start

```bash
composer require ashraful19/laravel-ai-orbit
php artisan vendor:publish --tag=ai-migrations
php artisan migrate
```

Visit `/ai-orbit` in your browser. If you haven't installed the Laravel AI SDK yet, require it first: `composer require laravel/ai`.

## Features

| Section | What it does |
|---------|-------------|
| **Dashboard** | Today's stats: conversations, messages, input/output tokens. Agent breakdown with Chart.js doughnut chart. Quick-link cards to all sections. |
| **Conversations** | Paginated, filterable table of every recorded conversation. Chat-style message timeline with role-based styling (user/assistant/system/tool). Raw JSON payload inspector for debugging. |
| **Playground** | Interactive sandbox to chat with any agent in real time. Inspector sidebar showing agent instructions, registered tools, and structured output schema. |
| **Traces** | Visual execution timeline with color-coded steps. Per-step latency in milliseconds. Expandable tool call arguments and responses. |
| **Usage** | Today's token consumption and per-agent attribution. Pro teaser cards for analytics, pricing matrix, budget alerts, and provider health. |

## Requirements

| Dependency | Version |
|-----------|---------|
| PHP | 8.2+ |
| Laravel | 11+ / 12+ / 13+ |
| [laravel/ai](https://github.com/laravel/ai) | ^0.6 |
| Livewire | ^4.0 |

## Installation

```bash
composer require ashraful19/laravel-ai-orbit
```

The package auto-discovers via Laravel's package discovery. No manual service provider registration needed.

### SDK Migrations

Orbit reads from the Laravel AI SDK's `agent_conversations` and `agent_conversation_messages` tables. Publish and run those migrations:

```bash
php artisan vendor:publish --tag=ai-migrations
php artisan migrate
```

> [!NOTE]
> Orbit adds **zero** migrations of its own. It reads entirely from the SDK's existing tables.

### Health Check

If the SDK tables are not yet migrated, Orbit shows a dismissible warning banner on every dashboard page telling you to run `php artisan migrate`. It also logs a warning to your application log.

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=ai-orbit-config
```

| Key | Default | Description |
|-----|---------|-------------|
| `path` | `ai-orbit` | Dashboard URI prefix. Configurable via `AI_ORBIT_PATH` env. |
| `auth_guard` | `web` | Auth guard used for access control. |
| `middleware` | `['web', 'auth']` | Route middleware applied to all dashboard routes. |
| `domain` | `null` | Optional subdomain for the dashboard. |
| `agent_directories` | `['app/AI/Agents', 'app/Ai/Agents']` | Paths scanned for agent classes. Relative to `base_path()`. |
| `registry_cache_ttl` | `3600` | Seconds to cache discovered agent metadata. Set to `0` to disable. |

## Authorization

By default, the Orbit dashboard is accessible **only in the `local` environment**.

For production, define a `viewAiOrbit` Gate in your `AppServiceProvider`:

```php
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::define('viewAiOrbit', function ($user) {
        return $user->isAdmin();
    });
}
```

## Routes

All routes are prefixed with the configured `path` (default `ai-orbit`).

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/` | `orbit.dashboard` | Dashboard |
| GET | `/conversations` | `orbit.conversations.index` | Thread Explorer |
| GET | `/conversations/{id}` | `orbit.conversations.show` | Message Timeline |
| GET | `/playground` | `orbit.playground.index` | Agent List |
| GET | `/playground/{agent}` | `orbit.playground.show` | Agent Sandbox |
| GET | `/traces/{id}` | `orbit.traces.show` | Execution Trace |
| GET | `/usage` | `orbit.usage.index` | Today's Stats |

## Customization

### Views

Publish and override any Blade view:

```bash
php artisan vendor:publish --tag=ai-orbit-views
```

Views are published to `resources/views/vendor/laravel-ai-orbit/`. All views extend the base layout via `<x-laravel-ai-orbit::layout>`.

### Config

```bash
php artisan vendor:publish --tag=ai-orbit-config
```

Publishes to `config/ai-orbit.php`.

### Assets

```bash
php artisan vendor:publish --tag=ai-orbit-assets
```

Publishes to `public/vendor/laravel-ai-orbit/`.

## Creating Agents

Orbit discovers agent classes by scanning the directories configured in `agent_directories`. Your agent must implement `Laravel\Ai\Contracts\Agent`.

```php
namespace App\AI\Agents;

use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;

class SupportAgent implements Agent, HasTools
{
    use Promptable;
    use RemembersConversations;

    public function instructions(): string
    {
        return <<<'INSTRUCTIONS'
            You are a helpful customer support agent. Be concise and empathetic.
            Always verify the user's identity before accessing account data.
        INSTRUCTIONS;
    }

    public function tools(): iterable
    {
        return [
            new \App\AI\Tools\LookupOrder,
            new \App\AI\Tools\RefundOrder,
        ];
    }
}
```

Once saved, the agent appears in the Playground and its conversations are tracked in the dashboard.

## Dark Mode

Orbit ships with a polished dark and light theme. Dark mode is the default. The toggle button in the top bar switches between them and persists your choice in `localStorage`.

## Testing

Orbit is compatible with the Laravel AI SDK's testing utilities:

```php
use Laravel\AI\Facades\AI;

AI::fake([
    SupportAgent::class => 'Your fake response here.',
]);
```

Orbit itself is tested with Pest:

```bash
composer test
```

## Pro

[Laravel AI Orbit Pro](https://anystack.com/products/ashraful19/laravel-ai-orbit-pro) extends Orbit with advanced features:

- **Arena** — Side-by-side prompt comparison across up to 3 models
- **Step-Through Debugger** — Pause on tool calls, edit arguments, inject mock responses
- **Full Analytics** — Historical charts (daily, weekly, monthly), cost forecasting
- **Pricing Matrix** — Editable cost-per-1K-tokens for every model and provider
- **Budget Alerts** — Threshold notifications via email, Slack, or webhook
- **Audit Log** — Security audit, PII detection, data retention rules
- **Prompt Library** — Save and reuse prompt templates
- **Export Tools** — Generate Pest tests with `AI::fake()`, export to JSON/JSONL

Pro extends free — controllers, Livewire components, and blade views inherit from the free package. Zero duplication.

## Development

```bash
git clone git@github.com:ashraful19/laravel-ai-orbit.git
cd laravel-ai-orbit
composer install
```

| Command | Purpose |
|---------|---------|
| `composer test` | Run the Pest test suite |
| `./vendor/bin/pint` | Auto-fix code style (PSR-12) |
| `./vendor/bin/pint --test` | Check style without fixing |
| `./vendor/bin/phpstan analyse` | Static analysis at level 8 |

## Versioning

Orbit follows [Semantic Versioning](https://semver.org/). The `FeatureGate` contract's public API is considered stable — signature changes require a major version bump.

## Security

If you discover a security vulnerability, please email ashrafulislamtushar@gmail.com instead of opening a public issue.

## Credits

- [Ashraful Islam Tushar](https://github.com/ashraful19)
- Built on top of [Laravel AI SDK](https://github.com/laravel/ai), [Livewire](https://livewire.laravel.com/), and [Chart.js](https://www.chartjs.org/)

## License

MIT License. See [LICENSE](LICENSE) for details.

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

Observability, management, and developer playground for the [Laravel AI SDK](https://github.com/laravel/ai).

Orbit gives you a Telescope-style dashboard to see everything your AI agents are doing — conversations, execution traces, token usage, and an interactive sandbox to test agents without writing code.

## Installation

```bash
composer require ashraful19/laravel-ai-orbit
```

The package auto-discovers. No manual registration needed.

> **Prerequisites:** Install and migrate the [Laravel AI SDK](https://github.com/laravel/ai) first. Orbit reads from the SDK's `agent_conversations` and `agent_conversation_messages` tables and adds zero migrations of its own.

Visit `/ai-orbit` in your browser.

## Sections

| Section | Description |
|---------|-------------|
| **Dashboard** | Today's stats — conversations, messages, input/output tokens — with an agent breakdown chart |
| **Conversations** | Searchable, filterable table of all conversations. Chat-style message timeline with raw JSON payload inspector |
| **Playground** | Interactive sandbox to chat with any discovered agent. Inspector sidebar shows instructions, tools, and schema |
| **Traces** | Visual execution timeline with color-coded steps, per-step latency, and expandable tool call details |
| **Usage** | Today's token consumption with per-agent attribution |

## Configuration

```bash
php artisan vendor:publish --tag=ai-orbit-config
```

| Key | Default | Description |
|-----|---------|-------------|
| `path` | `ai-orbit` | Dashboard URI prefix |
| `auth_guard` | `web` | Auth guard for access control |
| `middleware` | `['web', 'auth']` | Route middleware stack |
| `domain` | `null` | Custom subdomain |
| `agent_directories` | `['app/AI/Agents', 'app/Ai/Agents']` | Paths scanned for agent classes |
| `registry_cache_ttl` | `3600` | Agent discovery cache duration (seconds) |

## Authorization

By default, the dashboard is accessible in the `local` environment only. For production:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('viewAiOrbit', function ($user) {
    return $user->isAdmin();
});
```

## Routes

| URI | Description |
|-----|-------------|
| `/ai-orbit` | Dashboard |
| `/ai-orbit/conversations` | Thread Explorer |
| `/ai-orbit/conversations/{id}` | Message Timeline |
| `/ai-orbit/playground` | Agent List |
| `/ai-orbit/playground/{agent}` | Agent Sandbox |
| `/ai-orbit/traces/{id}` | Execution Trace |
| `/ai-orbit/usage` | Today's Stats |

## Customization

```bash
# Override any Blade view
php artisan vendor:publish --tag=ai-orbit-views

# Override config
php artisan vendor:publish --tag=ai-orbit-config

# Override assets
php artisan vendor:publish --tag=ai-orbit-assets
```

Published views land in `resources/views/vendor/laravel-ai-orbit/`.

## Dark Mode

Polished dark and light themes. Dark mode is the default. Toggle in the top bar persists via `localStorage`.

## Pro

[Laravel AI Orbit Pro](https://anystack.com/products/ashraful19/laravel-ai-orbit-pro) extends Orbit with:

- **Arena** — Side-by-side model comparison
- **Step-Through Debugger** — Pause on tool calls, edit arguments
- **Full Analytics** — Historical charts, cost forecasting
- **Pricing Matrix** — Editable cost per model
- **Budget Alerts** — Email, Slack, webhook notifications
- **Audit Log** — Security audit and PII detection

Pro extends free — never duplicates.

## Testing

Orbit uses Pest. Run the suite:

```bash
composer test
```

## Development

```bash
git clone git@github.com:ashraful19/laravel-ai-orbit.git
cd laravel-ai-orbit
composer install
```

| Command | Purpose |
|---------|---------|
| `composer test` | Run Pest test suite |
| `./vendor/bin/pint` | Fix code style |
| `./vendor/bin/phpstan analyse` | Static analysis (level 8) |

## License

MIT License. See [LICENSE](LICENSE) for details.

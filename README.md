<p align="center">
  <img src="https://raw.githubusercontent.com/ashrafic/laravel-ai-orbit/main/art/logo.svg" alt="Laravel AI Orbit" width="200">
</p>

<h1 align="center">
  <span style="color:#FF2D20">Laravel</span>
  <span style="background:linear-gradient(135deg,#6366f1,#a78bfa,#f472b6);-webkit-background-clip:text;-webkit-text-fill-color:transparent">AI Orbit</span>
</h1>

<p align="center">
  <strong>The Intelligent Control Tower for the Laravel AI SDK</strong>
</p>

<p align="center">
  <a href="https://packagist.org/packages/ashrafic/laravel-ai-orbit"><img src="https://img.shields.io/packagist/v/ashrafic/laravel-ai-orbit.svg?style=flat-square&color=6366f1" alt="Latest Version"></a>
  <a href="https://packagist.org/packages/ashrafic/laravel-ai-orbit"><img src="https://img.shields.io/packagist/dt/ashrafic/laravel-ai-orbit.svg?style=flat-square" alt="Total Downloads"></a>
  <a href="https://github.com/ashrafic/laravel-ai-orbit/actions/workflows/ci.yml"><img src="https://img.shields.io/github/actions/workflow/status/ashrafic/laravel-ai-orbit/ci.yml?style=flat-square&label=tests" alt="Tests"></a>
  <a href="https://packagist.org/packages/ashrafic/laravel-ai-orbit"><img src="https://img.shields.io/packagist/php-v/ashrafic/laravel-ai-orbit.svg?style=flat-square&color=8b5cf6" alt="PHP Version"></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="License"></a>
</p>

---

**Laravel AI Orbit** is a standalone observability dashboard and developer playground for the official [Laravel AI SDK](https://github.com/laravel/ai) (`laravel/ai` v0.6.x). Think of it as **Telescope for your AI agents** — a polished, real-time window into everything your agents are doing, with powerful tools to test, compare, and optimize them.

Built for Laravel 11+ and PHP 8.3+, Orbit installs in seconds, requires **zero frontend build steps**, and ships with a gorgeous glassmorphism UI in both dark and light modes.

---

## Features

| Category | Feature | Description |
|----------|---------|-------------|
| **Observability** | Dashboard | At-a-glance stats for conversations, messages, tokens, and agent breakdowns with configurable time periods |
| | Conversations | Searchable thread list with advanced filters, bookmarks, and chat-style message timeline with raw JSON inspector |
| | Traces | Visual execution timeline with per-step latency and expandable tool call details |
| **Playground** | Agent Sandbox | Interactive chat with any discovered agent. Intelligent dependency resolution auto-detects Eloquent models, container bindings, and scalars |
| | Live Overrides | Override system prompt, model, provider, temperature, and max tokens on the fly |
| | Multi-turn Chat | Persistent conversation sessions with the `RemembersConversations` trait |
| **Prompt Lab** | Side-by-Side | Compare up to 3 provider+model combinations on the same prompt |
| | Auto-Tagging | Automatically labels fastest, cheapest, most concise, and best-value responses |
| | Session History | Browse and revisit all past comparison sessions |
| **Usage & Cost** | Pricing Matrix | Editable per-model pricing rules with token cost configuration |
| | Analytics | Historical cost breakdowns by agent, model, and provider with interactive charts |
| | Budget Alerts | Configurable thresholds with queued email/slack notifications |
| | Provider Health | Monitor success rates, latency, and error counts per AI provider |
| **Security** | PII Detection | Built-in scanner detects emails, phones, SSNs, credit cards, and API keys in message payloads |
| | Data Retention | Configurable retention policies with dry-run previews and auto-cleanup of stale conversations |
| | Access Audit | Full activity log of dashboard access attempts |
| **Dev Tools** | Pest Export | Export conversations to Pest PHP test cases with one click |
| | JSONL Export | Export in OpenAI fine-tuning format for model training |
| | CSV Export | Export conversation data for spreadsheet analysis |
| | Prompt Library | Save, tag, and reuse prompts with full-text search |
| | Global Search | Search across all conversations, prompts, and bookmarks |
| | Agent Health | Score agents by response quality, tool usage, and error rates |

---

## Installation

Requires PHP 8.3+, Laravel 11+, and the [Laravel AI SDK](https://github.com/laravel/ai) installed with migrations run.

```bash
composer require ashrafic/laravel-ai-orbit
```

The package auto-discovers. Visit `/ai-orbit` in your browser.

> Orbit reads directly from the SDK's `agent_conversations` and `agent_conversation_messages` tables. If they don't exist yet, you'll see a friendly setup banner.

---

## Configuration

```bash
php artisan vendor:publish --tag=ai-orbit-config
```

| Key | Default | Description |
|-----|---------|-------------|
| `path` | `ai-orbit` | Dashboard URI prefix |
| `auth_guard` | `web` | Authentication guard |
| `middleware` | `['web']` | Route middleware stack |
| `domain` | `null` | Custom subdomain |
| `back_to_app_url` | `/` | "Back to App" link target |
| `agent_directories` | `['app/AI/Agents', 'app/Ai/Agents']` | Scanned agent discovery paths |
| `registry_cache_ttl` | `3600` | Agent metadata cache duration (seconds) |
| `prompt-lab.max_slots` | `3` | Max models per Prompt Lab comparison |
| `prompt-lab.timeout_seconds` | `120` | Request timeout per comparison slot |
| `budget.enabled` | `true` | Budget alert system toggle |
| `budget.notification_channels` | `['mail']` | Alert notification channels |
| `audit.enabled` | `true` | Audit & PII scanning toggle |
| `audit.retention_days` | `90` | Default data retention period |

---

## Authorization

By default, Orbit is accessible only in the `local` environment.

### Gate (Recommended)

```php
use Illuminate\Support\Facades\Gate;

Gate::define('viewAiOrbit', function ($user) {
    return $user->isAdmin();
});
```

### Middleware & Guard

```php
// config/ai-orbit.php
'middleware' => ['web', 'auth'],
'auth_guard' => 'web',
```

---

## Customization

```bash
# Override any Blade view
php artisan vendor:publish --tag=ai-orbit-views

# Override config
php artisan vendor:publish --tag=ai-orbit-config

# Override compiled assets
php artisan vendor:publish --tag=ai-orbit-assets
```

Published views land in `resources/views/vendor/laravel-ai-orbit/`.

---

## Documentation

Full documentation is available at **[ashrafic.github.io/laravel-ai-orbit](https://ashrafic.github.io/laravel-ai-orbit/)**.

---

## Testing

```bash
composer test          # Pest test suite
./vendor/bin/pint      # Code style (PSR-12)
./vendor/bin/phpstan analyse  # Static analysis (level 8)
```

---

## License

MIT License. See [LICENSE](LICENSE) for details.

---

<p align="center">
  <sub>Built with care by <a href="https://ashraficlabs.com">Ashrafic Labs</a></sub>
</p>

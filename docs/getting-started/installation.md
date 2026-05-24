# Installation

Getting started with Laravel AI Orbit takes less than a minute. The package auto-discovers and requires zero frontend build steps.

## Requirements

Before installing, make sure your environment meets the following:

| Requirement | Version | Notes |
|:---|:---|:---|
| PHP | `^8.2` | Required by the Laravel AI SDK |
| Laravel | `^11.0 \| ^12.0 \| ^13.0` | Framework version |
| Laravel AI SDK | `^0.6` | `laravel/ai` package with migrations run |
| Livewire | `^4.0` | Auto-installed as a dependency |

> **Important:** The Laravel AI SDK (`laravel/ai`) must be installed and its migrations must have been run. Orbit reads directly from the SDK's `agent_conversations` and `agent_conversation_messages` tables.

## Install via Composer

```bash
composer require ashrafic/laravel-ai-orbit
```

That's it. The package auto-registers via Laravel's package discovery. No service provider to add, no config to publish unless you want to customize.

## Run Migrations

Orbit creates its own tables for features like pricing rules, saved prompts, bookmarks, and budget alerts:

```bash
php artisan migrate
```

The following tables are created (all prefixed with `orbit_`):

| Table | Purpose |
|:---|:---|
| `orbit_pricing_rules` | Editable cost per token per model |
| `orbit_saved_prompts` | Prompt library with tags and metadata |
| `orbit_bookmarks` | Starred conversations |
| `orbit_prompt_lab_sessions` | Prompt Lab comparison history |
| `orbit_budget_alerts` | Budget thresholds and notifications |

## Access the Dashboard

Visit `/ai-orbit` in your browser:

```
http://your-app.test/ai-orbit
```

By default, Orbit is only accessible in the `local` environment. See [Authorization](/getting-started/authorization) to configure access for production.

## Health Check

If you see a friendly setup banner instead of the dashboard, it usually means the Laravel AI SDK tables haven't been created yet. Run:

```bash
php artisan migrate
```

Orbit performs an automatic health check on boot and surfaces any issues clearly in the UI.

## Next Steps

- [Configure Orbit](/getting-started/configuration) — customize the path, middleware, and features
- [Set Up Authorization](/getting-started/authorization) — control who can access the dashboard
- [Explore the Dashboard](/features/dashboard) — see what Orbit can do

# Upgrading

Orbit follows [Semantic Versioning](https://semver.org/). Upgrades within the same major version should be seamless.

## General Upgrade Steps

1. **Review the changelog** for breaking changes
2. **Run Composer update:**
   ```bash
   composer update ashrafic/laravel-ai-orbit
   ```
3. **Republish assets** (CSS, favicon, and other compiled assets):
   ```bash
   php artisan vendor:publish --tag=ai-orbit-assets --force
   ```
4. **Run migrations** if new tables were added:
   ```bash
   php artisan migrate
   ```
5. **Clear caches:**
   ```bash
   php artisan cache:clear
   php artisan view:clear
   ```

::: warning Always Republish Assets
After every update, **always republish assets** with `--force`. Orbit ships compiled CSS and favicon files that may change between releases. Skipping this step can lead to broken styling or missing icons.
:::

## Version Compatibility

| Orbit Version | Laravel | PHP | Laravel AI SDK |
|:---|:---|:---|:---|
| `^2.0` | `^12.0 \| ^13.0` | `^8.3` | `^1.0` |
| `^1.3` | `^12.0 \| ^13.0` | `^8.3` | `^0.10 \| ^0.11` |
| `^1.2` | `^12.0 \| ^13.0` | `^8.3` | `^0.6 \| ^0.7 \| ^0.8 \| ^0.9` |
| `^1.1` | `^12.0 \| ^13.0` | `^8.3` | `^0.6 \| ^0.7` |
| `^1.0` | `^12.0 \| ^13.0` | `^8.3` | `^0.6` |

::: tip Older SDK versions
Apps running `laravel/ai` 0.11 or lower should stay on Orbit `^1.3` — it supports the SDK down to 0.10 (and `^1.2` reaches 0.6). Composer resolves this automatically, but check the table above if you pin versions.
:::

## From 1.3.x to 2.0.0

Orbit 2.0 supports the Laravel AI SDK's stable `1.0` release and completes Orbit's move to the SDK's participant model: the deprecated `user_id` column is removed from `orbit_ai_runs`, and the message views render the SDK's new `steps`/`status` schema.

**Before you start:**

- PHP `^8.3` and Laravel `^12.0|^13.0` are required (SDK 1.0 dropped Laravel 11).
- **Resolve or abandon any conversations waiting on tool approval.** The SDK 1.0 migration removes `approval_state`, and paused turns cannot be resumed afterwards.
- **Back up your database** — both migrations below drop columns.

### Step 1 — Upgrade the SDK first (Orbit 1.3 keeps working)

```bash
composer require laravel/ai:"^1.0"
```

The SDK asks each application to hand-write its steps/status migration (its own tables, its own guide). Create a migration with the code from the [official SDK upgrade guide](https://github.com/laravel/ai/blob/1.x/UPGRADE.md) (section *"Upgrading To 1.0 From 0.11"* — *"Conversation Messages Now Store Steps"*), then:

```bash
php artisan migrate
```

Orbit 1.3.x is safe to keep running during this step — its schema guards degrade gracefully (tool-call rendering pauses until Orbit 2.0, nothing crashes).

### Step 2 — Upgrade Orbit and create its upgrade migration

```bash
composer require ashrafic/laravel-ai-orbit:"^2.0"
```

Following the SDK's own convention, Orbit 2.0 no longer ships schema-change migrations — upgrade migrations are documented here for you to create. This single guarded migration handles every upgrade origin (1.2.x, 1.3.x) — it adds participant columns if they are missing, backfills them from `user_id`, then drops `user_id`:

```bash
php artisan make:migration upgrade_orbit_ai_runs_to_orbit_2
```

```php
<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Illuminate\Database\Migrations\Migration
{
    public function up(): void
    {
        $table = 'orbit_ai_runs';

        // 1.2.x upgrades never received the participant columns.
        if (! Schema::hasColumn($table, 'participant_type')) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('participant_type')->nullable();
                $t->unsignedBigInteger('participant_id')->nullable()->index();
            });

            $guard = config('ai-orbit.auth_guard', config('auth.defaults.guard'));
            $provider = config("auth.guards.{$guard}.provider", 'users');
            $model = config("auth.providers.{$provider}.model");

            DB::table($table)->whereNotNull('user_id')->orderBy('id')->chunk(100, function ($rows) use ($table, $model) {
                foreach ($rows as $row) {
                    if (! is_numeric($row->user_id)) {
                        continue;
                    }

                    DB::table($table)->where('id', $row->id)->update([
                        'participant_type' => (new $model)->getMorphClass(),
                        'participant_id' => (int) $row->user_id,
                    ]);
                }
            });
        }

        // Everyone: remove the deprecated column.
        if (Schema::hasColumn($table, 'user_id')) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropIndex(['user_id']);
                $t->dropColumn('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('orbit_ai_runs', function (Blueprint $t) {
            $t->string('user_id')->nullable()->index();
        });
    }
};
```

```bash
php artisan migrate
```

The backfill resolves your application's user model through your auth guard configuration, mirroring how the SDK resolves participants.

### Step 3 — Republish assets

```bash
php artisan vendor:publish --tag=ai-orbit-assets --force
```

Views and config only need republishing if you customized them — there are no breaking config keys in 2.0.

### What changes in 2.0.0

- **`orbit_ai_runs.user_id` is gone** — runs identify people by `participant_type`/`participant_id` only, and the CSV export's "User" column became "Participant".
- **Steps-based message rendering** — the Message Timeline and Trace views render tool calls from the SDK's `steps` column, including each call's inline result. "Awaiting tool approval" now derives from the message's `status` (`paused`), and failed turns are shown with their error from `meta.error`.
- **Dual usage formats** — token/cost dashboards read both the SDK's legacy (`prompt_tokens`/`completion_tokens`) and current (`input_tokens`/`output_tokens`) usage keys, so historical rows keep their totals.
- **Classification observability** — the SDK's new `Classifying`/`Classified` events are captured as `classification` runs.
- **Note on cost semantics** — SDK 1.0's `inputTokens` includes cached and cache-written tokens, so flat per-token pricing rules bill cache hits at the base rate. Fine-grained cache-rate pricing is on the roadmap.

## From 1.2.x to 1.3.0

Orbit 1.3 requires `laravel/ai` `^0.10|^0.11` (the SDK's 0.10 release replaced `user_id` columns with polymorphic `participant_type`/`participant_id` columns and added an `approval_state` column to messages), and it adds the same participant columns additively to Orbit's own `orbit_ai_runs` table — the legacy `user_id` column keeps being written, so nothing breaks before you migrate.

How much you need to do depends on which SDK version your app currently runs:

### If your app already runs SDK 0.10 or 0.11

```bash
composer update ashrafic/laravel-ai-orbit
php artisan vendor:publish --tag=ai-orbit-migrations
php artisan migrate
php artisan vendor:publish --tag=ai-orbit-assets --force
```

### If your app is on SDK 0.9 or lower

**Step 1 — Update both packages together.** Composer won't resolve Orbit 1.3 beside the old SDK, so update them in one command:

```bash
composer require "laravel/ai:^0.11" "ashrafic/laravel-ai-orbit:^1.3" --with-all-dependencies
```

**Step 2 — Migrate the SDK's conversation tables (one time).** The SDK changed its own conversation schema in 0.10 and asks each application to hand-write the migration — Orbit can't do this for you, since the tables belong to the SDK and reference your app's user model. Copy the migration code from the [official SDK upgrade guide](https://github.com/laravel/ai/blob/0.x/UPGRADE.md) (section *"Upgrading To 0.10 From 0.9"*), create a migration in your app, and run it right away — after the composer update, the SDK itself expects the new schema:

```bash
php artisan migrate
```

**Step 3 — Run Orbit's shipped migration.** Orbit 1.3 adds `participant_type` and `participant_id` columns to `orbit_ai_runs`. The migration ships with the package — you only republish and run it:

```bash
php artisan vendor:publish --tag=ai-orbit-migrations
php artisan migrate
```

The migration is guarded (it skips tables that already have the new columns, so fresh installs are unaffected) and backfills the new columns from `user_id`, resolving your application's user model from your configured auth guard. The legacy `user_id` column stays in place and is deprecated — it will be removed in 2.0.

**Step 4 — Republish assets:**

```bash
php artisan vendor:publish --tag=ai-orbit-assets --force
```

::: tip Skipping Orbit's migration is safe
If you update Orbit but don't run its migration yet, nothing breaks: the recorder detects the missing columns and keeps writing the legacy `user_id` column only. Participant capture simply starts once you run the migration.
:::

### What's new in 1.3.0

- **Failure observability** — Orbit now listens to the SDK's `AgentFailed`, `StepFailed`, and `ToolFailed` events (SDK 0.11+). Failed runs are marked as `failed` instead of lingering in `running` state, step failures are appended to run traces, and tool invocations record wall time (`time_ms`).
- **Participant tracking** — Runs record who they belong to (`participant_type` + `participant_id`), following the SDK's participant model.

## From 1.2.1 to 1.2.2

1. **Laravel AI SDK compatibility** — The composer constraint now includes `laravel/ai` `^0.9`. No code changes or migrations are required, just update:

   ```bash
   composer update ashrafic/laravel-ai-orbit
   ```

## From 1.0.x to 1.1.0

1. **Republish assets** — The favicon and compiled CSS have been updated. Run:
   ```bash
   php artisan vendor:publish --tag=ai-orbit-assets --force
   ```

2. **New `ai-orbit:install` command** — A new one-command installer is available for fresh installs:
   ```bash
   php artisan ai-orbit:install
   ```
   Existing installations do not need to run this, but it is safe to do so.

3. **New features available** — After upgrading, the following new features are available:
   - **AI Run Observability** — Track one-off SDK runs in the dashboard
   - **Run Explorer** — Browse and inspect individual runs with full traces
   - **Agent Health Score UI** — Visual health indicators and export buttons
   - **Usage Dashboard merged** — The usage index and dashboard are now a single page

4. **Laravel AI SDK compatibility** — See the [version compatibility table](#version-compatibility) above for the SDK range supported by your Orbit version.

## From 0.x to 1.0

If you're upgrading from a pre-release version:

1. **Config file changes** — The config structure was reorganized. Compare your published `config/ai-orbit.php` with the latest version and merge any new keys.

2. **New migrations** — Run migrations to create new Orbit tables:
   ```bash
   php artisan migrate
   ```

3. **Livewire component tags** — If you've overridden views that reference Livewire components, note that component names are registered with the `ai-orbit.` prefix:
   ```blade
   <livewire:ai-orbit.today-stats />
   ```

## Breaking Changes Policy

- **Minor versions** (`2.0` → `2.1`) add features without breaking changes
- **Patch versions** (`2.0.0` → `2.0.1`) fix bugs without breaking changes
- **Major versions** (`2.x` → `3.0`) may include breaking changes and will be documented here

### Schema Change Policy

Like the Laravel AI SDK itself, Orbit ships create migrations only — fresh installs get the current schema in one step. Breaking schema changes are documented as hand-written upgrade migrations in this guide, never accumulated in the package.

## Staying Updated

- Watch the [GitHub repository](https://github.com/ashrafic/laravel-ai-orbit) for releases
- Check the [Changelog](/reference/changelog) for detailed release notes
- Review the [Roadmap](/reference/roadmap) for upcoming features

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
| `^1.3` | `^12.0 \| ^13.0` | `^8.3` | `^0.10 \| ^0.11` |
| `^1.2` | `^12.0 \| ^13.0` | `^8.3` | `^0.6 \| ^0.7 \| ^0.8 \| ^0.9` |
| `^1.1` | `^12.0 \| ^13.0` | `^8.3` | `^0.6 \| ^0.7` |
| `^1.0` | `^12.0 \| ^13.0` | `^8.3` | `^0.6` |

::: tip Older SDK versions
Apps running `laravel/ai` 0.9 or lower should stay on Orbit `^1.2.x` — it supports the SDK down to 0.6. Composer resolves this automatically, but check the table above if you pin versions.
:::

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

- **Minor versions** (`1.0` → `1.1`) add features without breaking changes
- **Patch versions** (`1.0.0` → `1.0.1`) fix bugs without breaking changes
- **Major versions** (`1.x` → `2.0`) may include breaking changes and will be documented here

### Planned Breaking Change

Orbit 2.0 will remove the deprecated `user_id` column from `orbit_ai_runs` (and the `AiRun::user_id` API) and align Orbit's own schema with the Laravel AI SDK's stable participant model. It is targeted for the SDK's stable 1.0 era. Until then, Orbit 1.x ships additive changes only — while the SDK is pre-1.0, its schema and API changes are absorbed internally in minor releases, and nothing in Orbit 1.x will break.

## Staying Updated

- Watch the [GitHub repository](https://github.com/ashrafic/laravel-ai-orbit) for releases
- Check the [Changelog](/reference/changelog) for detailed release notes
- Review the [Roadmap](/reference/roadmap) for upcoming features

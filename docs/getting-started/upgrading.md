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
| `^1.1` | `^12.0 \| ^13.0` | `^8.3` | `^0.6 \| ^0.7` |
| `^1.0` | `^11.0 \| ^12.0 \| ^13.0` | `^8.3` | `^0.6` |

## From 1.0.x to 1.1.0

1. **Drop Laravel 11 support** — Laravel 11 is no longer supported. Upgrade to Laravel 12 or 13 before updating Orbit.

2. **Republish assets** — The favicon and compiled CSS have been updated. Run:
   ```bash
   php artisan vendor:publish --tag=ai-orbit-assets --force
   ```

3. **New `ai-orbit:install` command** — A new one-command installer is available for fresh installs:
   ```bash
   php artisan ai-orbit:install
   ```
   Existing installations do not need to run this, but it is safe to do so.

4. **New features available** — After upgrading, the following new features are available:
   - **AI Run Observability** — Track one-off SDK runs in the dashboard
   - **Run Explorer** — Browse and inspect individual runs with full traces
   - **Agent Health Score UI** — Visual health indicators and export buttons
   - **Usage Dashboard merged** — The usage index and dashboard are now a single page

5. **Laravel AI SDK compatibility** — The package now also supports `laravel/ai` `^0.7`.

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

## Staying Updated

- Watch the [GitHub repository](https://github.com/ashrafic/laravel-ai-orbit) for releases
- Check the [Changelog](/reference/changelog) for detailed release notes
- Review the [Roadmap](/reference/roadmap) for upcoming features

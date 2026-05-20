# Upgrading

Orbit follows [Semantic Versioning](https://semver.org/). Upgrades within the same major version should be seamless.

## General Upgrade Steps

1. **Review the changelog** for breaking changes
2. **Run Composer update:**
   ```bash
   composer update ashrafic/laravel-ai-orbit
   ```
3. **Run migrations** if new tables were added:
   ```bash
   php artisan migrate
   ```
4. **Clear caches:**
   ```bash
   php artisan cache:clear
   php artisan view:clear
   ```
5. **Republish assets** if you've published views or assets:
   ```bash
   php artisan vendor:publish --tag=ai-orbit-views --force
   php artisan vendor:publish --tag=ai-orbit-assets --force
   ```

## Version Compatibility

| Orbit Version | Laravel | PHP | Laravel AI SDK |
|:---|:---|:---|:---|
| `^1.0` | `^11.0 \| ^12.0 \| ^13.0` | `^8.2` | `^0.6` |

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
   <livewire:ai-orbit.thread-explorer />
   ```

## Breaking Changes Policy

- **Minor versions** (`1.0` → `1.1`) add features without breaking changes
- **Patch versions** (`1.0.0` → `1.0.1`) fix bugs without breaking changes
- **Major versions** (`1.x` → `2.0`) may include breaking changes and will be documented here

## Staying Updated

- Watch the [GitHub repository](https://github.com/ashrafic/laravel-ai-orbit) for releases
- Check the [Changelog](/reference/changelog) for detailed release notes
- Review the [Roadmap](/reference/roadmap) for upcoming features

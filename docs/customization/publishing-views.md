# Publishing Views

Orbit's entire UI is built with Blade templates that you can override to match your needs. Publish the views and customize any aspect of the dashboard.

## Publish Views

```bash
php artisan vendor:publish --tag=ai-orbit-views
```

Published views are copied to:

```
resources/views/vendor/laravel-ai-orbit/
```

## View Structure

```
resources/views/vendor/laravel-ai-orbit/
├── components/
│   ├── layout.blade.php          # Main dashboard layout
│   ├── nav.blade.php             # Navigation sidebar
│   ├── card.blade.php            # Stat card component
│   ├── badge.blade.php           # Status badge component
│   ├── stat.blade.php            # Statistics display
│   ├── empty-state.blade.php     # Empty state placeholder
│   └── theme-toggle.blade.php    # Dark/light mode toggle
├── livewire/
│   ├── today-stats.blade.php     # Dashboard stats component
│   ├── thread-explorer.blade.php # Conversation list
│   ├── message-timeline.blade.php # Chat view
│   ├── agent-sandbox.blade.php   # Playground chat
│   ├── agent-inspector.blade.php # Agent metadata panel
│   ├── prompt-lab.blade.php      # Model comparison
│   ├── audit-dashboard.blade.php # Audit & PII scanner
│   ├── cost-dashboard.blade.php  # Analytics charts
│   ├── pricing-matrix.blade.php  # Pricing rules CRUD
│   ├── budget-alerts.blade.php   # Budget alert config
│   ├── provider-health.blade.php # Provider status
│   └── prompt-library.blade.php  # Saved prompts
├── dashboard.blade.php           # Dashboard page
├── conversations/
│   ├── index.blade.php           # Thread explorer page
│   └── show.blade.php            # Message timeline page
├── playground/
│   ├── index.blade.php           # Agent list page
│   └── show.blade.php            # Sandbox page
├── traces/
│   └── show.blade.php            # Execution trace page
├── usage/
│   ├── index.blade.php           # Today's stats page
│   ├── dashboard.blade.php       # Analytics page
│   ├── pricing.blade.php         # Pricing matrix page
│   ├── alerts.blade.php          # Budget alerts page
│   └── health.blade.php          # Provider health page
├── audit/
│   └── index.blade.php           # Audit page
└── prompts/
    └── index.blade.php           # Prompt library page
```

## Customizing the Layout

The main layout (`components/layout.blade.php`) wraps all pages. Customize it to:

- Add your company logo
- Change the navigation structure
- Add custom footer links
- Include additional CSS or JavaScript

```blade
{{-- Example: Add a custom header --}}
<div class="custom-header">
    <img src="/logo.png" alt="Company Logo">
</div>
```

## Customizing Livewire Components

Livewire components handle the interactive parts of Orbit. When you publish views, you get the Blade templates, but the PHP logic stays in the package.

To customize behavior, you can:

1. **Override the view** — Change the HTML structure
2. **Extend the component** — Create a custom Livewire component that extends Orbit's

### Example: Custom Thread Explorer

```php
// app/Http/Livewire/CustomThreadExplorer.php
namespace App\Http\Livewire;

use Ashrafic\AiOrbit\Http\Livewire\ThreadExplorer;

class CustomThreadExplorer extends ThreadExplorer
{
    public function render()
    {
        // Add custom data
        $customData = $this->getCustomData();
        
        return view('livewire.custom-thread-explorer', [
            'conversations' => $this->getConversations(),
            'customData' => $customData,
        ]);
    }
}
```

Register it in your service provider:

```php
use Livewire\Livewire;
use App\Http\Livewire\CustomThreadExplorer;

Livewire::component('ai-orbit.thread-explorer', CustomThreadExplorer::class);
```

## Reverting Changes

To revert to the package's default views, delete the published views:

```bash
rm -rf resources/views/vendor/laravel-ai-orbit
```

Or re-publish with the `--force` flag to overwrite your changes:

```bash
php artisan vendor:publish --tag=ai-orbit-views --force
```

## Best Practices

1. **Keep changes minimal** — Only override what you need to change
2. **Watch for updates** — New Orbit versions may add features to views; re-publish after major updates
3. **Use CSS for styling** — Prefer CSS overrides to view overrides when possible
4. **Test thoroughly** — Ensure Livewire components still work after view changes
5. **Version control** — Track your view overrides in git

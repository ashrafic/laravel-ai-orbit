# Publishing Assets

Orbit ships with compiled CSS assets. These are published as part of the `ai-orbit:install` command. To publish only the assets:

```bash
php artisan vendor:publish --tag=ai-orbit-assets
```

Published assets are copied to:

```
public/vendor/ai-orbit/
```

## Asset Structure

```
public/vendor/ai-orbit/
└── css/
    └── orbit.css          # Main stylesheet
```

## Customizing Styles

### Method 1: Override the CSS File

After publishing, edit `public/vendor/ai-orbit/css/orbit.css` directly.

### Method 2: Add Custom CSS

Create a custom CSS file and include it in your published layout view:

```blade
{{-- In your published layout.blade.php --}}
<link rel="stylesheet" href="/css/custom-orbit.css">
```

### Method 3: Use Tailwind Classes

Orbit uses Tailwind CSS. You can customize the appearance by modifying Tailwind classes in the published Blade views.

## Rebuilding from Source

If you want to modify the source CSS and rebuild:

1. Clone the Orbit repository
2. Install dependencies:
   ```bash
   npm install
   ```
3. Modify `resources/css/orbit.css`
4. Build:
   ```bash
   npm run build
   ```
5. Copy the built assets to your application

### Tailwind Configuration

Orbit's Tailwind config is in `tailwind.config.js`:

```js
module.exports = {
  content: [
    './resources/views/**/*.blade.php',
    './src/**/*.php',
  ],
  theme: {
    extend: {
      colors: {
        orbit: {
          indigo: '#6366f1',
          violet: '#8b5cf6',
          emerald: '#10b981',
          amber: '#f59e0b',
        },
      },
    },
  },
}
```

## Dark Mode

Orbit supports both dark and light modes. The theme is toggled via a button in the navigation bar.

### Forcing a Theme

To force dark mode always on, modify the layout view:

```blade
<html class="dark" lang="en">
```

To disable dark mode entirely:

```blade
{{-- Remove the theme toggle component --}}
{{-- <x-ai-orbit::theme-toggle /> --}}
```

### Custom Theme Colors

Override CSS custom properties in your custom CSS:

```css
:root {
  --orbit-primary: #your-color;
  --orbit-secondary: #your-color;
}
```

## Reverting Changes

To revert to the package's default assets:

```bash
php artisan vendor:publish --tag=ai-orbit-assets --force
```

## Best Practices

1. **Use a custom CSS file** — Rather than editing the published CSS directly, add a custom file that overrides specific styles
2. **Leverage Tailwind** — If you're already using Tailwind in your application, use Tailwind classes in your view overrides
3. **Minimize asset size** — Keep custom CSS minimal for fast page loads
4. **Test both themes** — Ensure your custom styles work in both dark and light modes

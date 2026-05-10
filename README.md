# Laravel AI Orbit

Observability, management, and developer playground for the [Laravel AI SDK](https://github.com/laravel/ai).

## Installation

```bash
composer require ashraful19/laravel-ai-orbit
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=ai-orbit-config
```

### Authorization

By default, the Orbit dashboard is only accessible in the `local` environment. To customize access, define a Gate in your `AppServiceProvider`:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('viewAiOrbit', function ($user) {
    return $user->isAdmin();
});
```

## Testing

Orbit subscribes to the same testing utilities provided by the Laravel AI SDK:

```php
use Laravel\AI\Facades\AI;

AI::fake([
    'agent' => 'Your chatbot response',
]);
```

## Pro

For advanced features like Arena (model comparison), full analytics, step-through debugging, and audit logging, check out [Laravel AI Orbit Pro](https://anystack.com/products/ashraful19/laravel-ai-orbit-pro).

## License

MIT License. See [LICENSE](LICENSE) for details.

# Authorization

By default, Orbit is accessible only in the `local` environment. In production, you must explicitly authorize access.

## Default Behavior

Out of the box, Orbit defines a Gate that allows access only when `APP_ENV=local`:

```php
Gate::define('viewAiOrbit', function ($user = null) {
    return app()->environment('local');
});
```

This means on production servers, visiting `/ai-orbit` will result in a `403 Forbidden` response until you configure authorization.

## Authorization Methods

### Method 1: Gate (Recommended)

Define a custom Gate in your `AuthServiceProvider` or `AppServiceProvider`:

```php
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::define('viewAiOrbit', function ($user) {
        return $user->isAdmin();
    });
}
```

The Gate receives the authenticated user (or `null` if not authenticated) and should return `true` to grant access.

**Common patterns:**

```php
// Admin-only access
Gate::define('viewAiOrbit', function ($user) {
    return $user->hasRole('admin');
});

// Multiple roles
Gate::define('viewAiOrbit', function ($user) {
    return in_array($user->role, ['admin', 'developer', 'ai-engineer']);
});

// Email domain restriction
Gate::define('viewAiOrbit', function ($user) {
    return str_ends_with($user->email, '@yourcompany.com');
});

// Environment-based (default behavior)
Gate::define('viewAiOrbit', function ($user = null) {
    return app()->environment('local', 'staging');
});
```

### Method 2: Middleware

Require authentication via the middleware stack:

```php
// config/ai-orbit.php
'middleware' => ['web', 'auth'],
```

This ensures only authenticated users can reach the Gate check. You can combine this with the Gate for role-based access:

```php
// config/ai-orbit.php
'middleware' => ['web', 'auth', 'can:viewAiOrbit'],
```

### Method 3: Custom Guard

If your application uses multiple authentication guards:

```php
// config/ai-orbit.php
'auth_guard' => 'admin',
```

Orbit will use the `admin` guard when resolving the authenticated user for the Gate check.

### Method 4: IP Whitelist (Custom Middleware)

For additional security, wrap Orbit routes with IP-based middleware:

```php
// config/ai-orbit.php
'middleware' => ['web', 'auth', 'ip.whitelist'],
```

## Authorization Middleware

Orbit ships with an `Authorize` middleware that is automatically appended to all routes. This middleware:

1. Resolves the user via the configured guard
2. Checks the `viewAiOrbit` Gate
3. Returns `403 Forbidden` if unauthorized

You don't need to register this middleware manually — it's applied automatically.

## Security Best Practices

1. **Always configure authorization before deploying to production**
2. **Use the Gate for fine-grained control** — middleware alone only checks authentication, not authorization
3. **Consider IP restrictions** for additional layers of security
4. **Review audit logs** regularly — Orbit logs all dashboard access attempts
5. **Enable HTTPS** — Orbit does not enforce HTTPS, but you should at the server or middleware level

## Troubleshooting

### "403 Forbidden" on local environment

Check that your `APP_ENV` is set to `local`:

```env
APP_ENV=local
```

Or explicitly define the Gate to allow local access:

```php
Gate::define('viewAiOrbit', function ($user = null) {
    return app()->environment('local');
});
```

### "403 Forbidden" in production

This is expected. Define a Gate that matches your authorization logic:

```php
Gate::define('viewAiOrbit', function ($user) {
    return $user->email === 'admin@example.com';
});
```

### Authentication not working

Ensure your middleware stack includes the appropriate auth middleware:

```php
// config/ai-orbit.php
'middleware' => ['web', 'auth'],
```

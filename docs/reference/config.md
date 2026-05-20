# Config Options

Complete reference for all configuration options in `config/ai-orbit.php`.

## Dashboard Path

```php
'path' => env('AI_ORBIT_PATH', 'ai-orbit'),
```

The URI prefix for the Orbit dashboard.

| Value | Example URL |
|:---|:---|
| `'ai-orbit'` | `/ai-orbit` |
| `'admin/ai'` | `/admin/ai` |
| `'debug'` | `/debug` |

## Authentication

```php
'auth_guard' => env('AI_ORBIT_GUARD', 'web'),
```

The authentication guard used for access control.

```php
'middleware' => ['web'],
```

Middleware stack applied to all Orbit routes. The `Authorize` middleware is automatically appended.

## Domain

```php
'domain' => env('AI_ORBIT_DOMAIN', null),
```

Custom subdomain for Orbit routes. `null` uses the application domain.

## Navigation

```php
'back_to_app_url' => env('AI_ORBIT_BACK_TO_APP_URL', '/'),
```

Target URL for the "Back to App" link in the dashboard header.

## Agent Discovery

```php
'agent_directories' => [
    'app/AI/Agents',
    'app/Ai/Agents',
],
```

Directories scanned for agent classes. Relative to the application base path.

```php
'registry_cache_ttl' => 3600,
```

Cache duration for discovered agents (seconds). `0` disables caching.

## Currency

```php
'currency' => env('ORBIT_CURRENCY', 'USD'),
'currency_symbol' => env('ORBIT_CURRENCY_SYMBOL', '$'),
```

Default currency for cost calculations and display.

## Budget Monitoring

```php
'budget' => [
    'enabled' => env('ORBIT_BUDGET_ENABLED', true),
    'notification_channels' => ['mail'],
],
```

| Option | Type | Default | Description |
|:---|:---|:---|:---|
| `enabled` | bool | `true` | Toggle budget alert system |
| `notification_channels` | array | `['mail']` | Channels for budget notifications |

## Prompt Lab

```php
'prompt-lab' => [
    'max_slots' => (int) env('ORBIT_PROMPT_LAB_MAX_SLOTS', 3),
    'timeout_seconds' => (int) env('ORBIT_PROMPT_LAB_TIMEOUT', 120),
],
```

| Option | Type | Default | Description |
|:---|:---|:---|:---|
| `max_slots` | int | `3` | Max provider+model combinations per comparison |
| `timeout_seconds` | int | `120` | Request timeout per slot |

## Sandbox

```php
'sandbox' => [
    'records_per_picker' => 20,
],
```

| Option | Type | Default | Description |
|:---|:---|:---|:---|
| `records_per_picker` | int | `20` | Records shown in Eloquent model pickers |

## Export

```php
'export' => [
    'pest_namespace' => env('ORBIT_PEST_NAMESPACE', 'Tests\\Feature\\AI'),
    'json_format' => env('ORBIT_JSON_FORMAT', 'openai'),
],
```

| Option | Type | Default | Description |
|:---|:---|:---|:---|
| `pest_namespace` | string | `Tests\\Feature\\AI` | Namespace for generated Pest tests |
| `json_format` | string | `openai` | Format for JSON exports |

## Audit

```php
'audit' => [
    'enabled' => env('ORBIT_AUDIT_ENABLED', true),
    'retention_days' => (int) env('ORBIT_RETENTION_DAYS', 90),
],
```

| Option | Type | Default | Description |
|:---|:---|:---|:---|
| `enabled` | bool | `true` | Toggle PII scanning and data retention |
| `retention_days` | int | `90` | Default data retention period |

## Environment Variables

| Variable | Config Key | Default | Description |
|:---|:---|:---|:---|
| `AI_ORBIT_PATH` | `path` | `ai-orbit` | Dashboard URI prefix |
| `AI_ORBIT_GUARD` | `auth_guard` | `web` | Auth guard |
| `AI_ORBIT_DOMAIN` | `domain` | `null` | Custom subdomain |
| `AI_ORBIT_BACK_TO_APP_URL` | `back_to_app_url` | `/` | Back to app link |
| `ORBIT_CURRENCY` | `currency` | `USD` | Currency code |
| `ORBIT_CURRENCY_SYMBOL` | `currency_symbol` | `$` | Currency symbol |
| `ORBIT_BUDGET_ENABLED` | `budget.enabled` | `true` | Budget alerts toggle |
| `ORBIT_PROMPT_LAB_MAX_SLOTS` | `prompt-lab.max_slots` | `3` | Max comparison slots |
| `ORBIT_PROMPT_LAB_TIMEOUT` | `prompt-lab.timeout_seconds` | `120` | Slot timeout |
| `ORBIT_PEST_NAMESPACE` | `export.pest_namespace` | `Tests\\Feature\\AI` | Pest namespace |
| `ORBIT_JSON_FORMAT` | `export.json_format` | `openai` | JSON export format |
| `ORBIT_AUDIT_ENABLED` | `audit.enabled` | `true` | Audit toggle |
| `ORBIT_RETENTION_DAYS` | `audit.retention_days` | `90` | Retention period |

## Full Config File

```php
<?php

return [
    'path' => env('AI_ORBIT_PATH', 'ai-orbit'),
    'auth_guard' => env('AI_ORBIT_GUARD', 'web'),
    'middleware' => ['web'],
    'domain' => env('AI_ORBIT_DOMAIN', null),
    'back_to_app_url' => env('AI_ORBIT_BACK_TO_APP_URL', '/'),
    
    'agent_directories' => [
        'app/AI/Agents',
        'app/Ai/Agents',
    ],
    
    'registry_cache_ttl' => 3600,
    
    'currency' => env('ORBIT_CURRENCY', 'USD'),
    'currency_symbol' => env('ORBIT_CURRENCY_SYMBOL', '$'),
    
    'budget' => [
        'enabled' => env('ORBIT_BUDGET_ENABLED', true),
        'notification_channels' => ['mail'],
    ],
    
    'prompt-lab' => [
        'max_slots' => (int) env('ORBIT_PROMPT_LAB_MAX_SLOTS', 3),
        'timeout_seconds' => (int) env('ORBIT_PROMPT_LAB_TIMEOUT', 120),
    ],
    
    'sandbox' => [
        'records_per_picker' => 20,
    ],
    
    'export' => [
        'pest_namespace' => env('ORBIT_PEST_NAMESPACE', 'Tests\\Feature\\AI'),
        'json_format' => env('ORBIT_JSON_FORMAT', 'openai'),
    ],
    
    'audit' => [
        'enabled' => env('ORBIT_AUDIT_ENABLED', true),
        'retention_days' => (int) env('ORBIT_RETENTION_DAYS', 90),
    ],
];
```

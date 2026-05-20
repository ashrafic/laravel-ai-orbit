<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Orbit Dashboard Path
    |--------------------------------------------------------------------------
    |
    | The URI path where the Orbit dashboard will be accessible. Feel free to
    | change this value to suit your application's needs.
    |
    */

    'path' => env('AI_ORBIT_PATH', 'ai-orbit'),

    /*
    |--------------------------------------------------------------------------
    | Authentication Guard
    |--------------------------------------------------------------------------
    |
    | The authentication guard to use when checking access to the Orbit
    | dashboard. Defaults to your application's default "web" guard.
    |
    */

    'auth_guard' => env('AI_ORBIT_GUARD', 'web'),

    /*
    |--------------------------------------------------------------------------
    | Route Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware applied to all Orbit dashboard routes. The "web" middleware
    | group is always applied. Add "auth" or custom auth middleware here
    | if you want to require authentication.
    |
    */

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Orbit Dashboard Domain
    |--------------------------------------------------------------------------
    |
    | Set a custom domain for the Orbit dashboard routes. Leave null to use
    | the same domain as the rest of your application.
    |
    */

    'domain' => env('AI_ORBIT_DOMAIN', null),

    /*
    |--------------------------------------------------------------------------
    | Back to Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used for the "Back to App" link in the Orbit dashboard
    | header. Set this to your application's home page or any URL where
    | users should return after using the Orbit dashboard.
    |
    */

    'back_to_app_url' => env('AI_ORBIT_BACK_TO_APP_URL', '/'),

    /*
    |--------------------------------------------------------------------------
    | Agent Discovery Directories
    |--------------------------------------------------------------------------
    |
    | Directories that Orbit will scan to discover agent classes. Paths are
    | relative to your application's base path.
    |
    */

    'agent_directories' => [
        'app/AI/Agents',
        'app/Ai/Agents',
    ],

    /*
    |--------------------------------------------------------------------------
    | Agent Registry Cache TTL
    |--------------------------------------------------------------------------
    |
    | Number of seconds to cache the discovered agent classes. Set to 0 to
    | disable caching entirely.
    |
    */

    'registry_cache_ttl' => 3600,

    /*
    |--------------------------------------------------------------------------
    | Currency Configuration
    |--------------------------------------------------------------------------
    |
    | The default currency and symbol used for cost calculations and display
    | throughout the dashboard (pricing matrix, cost dashboard, etc.).
    |
    */

    'currency' => env('ORBIT_CURRENCY', 'USD'),

    'currency_symbol' => env('ORBIT_CURRENCY_SYMBOL', '$'),

    /*
    |--------------------------------------------------------------------------
    | Budget Monitoring
    |--------------------------------------------------------------------------
    |
    | Configure budget alert functionality. Notifications are dispatched via
    | Laravel's queue system (non-blocking) so they never slow down requests.
    |
    */

    'budget' => [

        'enabled' => env('ORBIT_BUDGET_ENABLED', true),

        'notification_channels' => ['mail'],

    ],

    /*
    |--------------------------------------------------------------------------
    | Prompt Lab Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the Prompt Lab feature, which lets you configure an agent
    | and compare responses across multiple provider+model pairs.
    |
    */

    'prompt-lab' => [

        'max_slots' => (int) env('ORBIT_PROMPT_LAB_MAX_SLOTS', 3),

        'timeout_seconds' => (int) env('ORBIT_PROMPT_LAB_TIMEOUT', 120),

    ],

    /*
    |--------------------------------------------------------------------------
    | Sandbox Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the Agent Sandbox playground. Controls the number of
    | records shown in Eloquent model picker dropdowns.
    |
    */

    'sandbox' => [

        'records_per_picker' => 20,

    ],

    /*
    |--------------------------------------------------------------------------
    | Export Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for conversation export functionality — Pest test generation
    | and JSON fine-tuning exports.
    |
    */

    'export' => [

        'pest_namespace' => env('ORBIT_PEST_NAMESPACE', 'Tests\\Feature\\AI'),

        'json_format' => env('ORBIT_JSON_FORMAT', 'openai'),

    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Configuration
    |--------------------------------------------------------------------------
    |
    | Security audit and compliance settings. Includes access logging,
    | PII detection, and data retention policies.
    |
    */

    'audit' => [

        'enabled' => env('ORBIT_AUDIT_ENABLED', true),

        'retention_days' => (int) env('ORBIT_RETENTION_DAYS', 90),

    ],

];

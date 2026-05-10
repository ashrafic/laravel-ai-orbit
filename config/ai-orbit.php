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
    | Middleware applied to all Orbit dashboard routes. For most applications,
    | the defaults ("web" and "auth") are sufficient.
    |
    */

    'middleware' => ['web', 'auth'],

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

];

<?php

namespace Ashraf\Orbit\Support;

use Illuminate\Support\Facades\Config;

class OrbitConfig
{
    /**
     * Get the configured dashboard URI path.
     */
    public static function path(): string
    {
        return Config::get('ai-orbit.path') ?: 'ai-orbit';
    }

    /**
     * Get the configured authentication guard.
     */
    public static function guard(): string
    {
        return Config::get('ai-orbit.auth_guard', 'web');
    }

    /**
     * Get the configured route middleware stack.
     *
     * @return array<int, string>
     */
    public static function middleware(): array
    {
        return Config::get('ai-orbit.middleware', ['web', 'auth']);
    }

    /**
     * Get the configured agent discovery directories.
     *
     * @return array<int, string>
     */
    public static function agentDirs(): array
    {
        $dirs = Config::get('ai-orbit.agent_directories', []);

        if (function_exists('base_path')) {
            return array_map(fn (string $dir): string => base_path($dir), $dirs);
        }

        return $dirs;
    }

    /**
     * Get the configured dashboard domain.
     */
    public static function domain(): ?string
    {
        return Config::get('ai-orbit.domain');
    }

    /**
     * Get the configured agent registry cache TTL.
     */
    public static function registryCacheTtl(): int
    {
        return (int) Config::get('ai-orbit.registry_cache_ttl', 3600);
    }
}

<?php

namespace Ashraf\LaravelAiOrbit\Services;

use Ashraf\LaravelAiOrbit\Contracts\AgentRegistryContract;
use Ashraf\LaravelAiOrbit\Support\OrbitConfig;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class AgentRegistry implements AgentRegistryContract
{
    /**
     * Cache key for storing discovered agent class names.
     */
    private const CACHE_KEY_CLASSES = 'orbit:agent_registry:classes';

    /**
     * Cache key prefix for individual agent metadata.
     */
    private const CACHE_KEY_PREFIX_META = 'orbit:agent_registry:meta:';

    /**
     * Get all discovered agent class names.
     */
    public function all(): Collection
    {
        $cacheTtl = OrbitConfig::registryCacheTtl();

        if ($cacheTtl > 0) {
            $cached = Cache::get(self::CACHE_KEY_CLASSES);

            if ($cached !== null && is_array($cached)) {
                return collect($cached);
            }
        }

        $agents = $this->discoverAgents();
        $agents = array_values(array_unique($agents));

        if ($cacheTtl > 0) {
            Cache::put(self::CACHE_KEY_CLASSES, $agents, $cacheTtl);
        }

        return collect($agents);
    }

    /**
     * Get metadata for a specific agent class.
     */
    public function find(string $class): ?array
    {
        $cacheKey = self::CACHE_KEY_PREFIX_META.md5($class);

        $cacheTtl = OrbitConfig::registryCacheTtl();

        if ($cacheTtl > 0) {
            $cached = Cache::get($cacheKey);

            if ($cached !== null) {
                return $cached;
            }
        }

        if (! class_exists($class) || ! is_subclass_of($class, Agent::class)) {
            return null;
        }

        try {
            $instance = app($class);

            $instructions = (string) $instance->instructions();

            $tools = [];
            if ($instance instanceof HasTools) {
                foreach ($instance->tools() as $tool) {
                    $tools[] = get_class($tool);
                }
            }

            $hasSchema = $instance instanceof HasStructuredOutput;

            $metadata = [
                'class' => $class,
                'instructions' => $instructions,
                'tools' => $tools,
                'has_schema' => $hasSchema,
            ];

            if ($cacheTtl > 0) {
                Cache::put($cacheKey, $metadata, $cacheTtl);
            }

            return $metadata;
        } catch (\Throwable) {
            return [
                'class' => $class,
                'instructions' => '',
                'tools' => [],
                'has_schema' => false,
            ];
        }
    }

    /**
     * Clear the cache and re-scan configured directories.
     */
    public function refresh(): void
    {
        $classes = Cache::get(self::CACHE_KEY_CLASSES, []);

        Cache::forget(self::CACHE_KEY_CLASSES);

        foreach ($classes as $class) {
            Cache::forget(self::CACHE_KEY_PREFIX_META.md5($class));
        }
    }

    /**
     * Discover agent classes by scanning configured directories.
     *
     * @return array<int, string>
     */
    private function discoverAgents(): array
    {
        $agents = [];

        foreach (OrbitConfig::agentDirs() as $dir) {
            if (! is_dir($dir)) {
                continue;
            }

            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($files as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $className = $this->resolveClassFromFile($file->getPathname());

                if ($className === null) {
                    continue;
                }

                if (! class_exists($className)) {
                    continue;
                }

                if (is_subclass_of($className, Agent::class)) {
                    $agents[] = $className;
                }
            }
        }

        return $agents;
    }

    /**
     * Extract the fully qualified class name from a PHP file.
     */
    private function resolveClassFromFile(string $filePath): ?string
    {
        $content = file_get_contents($filePath);

        if ($content === false) {
            return null;
        }

        $tokens = token_get_all($content);
        $namespace = '';
        $class = '';
        $namespaceFound = false;
        $classFound = false;

        for ($i = 0, $count = count($tokens); $i < $count; $i++) {
            if (! is_array($tokens[$i])) {
                continue;
            }

            if ($tokens[$i][0] === T_NAMESPACE && ! $namespaceFound) {
                $namespaceFound = true;
                $j = $i + 1;
                while ($j < $count) {
                    if (! is_array($tokens[$j])) {
                        $j++;

                        continue;
                    }
                    if ($tokens[$j][0] === T_NAME_QUALIFIED || $tokens[$j][0] === T_STRING) {
                        $namespace = $tokens[$j][1];
                        break;
                    }
                    $j++;
                }
            }

            if ($tokens[$i][0] === T_CLASS) {
                $classFound = true;
                $j = $i + 1;
                // Skip whitespace
                while ($j < $count && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) {
                    $j++;
                }
                if ($j < $count && is_array($tokens[$j]) && $tokens[$j][0] === T_STRING) {
                    $class = $tokens[$j][1];
                }
                break;
            }
        }

        if ($class === '') {
            return null;
        }

        return $namespace !== '' ? $namespace.'\\'.$class : $class;
    }
}

<?php

namespace Ashraf\LaravelAiOrbit\Contracts;

use Illuminate\Support\Collection;

interface AgentRegistryContract
{
    /**
     * Get all discovered agent class names.
     *
     * @return Collection<int, string>
     */
    public function all(): Collection;

    /**
     * Get metadata for a specific agent class.
     *
     * @return array{
     *     class: string,
     *     instructions: string,
     *     tools: array<int, string>,
     *     has_schema: bool,
     *     provider?: string,
     *     model?: string
     * }|null
     */
    public function find(string $class): ?array;

    /**
     * Clear the cache and re-scan configured directories.
     */
    public function refresh(): void;
}

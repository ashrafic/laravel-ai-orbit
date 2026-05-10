<?php

namespace Ashraf\Orbit\Services;

use Ashraf\Orbit\Contracts\AgentRegistryContract;
use Illuminate\Support\Collection;

class AgentRegistry implements AgentRegistryContract
{
    public function all(): Collection
    {
        return collect();
    }

    public function find(string $class): ?array
    {
        return null;
    }

    public function refresh(): void
    {
        //
    }
}

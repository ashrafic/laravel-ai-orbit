<?php

use Ashrafic\AiOrbit\Contracts\AgentRegistryContract;
use Ashrafic\AiOrbit\Services\AgentRegistry;

it('registers the service provider and merges config', function () {
    expect(config('ai-orbit.path'))->toBe('ai-orbit');
    expect(config('ai-orbit.auth_guard'))->toBe('web');
});

it('binds AgentRegistryContract', function () {
    $registry = app(AgentRegistryContract::class);

    expect($registry)->toBeInstanceOf(AgentRegistry::class);
});

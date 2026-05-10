<?php

use Ashraf\Orbit\Contracts\AgentRegistryContract;
use Ashraf\Orbit\Contracts\FeatureGate;
use Ashraf\Orbit\Services\AgentRegistry;
use Ashraf\Orbit\Support\FreeFeatureGate;

it('registers the service provider and merges config', function () {
    expect(config('ai-orbit.path'))->toBe('ai-orbit');
    expect(config('ai-orbit.auth_guard'))->toBe('web');
});

it('binds FeatureGate to FreeFeatureGate by default', function () {
    $gate = app(FeatureGate::class);

    expect($gate)->toBeInstanceOf(FreeFeatureGate::class);
});

it('binds AgentRegistryContract', function () {
    $registry = app(AgentRegistryContract::class);

    expect($registry)->toBeInstanceOf(AgentRegistry::class);
});

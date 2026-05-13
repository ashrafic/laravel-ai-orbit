<?php

use Ashrafic\AiOrbit\Support\OrbitConfig;
use Illuminate\Support\Facades\Config;

it('returns configured path', function () {
    expect(OrbitConfig::path())->toBe('ai-orbit');
});

it('returns default path when not configured', function () {
    Config::offsetUnset('ai-orbit.path');

    expect(OrbitConfig::path())->toBe('ai-orbit');
});

it('returns configured auth guard', function () {
    Config::set('ai-orbit.auth_guard', 'api');

    expect(OrbitConfig::guard())->toBe('api');
});

it('returns configured middleware stack', function () {
    Config::set('ai-orbit.middleware', ['web', 'auth:api']);

    expect(OrbitConfig::middleware())->toBe(['web', 'auth:api']);
});

it('returns configured domain', function () {
    Config::set('ai-orbit.domain', 'orbit.example.com');

    expect(OrbitConfig::domain())->toBe('orbit.example.com');
});

it('returns null domain by default', function () {
    expect(OrbitConfig::domain())->toBeNull();
});

it('resolves agent directories with base_path', function () {
    Config::set('ai-orbit.agent_directories', ['app/AI/Agents']);

    $dirs = OrbitConfig::agentDirs();

    expect($dirs[0])->toContain('app/AI/Agents');
});

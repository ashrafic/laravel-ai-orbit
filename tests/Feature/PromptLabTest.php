<?php

use Ashrafic\AiOrbit\Agent\OrbitPromptLabAgent;
use Ashrafic\AiOrbit\Http\Livewire\PromptLab;
use Ashrafic\AiOrbit\Models\PromptLabSession;
use Ashrafic\AiOrbit\Services\PromptLabService;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('ai.providers', [
        'openai' => ['driver' => 'openai', 'key' => 'test-key'],
        'anthropic' => ['driver' => 'anthropic', 'key' => 'test-key'],
    ]);
    config()->set('ai.default', 'openai');
});

test('OrbitPromptLabAgent can be instantiated with all config fields', function () {
    $agent = new OrbitPromptLabAgent(
        systemPrompt: 'You are a helpful assistant.',
        model: 'gpt-5.4',
        provider: 'openai',
        labTemperature: 0.7,
        labMaxTokens: 1024,
        labContext: [['role' => 'user', 'content' => 'Hi']],
        labTopP: 0.9,
    );

    expect($agent->model())->toBe('gpt-5.4');
    expect($agent->provider())->toBe('openai');
    expect($agent->temperature())->toBe(0.7);
    expect($agent->maxTokens())->toBe(1024);
    expect($agent->topP())->toBe(0.9);
    expect($agent->instructions())->toContain('You are a helpful assistant.');
});

test('OrbitPromptLabAgent instructions includes context', function () {
    $agent = new OrbitPromptLabAgent(
        systemPrompt: 'Be concise.',
        model: 'gpt-5.4',
        provider: 'openai',
        labContext: [['role' => 'user', 'content' => 'What is PHP?']],
    );

    expect($agent->instructions())->toContain('Be concise.');
    expect($agent->instructions())->toContain('What is PHP?');
});

test('PromptLabSession model can be created', function () {
    $session = PromptLabSession::create([
        'prompt' => 'What is Laravel?',
        'system_prompt' => 'Be helpful.',
        'temperature' => 0.7,
        'max_tokens' => 500,
        'top_p' => 0.9,
        'slots' => [
            ['provider' => 'openai', 'model' => 'gpt-5.4'],
            ['provider' => 'anthropic', 'model' => 'claude-sonnet-4-6'],
        ],
        'results' => [
            ['provider' => 'openai', 'model' => 'gpt-5.4', 'content' => 'Result 1', 'latency_ms' => 100, 'tokens' => 50, 'cost' => 0.01, 'success' => true],
            ['provider' => 'anthropic', 'model' => 'claude-sonnet-4-6', 'content' => 'Result 2', 'latency_ms' => 200, 'tokens' => 40, 'cost' => 0.02, 'success' => true],
        ],
        'status' => 'completed',
    ]);

    expect($session->prompt)->toBe('What is Laravel?');
    expect($session->slots)->toBeArray();
    expect($session->slots)->toHaveCount(2);
    expect($session->results)->toBeArray();
    expect($session->status)->toBe('completed');
    expect($session->temperature)->toBe(0.7);
    expect($session->top_p)->toBe(0.9);
});

test('PromptLabService autoTags results correctly', function () {
    $service = app(PromptLabService::class);

    $results = [
        ['provider' => 'openai', 'model' => 'fast-model', 'content' => 'ok', 'latency_ms' => 10, 'tokens' => 5, 'cost' => 0.01, 'success' => true],
        ['provider' => 'anthropic', 'model' => 'cheap-model', 'content' => 'ok', 'latency_ms' => 20, 'tokens' => 10, 'cost' => 0.005, 'success' => true],
    ];

    $tags = $service->autoTagResults($results);

    expect($tags)->toBeArray();
    expect($tags['fast-model'])->toContain('Fastest');
    expect($tags['cheap-model'])->toContain('Cheapest');
});

test('PromptLabService handles failed slots gracefully', function () {
    $service = app(PromptLabService::class);

    $results = [
        ['provider' => 'openai', 'model' => 'failing-model', 'content' => '', 'latency_ms' => 50, 'tokens' => 0, 'cost' => 0, 'success' => false, 'error' => 'API error'],
        ['provider' => 'openai', 'model' => 'gpt-5.4', 'content' => 'ok', 'latency_ms' => 100, 'tokens' => 20, 'cost' => 0.01, 'success' => true],
    ];

    $tags = $service->autoTagResults($results);

    expect($tags)->toBeArray();
    expect($tags)->not->toHaveKey('failing-model');
});

test('PromptLabService returns configured providers from ai config', function () {
    $service = app(PromptLabService::class);

    $providers = $service->getConfiguredProviders();

    expect($providers)->toContain('openai');
    expect($providers)->toContain('anthropic');
});

test('PromptLab component can be instantiated', function () {
    $component = new PromptLab;

    expect($component->modelSlots)->toBeArray();
    expect($component->modelSlots)->toHaveCount(3);
    expect($component->temperature)->toBe(1.0);
    expect($component->topP)->toBe(1.0);
});

test('PromptLab component validates system prompt is required', function () {
    Livewire::test(PromptLab::class)
        ->set('prompt', 'Test prompt')
        ->set('modelSlots.0.provider', 'openai')
        ->set('modelSlots.0.model', 'gpt-5.4')
        ->call('runComparison')
        ->assertHasErrors(['systemPrompt' => 'required']);
});

test('PromptLab component validates prompt is required', function () {
    Livewire::test(PromptLab::class)
        ->set('systemPrompt', 'Test system')
        ->set('modelSlots.0.provider', 'openai')
        ->set('modelSlots.0.model', 'gpt-5.4')
        ->call('runComparison')
        ->assertHasErrors(['prompt' => 'required']);
});

test('PromptLab component validates modelSlots have provider and model', function () {
    Livewire::test(PromptLab::class)
        ->set('systemPrompt', 'Test system')
        ->set('prompt', 'Test prompt')
        ->call('runComparison')
        ->assertHasErrors(['modelSlots.0.provider', 'modelSlots.0.model']);
});

test('PromptLab component loads configured providers on mount', function () {
    Livewire::test(PromptLab::class)
        ->assertSet('configuredProviders', ['openai', 'anthropic']);
});

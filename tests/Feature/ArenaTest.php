<?php

use Ashrafic\AiOrbit\Http\Livewire\ArenaCompare;
use Ashrafic\AiOrbit\Models\ArenaSession;
use Ashrafic\AiOrbit\Services\ArenaService;
use Illuminate\Validation\ValidationException;

test('ArenaSession model can be created', function () {
    $session = ArenaSession::create([
        'prompt' => 'What is the meaning of life?',
        'models' => ['gpt-4o', 'claude-3-opus'],
        'results' => [
            'gpt-4o' => ['content' => '42', 'latency_ms' => 100, 'success' => true],
            'claude-3-opus' => ['content' => '42', 'latency_ms' => 200, 'success' => true],
        ],
        'status' => 'completed',
    ]);

    expect($session->prompt)->toBe('What is the meaning of life?');
    expect($session->models)->toBeArray();
    expect($session->status)->toBe('completed');
});

test('ArenaService handles failure gracefully', function () {
    $service = app(ArenaService::class);
    $results = $service->runComparison('test prompt', ['gpt-4o', 'failing-model']);

    expect($results)->toBeArray();
    expect($results)->toHaveCount(2);
    // At least gpt-4o should succeed
    expect(collect($results)->where('success', true))->not->toBeEmpty();
});

test('ArenaService autoTags results', function () {
    $service = app(ArenaService::class);
    $results = [
        'fast-model' => ['model' => 'fast-model', 'content' => 'ok', 'latency_ms' => 10, 'tokens' => 5, 'cost' => 0.01, 'success' => true],
        'cheap-model' => ['model' => 'cheap-model', 'content' => 'ok', 'latency_ms' => 20, 'tokens' => 10, 'cost' => 0.005, 'success' => true],
    ];

    $tags = $service->autoTagResults($results);

    expect($tags)->toBeArray();
});

test('ArenaCompare component can be instantiated', function () {
    $component = new ArenaCompare;

    expect($component->availableModels)->toBeArray();
    expect($component->selectedModels)->toBeArray();
});

test('ArenaCompare validates prompt required', function () {
    $component = new ArenaCompare;
    $component->selectedModels = ['gpt-4o'];

    try {
        $component->validate();
    } catch (ValidationException $e) {
        expect($e->errors())->toHaveKey('prompt');

        return;
    }

    $this->fail('Expected ValidationException was not thrown.');
});

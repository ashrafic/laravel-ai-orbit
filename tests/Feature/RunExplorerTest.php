<?php

use Ashrafic\AiOrbit\Http\Livewire\RunExplorer;
use Ashrafic\AiOrbit\Models\AiRun;
use Livewire\Livewire;

beforeEach(function () {
    AiRun::query()->delete();
});

it('renders with empty state when no runs exist', function () {
    Livewire::test(RunExplorer::class)
        ->assertSee('No runs found');
});

it('renders run rows when data exists', function () {
    AiRun::create([
        'operation' => 'agent_text',
        'status' => 'completed',
        'provider' => 'openai',
        'model' => 'gpt-4',
        'agent_class' => 'App\\Agents\\TestAgent',
        'input_tokens' => 100,
        'output_tokens' => 50,
        'cost' => '0.001500',
        'started_at' => now(),
    ]);

    Livewire::test(RunExplorer::class)
        ->assertSee('agent_text')
        ->assertSee('openai')
        ->assertSee('TestAgent')
        ->assertSee('150')
        ->assertDontSee('No runs found');
});

it('filters runs by operation', function () {
    AiRun::create(['operation' => 'agent_text', 'status' => 'completed', 'provider' => 'openai', 'started_at' => now()]);
    AiRun::create(['operation' => 'image', 'status' => 'completed', 'provider' => 'openai', 'started_at' => now()]);

    Livewire::test(RunExplorer::class)
        ->set('operation', 'agent_text')
        ->assertViewHas('runs', fn ($runs) => $runs->total() === 1);
});

it('filters runs by status', function () {
    AiRun::create(['operation' => 'agent_text', 'status' => 'completed', 'provider' => 'openai', 'started_at' => now()]);
    AiRun::create(['operation' => 'agent_text', 'status' => 'failed', 'provider' => 'openai', 'started_at' => now()]);

    Livewire::test(RunExplorer::class)
        ->set('status', 'failed')
        ->assertViewHas('runs', fn ($runs) => $runs->total() === 1);
});

it('filters runs by provider', function () {
    AiRun::create(['operation' => 'agent_text', 'status' => 'completed', 'provider' => 'openai', 'started_at' => now()]);
    AiRun::create(['operation' => 'agent_text', 'status' => 'completed', 'provider' => 'anthropic', 'started_at' => now()]);

    Livewire::test(RunExplorer::class)
        ->set('provider', 'anthropic')
        ->assertViewHas('runs', fn ($runs) => $runs->total() === 1);
});

it('filters runs by conversation state', function () {
    AiRun::create(['operation' => 'agent_text', 'status' => 'completed', 'conversation_id' => 'conv-abc', 'started_at' => now()]);
    AiRun::create(['operation' => 'agent_text', 'status' => 'completed', 'conversation_id' => null, 'started_at' => now()]);

    Livewire::test(RunExplorer::class)
        ->set('conversationState', 'linked')
        ->assertViewHas('runs', fn ($runs) => $runs->total() === 1);

    Livewire::test(RunExplorer::class)
        ->set('conversationState', 'unlinked')
        ->assertViewHas('runs', fn ($runs) => $runs->total() === 1);
});

it('searches runs across multiple columns', function () {
    AiRun::create(['operation' => 'agent_text', 'status' => 'completed', 'provider' => 'openai', 'invocation_id' => 'abc-123', 'started_at' => now()]);
    AiRun::create(['operation' => 'image', 'status' => 'completed', 'provider' => 'anthropic', 'started_at' => now()]);

    Livewire::test(RunExplorer::class)
        ->set('search', 'openai')
        ->assertViewHas('runs', fn ($runs) => $runs->total() === 1);

    Livewire::test(RunExplorer::class)
        ->set('search', 'abc-123')
        ->assertViewHas('runs', fn ($runs) => $runs->total() === 1);
});

it('deletes a run', function () {
    AiRun::create(['operation' => 'agent_text', 'status' => 'completed', 'provider' => 'openai', 'started_at' => now()]);
    $run = AiRun::first();

    Livewire::test(RunExplorer::class)
        ->call('deleteRun', $run->id);

    expect(AiRun::count())->toBe(0);
});

it('pre-populates operation dropdown with known operations plus DB values', function () {
    AiRun::create(['operation' => 'agent_text', 'status' => 'completed', 'provider' => 'openai', 'started_at' => now()]);
    AiRun::create(['operation' => 'custom_op', 'status' => 'completed', 'provider' => 'openai', 'started_at' => now()]);

    $operations = (new RunExplorer)->availableOperations();
    expect($operations)->toBeArray()
        ->toContain('agent_text')
        ->toContain('image')
        ->toContain('audio')
        ->toContain('embeddings')
        ->toContain('failover')
        ->toContain('custom_op');
});

it('pre-populates provider dropdown with SDK providers plus DB values', function () {
    AiRun::create(['operation' => 'agent_text', 'status' => 'completed', 'provider' => 'openai', 'started_at' => now()]);
    AiRun::create(['operation' => 'agent_text', 'status' => 'completed', 'provider' => 'custom_provider', 'started_at' => now()]);

    $providers = (new RunExplorer)->availableProviders();
    expect($providers)->toBeArray()
        ->toContain('openai')
        ->toContain('anthropic')
        ->toContain('deepseek')
        ->toContain('gemini')
        ->toContain('custom_provider');
});

it('pre-populates agent class dropdown with discovered agents plus DB values', function () {
    AiRun::create([
        'operation' => 'agent_text',
        'status' => 'completed',
        'provider' => 'openai',
        'agent_class' => 'App\\AI\\Agents\\LegacyAgent',
        'started_at' => now(),
    ]);

    $agents = (new RunExplorer)->availableAgentClasses();
    expect($agents)->toBeArray()
        ->toContain('App\\AI\\Agents\\LegacyAgent');
});

it('resets page when filters change', function () {
    AiRun::create(['operation' => 'agent_text', 'status' => 'completed', 'provider' => 'openai', 'started_at' => now()]);

    $component = Livewire::test(RunExplorer::class);

    expect($component->get('paginators')['page'] ?? 1)->toBe(1);

    $component->set('search', 'something');

    expect($component->get('paginators')['page'] ?? 1)->toBe(1);
});

it('renders show page for a single run', function () {
    $this->app['env'] = 'local';

    $run = AiRun::create([
        'invocation_id' => '018f0000-0000-7000-8000-000000000099',
        'operation' => 'agent_text',
        'status' => 'completed',
        'provider' => 'openai',
        'model' => 'gpt-4',
        'agent_class' => 'App\\Agents\\TestAgent',
        'input_tokens' => 100,
        'output_tokens' => 50,
        'cost' => '0.001500',
        'latency_ms' => 320,
        'payload' => ['prompt' => 'Hello'],
        'usage' => ['prompt_tokens' => 100],
        'events' => [],
        'started_at' => now(),
        'completed_at' => now(),
    ]);

    $response = $this->get(route('orbit.runs.show', $run));

    $response->assertStatus(200)
        ->assertSee('agent_text')
        ->assertSee('openai')
        ->assertSee('gpt-4')
        ->assertSee('150')
        ->assertSee('320ms');
});

it('returns 404 for non-existent run', function () {
    $this->app['env'] = 'local';

    $response = $this->get(route('orbit.runs.show', 99999));

    $response->assertStatus(404);
});

it('shows missing pricing warning when run is unpriced', function () {
    $this->app['env'] = 'local';

    $run = AiRun::create([
        'operation' => 'agent_text',
        'status' => 'completed',
        'provider' => 'openai',
        'model' => 'unknown-model',
        'input_tokens' => 100,
        'output_tokens' => 50,
        'cost' => '0.000000',
        'missing_pricing' => true,
        'started_at' => now(),
    ]);

    $response = $this->get(route('orbit.runs.show', $run));

    $response->assertSee('Unpriced Cost')
        ->assertSee('no matching pricing rule');
});

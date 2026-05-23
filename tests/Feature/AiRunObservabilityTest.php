<?php

use Ashrafic\AiOrbit\Models\AiRun;
use Ashrafic\AiOrbit\Models\BudgetAlert;
use Ashrafic\AiOrbit\Models\PricingRule;
use Ashrafic\AiOrbit\Notifications\BudgetExceeded;
use Ashrafic\AiOrbit\Services\TokenAggregator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Laravel\Ai\AnonymousAgent;
use Laravel\Ai\Events\AgentPrompted;
use Laravel\Ai\Events\PromptingAgent;
use Laravel\Ai\Events\ProviderFailedOver;
use Laravel\Ai\Exceptions\FailoverableException;
use Laravel\Ai\Gateway\OpenAi\OpenAiGateway;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\Providers\OpenAiProvider;
use Laravel\Ai\Responses\AgentResponse;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\Usage;

it('records one-off agent prompts when observability is enabled', function () {
    [$prompt, $response] = makeRunFixtures(invocationId: '018f0000-0000-7000-8000-000000000001');

    event(new PromptingAgent('018f0000-0000-7000-8000-000000000001', $prompt));
    event(new AgentPrompted('018f0000-0000-7000-8000-000000000001', $prompt, $response));

    $run = AiRun::query()->first();

    expect($run)->not->toBeNull()
        ->and($run->operation)->toBe('agent_text')
        ->and($run->status)->toBe('completed')
        ->and($run->provider)->toBe('openai')
        ->and($run->model)->toBe('gpt-test')
        ->and($run->conversation_id)->toBeNull()
        ->and($run->input_tokens)->toBe(12)
        ->and($run->output_tokens)->toBe(8)
        ->and($run->payload['prompt'])->toBe('Explain Orbit')
        ->and($run->payload['response'])->toBe('Orbit observes SDK calls.');
});

it('does not record runs when observability is disabled', function () {
    config()->set('ai-orbit.observability.enabled', false);

    [$prompt, $response] = makeRunFixtures(invocationId: '018f0000-0000-7000-8000-000000000002');

    event(new PromptingAgent('018f0000-0000-7000-8000-000000000002', $prompt));
    event(new AgentPrompted('018f0000-0000-7000-8000-000000000002', $prompt, $response));

    expect(AiRun::query()->count())->toBe(0);
});

it('does not store runs when run storage is disabled', function () {
    config()->set('ai-orbit.observability.store_runs', false);

    [$prompt, $response] = makeRunFixtures(invocationId: '018f0000-0000-7000-8000-000000000012');

    event(new PromptingAgent('018f0000-0000-7000-8000-000000000012', $prompt));
    event(new AgentPrompted('018f0000-0000-7000-8000-000000000012', $prompt, $response));

    expect(AiRun::query()->count())->toBe(0);
});

it('links completed conversation runs without replacing SDK conversation storage', function () {
    DB::table('agent_conversations')->insert([
        'id' => 'conversation-1',
        'user_id' => 5,
        'title' => 'Remembered thread',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    [$prompt, $response] = makeRunFixtures(invocationId: '018f0000-0000-7000-8000-000000000003');
    $response->withinConversation('conversation-1', (object) ['id' => 5]);

    event(new PromptingAgent('018f0000-0000-7000-8000-000000000003', $prompt));
    event(new AgentPrompted('018f0000-0000-7000-8000-000000000003', $prompt, $response));

    expect(DB::table('agent_conversations')->where('id', 'conversation-1')->exists())->toBeTrue()
        ->and(AiRun::query()->where('conversation_id', 'conversation-1')->exists())->toBeTrue();
});

it('respects payload capture settings and truncation', function () {
    config()->set('ai-orbit.observability.max_payload_length', 5);

    [$prompt, $response] = makeRunFixtures(
        invocationId: '018f0000-0000-7000-8000-000000000004',
        promptText: '123456789',
        responseText: 'abcdefghi',
    );

    event(new AgentPrompted('018f0000-0000-7000-8000-000000000004', $prompt, $response));

    expect(AiRun::query()->first()->payload)
        ->toMatchArray(['prompt' => '12345', 'response' => 'abcde']);

    AiRun::query()->delete();
    config()->set('ai-orbit.observability.capture_text_payloads', false);

    [$prompt, $response] = makeRunFixtures(invocationId: '018f0000-0000-7000-8000-000000000005');

    event(new AgentPrompted('018f0000-0000-7000-8000-000000000005', $prompt, $response));

    expect(AiRun::query()->first()->payload)
        ->toHaveKey('metadata_only', true)
        ->not->toHaveKey('prompt')
        ->not->toHaveKey('response');
});

it('uses run data for usage stats before falling back to conversation tables', function () {
    DB::table('agent_conversations')->insert([
        'id' => 'conversation-2',
        'user_id' => 5,
        'title' => 'Fallback thread',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('agent_conversation_messages')->insert([
        'id' => 'message-1',
        'conversation_id' => 'conversation-2',
        'user_id' => 5,
        'agent' => AnonymousAgent::class,
        'role' => 'assistant',
        'content' => 'Hello',
        'attachments' => json_encode([]),
        'tool_calls' => json_encode([]),
        'tool_results' => json_encode([]),
        'usage' => json_encode(['prompt_tokens' => 2, 'completion_tokens' => 3]),
        'meta' => json_encode(['provider' => 'openai', 'model' => 'gpt-test']),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $aggregator = app(TokenAggregator::class);

    expect($aggregator->todayStats()['input_tokens'])->toBe(2);

    AiRun::query()->create([
        'operation' => 'agent_text',
        'status' => 'completed',
        'provider' => 'openai',
        'model' => 'gpt-test',
        'agent_class' => AnonymousAgent::class,
        'input_tokens' => 10,
        'output_tokens' => 15,
        'started_at' => now(),
    ]);

    expect($aggregator->todayStats()['input_tokens'])->toBe(10)
        ->and($aggregator->agentBreakdown()->first()->total)->toBe(25);
});

it('records failover events as failed runs', function () {
    $exception = new class('Provider unavailable') extends RuntimeException implements FailoverableException {};

    event(new ProviderFailedOver(makeOpenAiProvider(), 'gpt-test', $exception));

    $run = AiRun::query()->first();

    expect($run)->not->toBeNull()
        ->and($run->operation)->toBe('failover')
        ->and($run->status)->toBe('failed')
        ->and($run->provider)->toBe('openai')
        ->and($run->model)->toBe('gpt-test')
        ->and($run->error)->toBe('Provider unavailable');
});

it('marks runs with missing pricing', function () {
    [$prompt, $response] = makeRunFixtures(invocationId: '018f0000-0000-7000-8000-000000000013');

    event(new AgentPrompted('018f0000-0000-7000-8000-000000000013', $prompt, $response));

    $run = AiRun::query()->first();

    expect($run->cost)->toBe('0.000000')
        ->and($run->priced)->toBeFalse()
        ->and($run->missing_pricing)->toBeTrue();
});

it('sends budget alerts from completed events even when run storage is disabled', function () {
    Notification::fake();
    config()->set('ai-orbit.observability.store_runs', false);

    PricingRule::create([
        'model' => 'gpt-test',
        'provider' => 'openai',
        'input_cost_per_1m' => '10000.00',
        'output_cost_per_1m' => '10000.00',
        'currency' => 'USD',
    ]);

    BudgetAlert::create([
        'threshold_amount' => '0.01',
        'period' => 'monthly',
        'channels' => ['mail'],
        'recipients' => ['ops@example.com'],
        'enabled' => true,
    ]);

    [$prompt, $response] = makeRunFixtures(invocationId: '018f0000-0000-7000-8000-000000000014');

    event(new AgentPrompted('018f0000-0000-7000-8000-000000000014', $prompt, $response));

    expect(AiRun::query()->count())->toBe(0);

    Notification::assertSentOnDemand(BudgetExceeded::class, function (BudgetExceeded $notification, array $channels, object $notifiable): bool {
        return $channels === ['mail']
            && $notifiable->routeNotificationFor('mail') === 'ops@example.com'
            && $notification->toArray($notifiable)['current_spend'] >= 0.01;
    });
});

it('does not send budget alerts below the threshold', function () {
    Notification::fake();

    PricingRule::create([
        'model' => 'gpt-test',
        'provider' => 'openai',
        'input_cost_per_1m' => '1.00',
        'output_cost_per_1m' => '1.00',
        'currency' => 'USD',
    ]);

    BudgetAlert::create([
        'threshold_amount' => '10.00',
        'period' => 'monthly',
        'channels' => ['mail'],
        'recipients' => ['ops@example.com'],
        'enabled' => true,
    ]);

    [$prompt, $response] = makeRunFixtures(invocationId: '018f0000-0000-7000-8000-000000000015');

    event(new AgentPrompted('018f0000-0000-7000-8000-000000000015', $prompt, $response));

    Notification::assertNothingSent();
});

function makeRunFixtures(
    string $invocationId,
    string $promptText = 'Explain Orbit',
    string $responseText = 'Orbit observes SDK calls.'
): array {
    $provider = makeOpenAiProvider();

    $agent = new AnonymousAgent('Be concise.', [], []);
    $prompt = new AgentPrompt($agent, $promptText, [], $provider, 'gpt-test');
    $response = new AgentResponse(
        $invocationId,
        $responseText,
        new Usage(promptTokens: 12, completionTokens: 8),
        new Meta('openai', 'gpt-test')
    );

    return [$prompt, $response];
}

function makeOpenAiProvider(): OpenAiProvider
{
    return new OpenAiProvider(
        new OpenAiGateway(app('events')),
        ['name' => 'openai', 'driver' => 'openai', 'key' => 'test'],
        app('events')
    );
}

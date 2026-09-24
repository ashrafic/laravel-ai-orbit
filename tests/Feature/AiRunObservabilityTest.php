<?php

use Ashrafic\AiOrbit\Models\AiRun;
use Ashrafic\AiOrbit\Models\BudgetAlert;
use Ashrafic\AiOrbit\Models\PricingRule;
use Ashrafic\AiOrbit\Notifications\BudgetExceeded;
use Ashrafic\AiOrbit\Services\TokenAggregator;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Laravel\Ai\AnonymousAgent;
use Laravel\Ai\Approvals\PendingApproval;
use Laravel\Ai\Contracts\Gateway\ClassificationGateway;
use Laravel\Ai\Contracts\Providers\ClassificationProvider;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Events\AgentFailed;
use Laravel\Ai\Events\AgentPrompted;
use Laravel\Ai\Events\Classified;
use Laravel\Ai\Events\Classifying;
use Laravel\Ai\Events\PromptingAgent;
use Laravel\Ai\Events\ProviderFailedOver;
use Laravel\Ai\Events\StartingStep;
use Laravel\Ai\Events\StepCompleted;
use Laravel\Ai\Events\StepFailed;
use Laravel\Ai\Events\ToolApprovalRequested;
use Laravel\Ai\Events\ToolApprovalResolved;
use Laravel\Ai\Events\ToolFailed;
use Laravel\Ai\Events\ToolInvoked;
use Laravel\Ai\Exceptions\FailoverableException;
use Laravel\Ai\Gateway\OpenAi\OpenAiGateway;
use Laravel\Ai\Gateway\StepResponse;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\Prompts\ClassificationPrompt;
use Laravel\Ai\Providers\OpenAiProvider;
use Laravel\Ai\Providers\Provider;
use Laravel\Ai\Responses\AgentResponse;
use Laravel\Ai\Responses\ClassificationResponse;
use Laravel\Ai\Responses\Data\FinishReason;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\TextUsage;
use Laravel\Ai\Tools\Request;

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
        'participant_type' => 'App\\Models\\User',
        'participant_id' => 5,
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

it('uses sdk tables for core metrics and runs for run metrics', function () {
    DB::table('agent_conversations')->insert([
        'id' => 'conversation-2',
        'participant_type' => 'App\\Models\\User',
        'participant_id' => 5,
        'title' => 'Fallback thread',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('agent_conversation_messages')->insert([
        'id' => 'message-1',
        'conversation_id' => 'conversation-2',
        'participant_type' => 'App\\Models\\User',
        'participant_id' => 5,
        'agent' => AnonymousAgent::class,
        'role' => 'assistant',
        'content' => 'Hello',
        'attachments' => json_encode([]),
        'steps' => json_encode([]),
        'usage' => json_encode(['prompt_tokens' => 2, 'completion_tokens' => 3]),
        'meta' => json_encode(['provider' => 'openai', 'model' => 'gpt-test']),
        'status' => 'completed',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $aggregator = app(TokenAggregator::class);

    expect($aggregator->periodStats()['input_tokens'])->toBe(2)
        ->and($aggregator->periodStats()['total_conversations'])->toBe(1)
        ->and($aggregator->periodStats()['total_runs'])->toBe(0);

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

    // run has no conversation_id → treated as one-off, merged with SDK stats
    expect($aggregator->periodStats()['input_tokens'])->toBe(12)
        ->and($aggregator->periodStats()['total_runs'])->toBe(1)
        ->and($aggregator->periodStats()['completed_runs'])->toBe(1)
        ->and($aggregator->agentBreakdown()->first()->total)->toBe(30);
});

it('aggregates sdk 1.0 input_tokens usage format alongside the legacy format', function () {
    DB::table('agent_conversations')->insert([
        'id' => 'conversation-3',
        'participant_type' => 'App\\Models\\User',
        'participant_id' => 5,
        'title' => 'Mixed formats',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $message = fn (string $id, array $usage) => [
        'id' => $id,
        'conversation_id' => 'conversation-3',
        'participant_type' => 'App\\Models\\User',
        'participant_id' => 5,
        'agent' => AnonymousAgent::class,
        'role' => 'assistant',
        'content' => 'Hello',
        'attachments' => json_encode([]),
        'steps' => json_encode([]),
        'usage' => json_encode($usage),
        'meta' => json_encode(['provider' => 'openai', 'model' => 'gpt-test']),
        'status' => 'completed',
        'created_at' => now(),
        'updated_at' => now(),
    ];

    DB::table('agent_conversation_messages')->insert([
        $message('message-legacy', ['prompt_tokens' => 100, 'completion_tokens' => 50]),
        $message('message-current', ['input_tokens' => 200, 'output_tokens' => 80]),
    ]);

    $aggregator = app(TokenAggregator::class);

    expect($aggregator->periodStats()['input_tokens'])->toBe(300)
        ->and($aggregator->periodStats()['output_tokens'])->toBe(130);
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

it('marks runs failed when the agent run fails', function () {
    [$prompt, $response] = makeRunFixtures(invocationId: '018f0000-0000-7000-8000-000000000101');

    event(new PromptingAgent('018f0000-0000-7000-8000-000000000101', $prompt));
    event(new AgentFailed('018f0000-0000-7000-8000-000000000101', $prompt, new RuntimeException('Provider exploded')));

    $run = AiRun::query()->first();

    expect($run->status)->toBe('failed')
        ->and($run->operation)->toBe('agent_text')
        ->and($run->error)->toBe('Provider exploded')
        ->and($run->completed_at)->not->toBeNull();
});

it('appends step failures and marks the run failed on the final step', function () {
    $invocationId = '018f0000-0000-7000-8000-000000000102';
    [$prompt, $response] = makeRunFixtures(invocationId: $invocationId);
    $provider = makeOpenAiProvider();
    $agent = new AnonymousAgent('Be concise.', [], []);

    event(new PromptingAgent($invocationId, $prompt));
    event(new StepFailed($invocationId, 1, $agent, $provider, 'gpt-test', false, new RuntimeException('Step hiccup'), 88.0));
    event(new StepFailed($invocationId, 2, $agent, $provider, 'gpt-test', true, new RuntimeException('Stream died'), 240.0));

    $run = AiRun::query()->first();

    expect($run->status)->toBe('failed')
        ->and($run->error)->toBe('Stream died')
        ->and($run->events)->toHaveCount(2)
        ->and($run->events[0])->toMatchArray(['type' => 'step_failed', 'step_number' => 1, 'is_final' => false])
        ->and($run->events[1]['step_number'])->toBe(2);
});

it('does not fail the run for recoverable step failures', function () {
    $invocationId = '018f0000-0000-7000-8000-000000000105';
    [$prompt, $response] = makeRunFixtures(invocationId: $invocationId);
    $provider = makeOpenAiProvider();
    $agent = new AnonymousAgent('Be concise.', [], []);

    event(new PromptingAgent($invocationId, $prompt));
    event(new StepFailed($invocationId, 1, $agent, $provider, 'gpt-test', false, new RuntimeException('Transient'), 12.5));
    event(new AgentPrompted($invocationId, $prompt, $response));

    $run = AiRun::query()->first();

    expect($run->status)->toBe('completed')
        ->and($run->events[0]['type'])->toBe('step_failed');
});

it('records tool failures including wall time', function () {
    $invocationId = '018f0000-0000-7000-8000-000000000103';

    event(new ToolFailed($invocationId, 'tool-invocation-1', new AnonymousAgent('Be concise.', [], []), makeTestTool(), ['query' => 'test'], new RuntimeException('Boom'), 55.5));

    $run = AiRun::query()->first();

    expect($run)->not->toBeNull()
        ->and($run->status)->toBe('running')
        ->and($run->operation)->toBe('agent_text')
        ->and($run->events[0])->toMatchArray([
            'type' => 'tool_failed',
            'tool_invocation_id' => 'tool-invocation-1',
            'time_ms' => 55.5,
        ]);
});

it('records tool wall time on successful tool invocations', function () {
    $invocationId = '018f0000-0000-7000-8000-000000000104';

    event(new ToolInvoked($invocationId, 'tool-invocation-1', new AnonymousAgent('Be concise.', [], []), makeTestTool(), ['query' => 'test'], 'result', 42.0));

    $run = AiRun::query()->first();

    expect($run->events[0])->toMatchArray([
        'type' => 'tool_invoked',
        'time_ms' => 42.0,
    ]);
});

it('does not record failures when observability is disabled', function () {
    config()->set('ai-orbit.observability.enabled', false);

    [$prompt, $response] = makeRunFixtures(invocationId: '018f0000-0000-7000-8000-000000000106');

    event(new AgentFailed('018f0000-0000-7000-8000-000000000106', $prompt, new RuntimeException('Silent')));

    expect(AiRun::query()->count())->toBe(0);
});

it('records the participant type and id on runs', function () {
    $invocationId = '018f0000-0000-7000-8000-000000000107';
    [$prompt, $response] = makeRunFixtures(invocationId: $invocationId);

    $participant = new class extends Model {};
    $participant->id = 7;

    $response->withinConversation('conversation-1', $participant);

    event(new PromptingAgent($invocationId, $prompt));
    event(new AgentPrompted($invocationId, $prompt, $response));

    $run = AiRun::query()->first();

    expect($run->participant_id)->toBe(7)
        ->and($run->participant_type)->toBe($participant::class);
});

it('records step starts and completions in the run trace', function () {
    $invocationId = '018f0000-0000-7000-8000-000000000109';
    [$prompt, $response] = makeRunFixtures(invocationId: $invocationId);
    $provider = makeOpenAiProvider();
    $agent = new AnonymousAgent('Be concise.', [], []);

    event(new PromptingAgent($invocationId, $prompt));
    event(new StartingStep($invocationId, 1, $agent, $provider, 'gpt-test', false, [], null));
    event(new StepCompleted($invocationId, 1, $agent, $provider, 'gpt-test', false, makeStepResponse(), 340.5));

    $run = AiRun::query()->first();

    expect($run->events)->toHaveCount(2)
        ->and($run->events[0])->toMatchArray(['type' => 'step_started', 'step_number' => 1])
        ->and($run->events[1])->toMatchArray(['type' => 'step_completed', 'time_ms' => 340.5]);
});

it('marks runs pending approval and resumes them when approval resolves', function () {
    $invocationId = '018f0000-0000-7000-8000-000000000109';
    [$prompt, $response] = makeRunFixtures(invocationId: $invocationId);

    event(new PromptingAgent($invocationId, $prompt));
    event(new ToolApprovalRequested($invocationId, new AnonymousAgent('Be concise.', [], []), collect([
        new PendingApproval(id: 'call_1', tool: 'search', arguments: ['q' => 'x']),
    ])));

    $run = AiRun::query()->first();

    expect($run->status)->toBe('pending_approval')
        ->and($run->events[0]['type'])->toBe('approval_requested')
        ->and($run->events[0]['approvals'][0]['tool'])->toBe('search');

    event(new ToolApprovalResolved($invocationId, new AnonymousAgent('Be concise.', [], []), collect()));

    $run->refresh();

    expect($run->status)->toBe('running')
        ->and($run->events[1]['type'])->toBe('approval_resolved');
});

it('creates a pending-approval run when no run exists yet', function () {
    $invocationId = '018f0000-0000-7000-8000-000000000110';

    event(new ToolApprovalRequested($invocationId, new AnonymousAgent('Be concise.', [], []), collect(), 'conversation-9', (object) ['id' => 3]));

    $run = AiRun::query()->first();

    expect($run->status)->toBe('pending_approval')
        ->and($run->conversation_id)->toBe('conversation-9')
        ->and($run->participant_id)->toBe(3);
});

it('upgrades the pre-1.3 table shape via the documented 2.0 migration and records participants', function () {
    Schema::drop('orbit_ai_runs');
    Schema::create('orbit_ai_runs', function (Blueprint $table) {
        $table->id();
        $table->uuid('invocation_id')->nullable();
        $table->string('operation')->index();
        $table->string('status')->default('running')->index();
        $table->string('provider')->nullable()->index();
        $table->string('model')->nullable()->index();
        $table->string('agent_class')->nullable()->index();
        $table->string('user_id')->nullable()->index();
        $table->string('conversation_id')->nullable()->index();
        $table->unsignedInteger('input_tokens')->default(0);
        $table->unsignedInteger('output_tokens')->default(0);
        $table->decimal('cost', 12, 6)->default(0);
        $table->boolean('priced')->default(false);
        $table->boolean('missing_pricing')->default(false);
        $table->unsignedInteger('latency_ms')->nullable();
        $table->json('payload')->nullable();
        $table->json('usage')->nullable();
        $table->json('events')->nullable();
        $table->text('error')->nullable();
        $table->timestamp('started_at')->nullable()->index();
        $table->timestamp('completed_at')->nullable()->index();
        $table->timestamps();
    });

    DB::table('orbit_ai_runs')->insert([
        ['operation' => 'agent_text', 'user_id' => '5', 'created_at' => now(), 'updated_at' => now()],
        ['operation' => 'image', 'user_id' => null, 'created_at' => now(), 'updated_at' => now()],
    ]);

    config()->set('auth.providers.users.model', AiRun::class);

    // The upgrade migration documented in docs/getting-started/upgrading.md ("Upgrading To 2.0"):
    $table = 'orbit_ai_runs';

    if (! Schema::hasColumn($table, 'participant_type')) {
        Schema::table($table, function (Blueprint $t) {
            $t->string('participant_type')->nullable();
            $t->unsignedBigInteger('participant_id')->nullable()->index();
        });

        $guard = config('ai-orbit.auth_guard', config('auth.defaults.guard'));
        $provider = config("auth.guards.{$guard}.provider", 'users');
        $model = config("auth.providers.{$provider}.model");

        DB::table($table)->whereNotNull('user_id')->orderBy('id')->chunk(100, function ($rows) use ($table, $model) {
            foreach ($rows as $row) {
                if (! is_numeric($row->user_id)) {
                    continue;
                }

                DB::table($table)->where('id', $row->id)->update([
                    'participant_type' => (new $model)->getMorphClass(),
                    'participant_id' => (int) $row->user_id,
                ]);
            }
        });
    }

    if (Schema::hasColumn($table, 'user_id')) {
        Schema::table($table, function (Blueprint $t) {
            $t->dropIndex(['user_id']);
            $t->dropColumn('user_id');
        });
    }

    $rows = DB::table('orbit_ai_runs')->orderBy('id')->get();

    expect($rows[0]->participant_id)->toBe(5)
        ->and($rows[0]->participant_type)->toBe(AiRun::class)
        ->and($rows[1]->participant_id)->toBeNull()
        ->and($rows[1]->participant_type)->toBeNull()
        ->and(Schema::hasColumn('orbit_ai_runs', 'user_id'))->toBeFalse();

    // The recorder writes participant runs against the upgraded shape.
    [$prompt, $response] = makeRunFixtures(invocationId: '018f0000-0000-7000-8000-000000000108');

    $participant = new class extends Model {};
    $participant->id = 9;
    $response->withinConversation('conversation-up', $participant);

    event(new PromptingAgent('018f0000-0000-7000-8000-000000000108', $prompt));
    event(new AgentPrompted('018f0000-0000-7000-8000-000000000108', $prompt, $response));

    $run = AiRun::query()->where('invocation_id', '018f0000-0000-7000-8000-000000000108')->first();

    expect($run)->not->toBeNull()
        ->and($run->status)->toBe('completed')
        ->and($run->participant_id)->toBe(9)
        ->and($run->participant_type)->toBe($participant::class);
});

it('records classification runs from the Classifying and Classified events', function () {
    $invocationId = '018f0000-0000-7000-8000-000000000112';

    $classificationProvider = new class(new OpenAiGateway(app('events')), ['name' => 'test', 'driver' => 'openai', 'key' => 'test'], app('events')) extends Provider implements ClassificationProvider
    {
        public function classify(string|array $state, array $questions, ?string $model = null, int $timeout = 30, array $providerOptions = []): ClassificationResponse
        {
            return new ClassificationResponse([], new TextUsage, new Meta('test', 'gpt-test'));
        }

        public function classificationGateway(): ClassificationGateway
        {
            throw new RuntimeException('not needed');
        }

        public function useClassificationGateway(ClassificationGateway $gateway): static
        {
            return $this;
        }

        public function defaultClassificationModel(): string
        {
            return 'gpt-test';
        }
    };

    $prompt = new ClassificationPrompt(
        'Is this urgent?',
        [],
        $classificationProvider,
        'gpt-test',
    );

    event(new Classifying($invocationId, $classificationProvider, 'gpt-test', $prompt));
    event(new Classified(
        $invocationId,
        $classificationProvider,
        'gpt-test',
        $prompt,
        new ClassificationResponse(
            [],
            new TextUsage(inputTokens: 4, outputTokens: 2),
            new Meta('test', 'gpt-test'),
        ),
    ));

    $run = AiRun::query()->where('invocation_id', $invocationId)->first();

    expect($run)->not->toBeNull()
        ->and($run->operation)->toBe('classification')
        ->and($run->status)->toBe('completed')
        ->and($run->input_tokens)->toBe(4)
        ->and($run->output_tokens)->toBe(2);
});

function makeTestTool(): Tool
{
    return new class implements Tool
    {
        public function description(): Stringable|string
        {
            return 'Test tool';
        }

        public function handle(Request $request): Stringable|string
        {
            return 'ok';
        }

        public function schema(JsonSchema $schema): array
        {
            return [];
        }
    };
}

function makeStepResponse(): StepResponse
{
    return new StepResponse(
        'step text',
        [],
        FinishReason::Stop,
        new TextUsage(inputTokens: 3, outputTokens: 2),
        new Meta('openai', 'gpt-test'),
    );
}

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
        new TextUsage(inputTokens: 12, outputTokens: 8),
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

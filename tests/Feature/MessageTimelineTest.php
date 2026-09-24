<?php

use Ashrafic\AiOrbit\Http\Livewire\MessageTimeline;
use Ashrafic\AiOrbit\Services\ConversationRepository;
use Ashrafic\AiOrbit\Support\StepParser;
use Livewire\Livewire;

beforeEach(function () {
    $this->mockConversation = (object) [
        'id' => 'conv-123',
        'title' => 'Test Conversation',
        'created_at' => now(),
        'agent_class' => 'App\\Agents\\TestAgent',
        'messages' => collect([
            (object) [
                'role' => 'user',
                'content' => 'Hello world',
                'created_at' => now(),
                'steps' => '[]',
                'status' => 'completed',
            ],
            (object) [
                'role' => 'assistant',
                'content' => 'Hi there!',
                'created_at' => now()->addSecond(),
                'steps' => '[]',
                'status' => 'completed',
            ],
        ]),
    ];
});

test('message timeline renders with styled messages', function () {
    $repository = Mockery::mock(ConversationRepository::class);
    $repository->shouldReceive('find')->with('conv-123')->andReturn($this->mockConversation);
    app()->instance(ConversationRepository::class, $repository);

    Livewire::test(MessageTimeline::class, ['conversationId' => 'conv-123'])
        ->assertSee('Test Conversation')
        ->assertSee('User')
        ->assertSee('Assistant')
        ->assertSee('Hello world')
        ->assertSee('Hi there!');
});

test('message timeline renders failed and paused statuses with step tool calls', function () {
    $steps = json_encode([
        ['content' => '', 'tool_calls' => [
            ['id' => 'call_1', 'name' => 'read_file', 'arguments' => ['path' => 'a.txt'], 'result' => 'contents'],
            ['id' => 'call_2', 'name' => 'delete_file', 'arguments' => ['path' => 'b.txt'], 'approval_reason' => 'Destructive.'],
        ]],
    ]);

    $conversation = (object) [
        'id' => 'conv-456',
        'title' => 'Approval flow',
        'created_at' => now(),
        'agent_class' => 'App\\Agents\\TestAgent',
        'messages' => collect([
            (object) [
                'role' => 'assistant',
                'content' => 'Working on it',
                'created_at' => now(),
                'steps' => $steps,
                'status' => 'paused',
            ],
            (object) [
                'role' => 'assistant',
                'content' => 'Broke',
                'created_at' => now()->addMinute(),
                'steps' => '[]',
                'status' => 'failed',
                'meta' => json_encode(['error' => 'Provider exploded']),
            ],
        ]),
    ];

    $repository = Mockery::mock(ConversationRepository::class);
    $repository->shouldReceive('find')->with('conv-456')->andReturn($conversation);
    app()->instance(ConversationRepository::class, $repository);

    Livewire::test(MessageTimeline::class, ['conversationId' => 'conv-456'])
        ->assertSee('Awaiting tool approval')
        ->assertSee('Failed turn')
        ->assertSee('Provider exploded')
        ->assertSee('read_file')
        ->assertSee('delete_file')
        ->assertSee('Destructive.');
});

test('step parser flattens calls with state', function () {
    $steps = json_encode([
        ['content' => '', 'tool_calls' => [
            ['id' => 'c1', 'name' => 'a', 'arguments' => [], 'result' => 'done'],
            ['id' => 'c2', 'name' => 'b', 'arguments' => ['x' => 1], 'result' => null, 'denied' => true],
            ['id' => 'c3', 'name' => 'c', 'arguments' => [], 'approval_reason' => 'Needs a human'],
        ]],
        ['content' => 'final', 'tool_calls' => []],
    ]);

    $calls = StepParser::toolCalls($steps);

    expect($calls)->toHaveCount(3)
        ->and($calls[0])->toMatchArray(['name' => 'a', 'pending' => false, 'denied' => false, 'failed' => false, 'step' => 1])
        ->and($calls[1])->toMatchArray(['name' => 'b', 'denied' => true])
        ->and($calls[2])->toMatchArray(['name' => 'c', 'pending' => true, 'approval_reason' => 'Needs a human'])
        ->and(StepParser::toolCalls(null))->toBe([])
        ->and(StepParser::toolCalls('not-json'))->toBe([]);
});

test('highlightJson returns highlighted HTML for JSON string', function () {
    $component = new MessageTimeline;
    $component->conversationId = 'test';

    $result = $component->highlightJson(['name' => 'test', 'count' => 42, 'active' => true]);

    expect($result)->toContain('text-purple-400')
        ->and($result)->toContain('text-green-400')
        ->and($result)->toContain('text-amber-400')
        ->and($result)->toContain('text-blue-400');
});

test('highlightJson handles nested objects', function () {
    $component = new MessageTimeline;
    $component->conversationId = 'test';

    $data = ['user' => ['name' => 'test', 'roles' => ['admin', 'user']]];
    $result = $component->highlightJson($data);

    expect($result)->toContain('"name"')
        ->and($result)->toContain('"test"')
        ->and($result)->toContain('"admin"');
});

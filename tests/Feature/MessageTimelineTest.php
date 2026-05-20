<?php

use Ashrafic\AiOrbit\Http\Livewire\MessageTimeline;
use Ashrafic\AiOrbit\Services\ConversationRepository;
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
                'tool_calls' => null,
            ],
            (object) [
                'role' => 'assistant',
                'content' => 'Hi there!',
                'created_at' => now()->addSecond(),
                'tool_calls' => null,
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

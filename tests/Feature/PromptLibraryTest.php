<?php

use Ashrafic\AiOrbit\Http\Livewire\PromptLibrary;
use Ashrafic\AiOrbit\Models\SavedPrompt;
use Illuminate\Validation\ValidationException;

test('SavedPrompt model can be created', function () {
    $prompt = SavedPrompt::create([
        'name' => 'Code Review Prompt',
        'content' => 'Review this code for security issues.',
        'agent_class' => 'App\\Ai\\Agents\\CodeReviewer',
        'tags' => ['review', 'security'],
    ]);

    expect($prompt->name)->toBe('Code Review Prompt');
    expect($prompt->tags)->toBe(['review', 'security']);
});

test('PromptLibrary component can be instantiated', function () {
    $component = new PromptLibrary;

    expect($component)->toBeInstanceOf(PromptLibrary::class);
});

test('PromptLibrary can create prompt via model', function () {
    SavedPrompt::create([
        'name' => 'Test Prompt',
        'content' => 'Test content',
    ]);

    expect(SavedPrompt::where('name', 'Test Prompt')->exists())->toBeTrue();
});

test('PromptLibrary validates required fields', function () {
    $component = new PromptLibrary;
    $component->name = '';
    $component->content = '';

    try {
        $component->validate();
    } catch (ValidationException $e) {
        expect($e->errors())->toHaveKey('name');
        expect($e->errors())->toHaveKey('content');

        return;
    }

    $this->fail('Expected ValidationException was not thrown.');
});

test('PromptLibrary can delete prompt via model', function () {
    $prompt = SavedPrompt::create([
        'name' => 'To Delete',
        'content' => 'Content',
    ]);

    $prompt->delete();

    expect(SavedPrompt::where('name', 'To Delete')->exists())->toBeFalse();
});

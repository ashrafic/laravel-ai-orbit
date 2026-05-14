<?php

namespace Ashrafic\AiOrbit\Http\Livewire;

use Ashrafic\AiOrbit\Services\PromptLabService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PromptLab extends Component
{
    public string $systemPrompt = '';

    public string $prompt = '';

    public float $temperature = 1.0;

    public ?int $maxTokens = null;

    public float $topP = 1.0;

    public string $context = '';

    public array $slots = [
        ['provider' => '', 'model' => ''],
        ['provider' => '', 'model' => ''],
        ['provider' => '', 'model' => ''],
    ];

    public ?array $results = null;

    public ?array $autoTags = null;

    public bool $running = false;

    public array $configuredProviders = [];

    public array $modelsForProvider = [];

    protected $rules = [
        'systemPrompt' => 'required|string',
        'prompt' => 'required|string|min:1',
        'temperature' => 'numeric|min:0|max:2',
        'topP' => 'numeric|min:0|max:1',
        'slots' => 'required|array|min:1',
        'slots.*.provider' => 'required|string',
        'slots.*.model' => 'required|string',
    ];

    protected $messages = [
        'systemPrompt.required' => 'System prompt is required.',
        'prompt.required' => 'Instruction is required.',
        'slots.*.provider.required' => 'Provider is required for each slot.',
        'slots.*.model.required' => 'Model is required for each slot.',
    ];

    public function mount(PromptLabService $service): void
    {
        $this->configuredProviders = $service->getConfiguredProviders();
    }

    public function updatedSlots(int $index, string $key): void
    {
        if ($key === 'provider' && ! empty($this->slots[$index]['provider'])) {
            $service = app(PromptLabService::class);
            $this->modelsForProvider[$index] = $service->getModelsForProvider(
                $this->slots[$index]['provider']
            );
        }
    }

    public function runComparison(): void
    {
        $this->validate();

        $this->running = true;
        $this->results = null;
        $this->autoTags = null;

        $context = null;
        if (! empty($this->context)) {
            $decoded = json_decode($this->context, true);
            $context = is_array($decoded) ? $decoded : null;
        }

        $service = app(PromptLabService::class);
        $results = $service->runComparison(
            prompt: $this->prompt,
            slots: $this->slots,
            systemPrompt: $this->systemPrompt,
            temperature: $this->temperature,
            maxTokens: $this->maxTokens,
            context: $context,
            topP: $this->topP,
        );

        $this->results = $results;
        $this->autoTags = $service->autoTagResults($results);

        $service->saveSession(
            prompt: $this->prompt,
            slots: $this->slots,
            results: $results,
            systemPrompt: $this->systemPrompt,
            temperature: $this->temperature,
            maxTokens: $this->maxTokens,
            context: $context,
            topP: $this->topP,
        );

        $this->running = false;
    }

    public function render(): View
    {
        return view('ai-orbit::prompt-lab.compare');
    }
}

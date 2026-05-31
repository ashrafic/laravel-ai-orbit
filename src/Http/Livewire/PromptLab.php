<?php

namespace Ashrafic\AiOrbit\Http\Livewire;

use Ashrafic\AiOrbit\Models\SavedPrompt;
use Ashrafic\AiOrbit\Services\PromptLabService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class PromptLab extends Component
{
    public string $systemPrompt = '';

    public string $prompt = '';

    public float $temperature = 1.0;

    public ?int $maxTokens = null;

    public float $topP = 1.0;

    public string $context = '';

    public string $saveName = '';

    public bool $showSaveForm = false;

    /** @var Collection<int, SavedPrompt> */
    public Collection $recentPrompts;

    public array $modelSlots = [
        ['provider' => '', 'model' => ''],
        ['provider' => '', 'model' => ''],
        ['provider' => '', 'model' => ''],
    ];

    public ?array $results = null;

    public ?array $autoTags = null;

    public bool $running = false;

    public array $configuredProviders = [];

    protected $rules = [
        'systemPrompt' => 'required|string',
        'prompt' => 'required|string|min:1',
        'temperature' => 'numeric|min:0|max:2',
        'topP' => 'numeric|min:0|max:1',
        'modelSlots' => 'required|array|min:1',
        'modelSlots.*.provider' => 'nullable|string',
        'modelSlots.*.model' => 'nullable|string',
    ];

    protected $messages = [
        'systemPrompt.required' => 'System prompt is required.',
        'prompt.required' => 'Instruction is required.',
        'modelSlots.*.provider.required' => 'Provider is required for each slot.',
        'modelSlots.*.model.required' => 'Model is required for each slot.',
    ];

    public function mount(PromptLabService $service): void
    {
        $this->configuredProviders = $service->getConfiguredProviders();
        $this->recentPrompts = SavedPrompt::orderBy('updated_at', 'desc')->limit(6)->get();
    }

    #[On('prompt-loaded')]
    public function loadPrompt(array $prompt): void
    {
        $this->systemPrompt = $prompt['content'] ?? '';

        if (! empty($prompt['instruction'])) {
            $this->prompt = $prompt['instruction'];
        }
    }

    public function loadFromLibrary(int $id): void
    {
        $saved = SavedPrompt::find($id);

        if (! $saved) {
            return;
        }

        $this->systemPrompt = $saved->content;

        if (! empty($saved->instruction)) {
            $this->prompt = $saved->instruction;
        }

        if (! empty($saved->meta)) {
            $this->temperature = (float) ($saved->meta['temperature'] ?? $this->temperature);
            $this->maxTokens = $saved->meta['max_tokens'] ?? $this->maxTokens;
            $this->topP = (float) ($saved->meta['top_p'] ?? $this->topP);
            $this->context = $saved->meta['context'] ?? $this->context;
        }
    }

    public function startSave(): void
    {
        $this->saveName = '';
        $this->showSaveForm = true;
    }

    public function cancelSave(): void
    {
        $this->showSaveForm = false;
        $this->saveName = '';
    }

    public function saveToLibrary(): void
    {
        $this->validate(['saveName' => 'required|string|max:255']);

        SavedPrompt::create([
            'name' => $this->saveName,
            'content' => $this->systemPrompt,
            'instruction' => $this->prompt,
            'meta' => [
                'temperature' => $this->temperature,
                'max_tokens' => $this->maxTokens,
                'top_p' => $this->topP,
                'context' => $this->context,
            ],
            'tags' => ['prompt-lab'],
        ]);

        $this->recentPrompts = SavedPrompt::orderBy('updated_at', 'desc')->limit(6)->get();
        $this->showSaveForm = false;
        $this->saveName = '';
    }

    public function deleteSaved(int $id): void
    {
        SavedPrompt::findOrFail($id)->delete();
        $this->recentPrompts = SavedPrompt::orderBy('updated_at', 'desc')->limit(6)->get();
    }

    public function runComparison(): void
    {
        $this->validate();

        $filledSlots = array_values(array_filter($this->modelSlots, fn ($s) => ! empty($s['provider']) && ! empty($s['model'])));

        if (empty($filledSlots)) {
            $this->addError('modelSlots', 'Please fill at least one provider and model pair.');

            return;
        }

        foreach ($this->modelSlots as $slot) {
            $hasProvider = ! empty($slot['provider']);
            $hasModel = ! empty($slot['model']);
            if ($hasProvider !== $hasModel) {
                $this->addError('modelSlots', 'Both provider and model are required for each filled slot.');

                return;
            }
        }

        $this->running = true;
        $this->results = null;
        $this->autoTags = null;

        $context = null;
        if (! empty($this->context)) {
            $decoded = json_decode($this->context, true);
            $context = is_array($decoded) ? $decoded : ['context' => $this->context];
        }

        $service = app(PromptLabService::class);
        $results = $service->runComparison(
            prompt: $this->prompt,
            slots: $filledSlots,
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
            slots: $filledSlots,
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

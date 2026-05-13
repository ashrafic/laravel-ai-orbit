<?php

namespace Ashrafic\AiOrbit\Http\Livewire;

use Ashrafic\AiOrbit\Services\ArenaService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ArenaCompare extends Component
{
    public string $prompt = '';

    public array $selectedModels = [];

    public ?array $results = null;

    public ?array $autoTags = null;

    public bool $running = false;

    public array $availableModels = [
        'gpt-4o' => 'GPT-4o',
        'gpt-4-turbo' => 'GPT-4 Turbo',
        'claude-3-opus' => 'Claude 3 Opus',
        'claude-3-sonnet' => 'Claude 3 Sonnet',
        'gemini-1.5-pro' => 'Gemini 1.5 Pro',
        'deepseek-v4' => 'DeepSeek V4',
    ];

    protected $rules = [
        'prompt' => 'required|string|min:1',
        'selectedModels' => 'required|array|min:1|max:3',
    ];

    public function toggleModel(string $model): void
    {
        if (in_array($model, $this->selectedModels, true)) {
            $this->selectedModels = array_values(array_filter(
                $this->selectedModels, fn ($m) => $m !== $model
            ));
        } else {
            if (count($this->selectedModels) >= 3) {
                return;
            }

            $this->selectedModels[] = $model;
        }
    }

    public function runComparison(): void
    {
        $this->validate();

        $this->running = true;
        $this->results = null;
        $this->autoTags = null;

        $service = app(ArenaService::class);
        $results = $service->runComparison($this->prompt, $this->selectedModels);

        $this->results = $results;
        $this->autoTags = $service->autoTagResults($results);

        $service->saveSession($this->prompt, $this->selectedModels, $results);

        $this->running = false;
    }

    public function render(): View
    {
        return view('ai-orbit::arena.compare', [
            'availableModels' => $this->availableModels,
        ]);
    }
}

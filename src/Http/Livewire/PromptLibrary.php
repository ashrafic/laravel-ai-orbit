<?php

namespace Ashrafic\AiOrbit\Http\Livewire;

use Ashrafic\AiOrbit\Models\SavedPrompt;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class PromptLibrary extends Component
{
    use WithPagination;

    public string $search = '';

    public string $name = '';

    public string $content = '';

    public string $instruction = '';

    public float $temperature = 1.0;

    public ?int $maxTokens = null;

    public float $topP = 1.0;

    public string $context = '';

    public array $tags = [];

    public string $tagInput = '';

    public ?int $editingId = null;

    public bool $showForm = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'content' => 'required|string',
        'instruction' => 'nullable|string',
        'temperature' => 'numeric|min:0|max:2',
        'topP' => 'numeric|min:0|max:1',
        'tags' => 'array',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function addTag(): void
    {
        $trimmed = trim($this->tagInput);

        if ($trimmed !== '' && ! in_array($trimmed, $this->tags, true)) {
            $this->tags[] = $trimmed;
        }

        $this->tagInput = '';
    }

    public function removeTag(string $tag): void
    {
        $this->tags = array_values(array_filter($this->tags, fn ($t) => $t !== $tag));
    }

    public function loadPrompt(int $id): void
    {
        $prompt = SavedPrompt::find($id);

        if ($prompt) {
            $this->dispatch('prompt-loaded', prompt: $prompt->toArray());
        }
    }

    public function edit(int $id): void
    {
        $prompt = SavedPrompt::findOrFail($id);
        $this->editingId = $id;
        $this->showForm = true;
        $this->name = $prompt->name;
        $this->content = $prompt->content;
        $this->instruction = $prompt->instruction ?? '';
        $this->temperature = (float) ($prompt->meta['temperature'] ?? 1.0);
        $this->maxTokens = $prompt->meta['max_tokens'] ?? null;
        $this->topP = (float) ($prompt->meta['top_p'] ?? 1.0);
        $this->context = $prompt->meta['context'] ?? '';
        $this->tags = $prompt->tags ?? [];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'content' => $this->content,
            'instruction' => $this->instruction,
            'meta' => [
                'temperature' => $this->temperature,
                'max_tokens' => $this->maxTokens,
                'top_p' => $this->topP,
                'context' => $this->context,
            ],
            'tags' => $this->tags,
        ];

        if ($this->editingId) {
            SavedPrompt::findOrFail($this->editingId)->update($data);
        } else {
            SavedPrompt::create($data);
        }

        $this->reset(['showForm', 'name', 'content', 'instruction', 'temperature', 'maxTokens', 'topP', 'context', 'tags', 'editingId']);
        $this->temperature = 1.0;
        $this->topP = 1.0;
    }

    public function delete(int $id): void
    {
        SavedPrompt::findOrFail($id)->delete();
    }

    public function cancelEdit(): void
    {
        $this->reset(['showForm', 'name', 'content', 'instruction', 'temperature', 'maxTokens', 'topP', 'context', 'tags', 'editingId']);
        $this->temperature = 1.0;
        $this->topP = 1.0;
    }

    public function render(): View
    {
        $query = SavedPrompt::query();

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('content', 'like', '%'.$this->search.'%');
            });
        }

        $prompts = $query->orderBy('updated_at', 'desc')->paginate(12);

        return view('ai-orbit::livewire.prompt-library', [
            'prompts' => $prompts,
        ]);
    }
}

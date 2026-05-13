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

    public ?string $agentClass = null;

    public array $tags = [];

    public string $tagInput = '';

    public ?int $editingId = null;

    protected $rules = [
        'name' => 'required|string|max:255',
        'content' => 'required|string',
        'agentClass' => 'nullable|string',
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
        $this->name = $prompt->name;
        $this->content = $prompt->content;
        $this->agentClass = $prompt->agent_class;
        $this->tags = $prompt->tags ?? [];
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            SavedPrompt::findOrFail($this->editingId)->update([
                'name' => $this->name,
                'content' => $this->content,
                'agent_class' => $this->agentClass,
                'tags' => $this->tags,
            ]);
        } else {
            SavedPrompt::create([
                'name' => $this->name,
                'content' => $this->content,
                'agent_class' => $this->agentClass,
                'tags' => $this->tags,
            ]);
        }

        $this->reset(['name', 'content', 'agentClass', 'tags', 'editingId']);
    }

    public function delete(int $id): void
    {
        SavedPrompt::findOrFail($id)->delete();
    }

    public function cancelEdit(): void
    {
        $this->reset(['name', 'content', 'agentClass', 'tags', 'editingId']);
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

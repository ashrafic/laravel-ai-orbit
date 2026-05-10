<?php

namespace Ashrafic\AiOrbit\Http\Livewire;

use Ashrafic\AiOrbit\Services\ConversationRepository;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class ThreadExplorer extends Component
{
    use WithPagination;

    public string $search = '';

    public string $dateRange = '';

    public string $agentClass = '';

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingDateRange(): void
    {
        $this->resetPage();
    }

    public function updatingAgentClass(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function deleteConversation(string $id): void
    {
        app(ConversationRepository::class)->delete($id);
    }

    public function render(): View
    {
        $repository = app(ConversationRepository::class);

        $filters = [];

        if ($this->dateRange !== '' && $this->dateRange !== '0') {
            $filters['date_from'] = match ($this->dateRange) {
                'today' => now()->startOfDay(),
                '7d' => now()->subDays(7)->startOfDay(),
                '30d' => now()->subDays(30)->startOfDay(),
                default => null,
            };
        }

        if ($this->agentClass !== '' && $this->agentClass !== '0') {
            $filters['agent'] = $this->agentClass;
        }

        $conversations = $repository->list($filters);

        return view('ai-orbit::livewire.thread-explorer', [
            'conversations' => $conversations,
        ]);
    }
}

<?php

namespace Ashrafic\AiOrbit\Http\Livewire;

use Ashrafic\AiOrbit\Models\AiRun;
use Ashrafic\AiOrbit\Services\AgentRegistry;
use Ashrafic\AiOrbit\Services\AiRunRepository;
use Illuminate\Contracts\View\View;
use Laravel\Ai\Enums\Lab;
use Livewire\Component;
use Livewire\WithPagination;

class RunExplorer extends Component
{
    use WithPagination;

    public string $search = '';

    public string $dateRange = '';

    public string $operation = '';

    public string $status = '';

    public string $provider = '';

    public string $model = '';

    public string $agentClass = '';

    public string $conversationState = '';

    public string $sortField = 'started_at';

    public string $sortDirection = 'desc';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingDateRange(): void
    {
        $this->resetPage();
    }

    public function updatingOperation(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingProvider(): void
    {
        $this->resetPage();
    }

    public function updatingModel(): void
    {
        $this->resetPage();
    }

    public function updatingAgentClass(): void
    {
        $this->resetPage();
    }

    public function updatingConversationState(): void
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

    public function deleteRun(int $id): void
    {
        $run = AiRun::query()->find($id);

        if ($run) {
            $run->delete();
        }
    }

    /**
     * @return array<int, string>
     */
    public function availableOperations(): array
    {
        $known = [
            'agent_text',
            'agent_stream',
            'image',
            'audio',
            'transcription',
            'embeddings',
            'reranking',
            'file',
            'store',
            'store_file',
            'failover',
            'tool',
        ];

        $db = AiRun::query()
            ->select('operation')
            ->distinct()
            ->whereNotNull('operation')
            ->orderBy('operation')
            ->pluck('operation')
            ->toArray();

        return array_values(array_unique(array_merge($known, $db)));
    }

    /**
     * @return array<int, string>
     */
    public function availableStatuses(): array
    {
        return ['running', 'completed', 'failed'];
    }

    /**
     * @return array<int, string>
     */
    public function availableProviders(): array
    {
        $known = array_map(fn (Lab $lab) => $lab->value, Lab::cases());

        $db = AiRun::query()
            ->select('provider')
            ->distinct()
            ->whereNotNull('provider')
            ->orderBy('provider')
            ->pluck('provider')
            ->toArray();

        return array_values(array_unique(array_merge($known, $db)));
    }

    /**
     * @return array<int, string>
     */
    public function availableModels(): array
    {
        return AiRun::query()
            ->select('model')
            ->distinct()
            ->whereNotNull('model')
            ->orderBy('model')
            ->pluck('model')
            ->toArray();
    }

    /**
     * @return array<int, string>
     */
    public function availableAgentClasses(): array
    {
        $known = app(AgentRegistry::class)->all()->toArray();

        $db = AiRun::query()
            ->select('agent_class')
            ->distinct()
            ->whereNotNull('agent_class')
            ->orderBy('agent_class')
            ->pluck('agent_class')
            ->toArray();

        return array_values(array_unique(array_merge($known, $db)));
    }

    public function render(): View
    {
        $filters = [];

        if ($this->dateRange !== '' && $this->dateRange !== '0') {
            $filters['date_from'] = match ($this->dateRange) {
                'today' => now()->startOfDay(),
                '7d' => now()->subDays(7)->startOfDay(),
                '30d' => now()->subDays(30)->startOfDay(),
                default => null,
            };
        }

        if ($this->operation !== '' && $this->operation !== '0') {
            $filters['operation'] = $this->operation;
        }

        if ($this->status !== '' && $this->status !== '0') {
            $filters['status'] = $this->status;
        }

        if ($this->provider !== '' && $this->provider !== '0') {
            $filters['provider'] = $this->provider;
        }

        if ($this->model !== '' && $this->model !== '0') {
            $filters['model'] = $this->model;
        }

        if ($this->agentClass !== '' && $this->agentClass !== '0') {
            $filters['agent_class'] = $this->agentClass;
        }

        if ($this->conversationState !== '' && $this->conversationState !== '0') {
            $filters['conversation_state'] = $this->conversationState;
        }

        if ($this->search !== '') {
            $filters['search'] = $this->search;
        }

        $filters['sort_field'] = $this->sortField;
        $filters['sort_direction'] = $this->sortDirection;

        $runs = app(AiRunRepository::class)->list($filters);

        return view('ai-orbit::livewire.run-explorer', [
            'runs' => $runs,
        ]);
    }
}

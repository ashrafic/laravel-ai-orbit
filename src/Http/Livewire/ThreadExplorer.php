<?php

namespace Ashrafic\AiOrbit\Http\Livewire;

use Ashrafic\AiOrbit\Models\Bookmark;
use Ashrafic\AiOrbit\Services\ConversationRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
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

    public string $userIdFilter = '';

    public ?int $tokenMin = null;

    public ?int $tokenMax = null;

    public ?float $costMin = null;

    public ?float $costMax = null;

    public ?float $latencyMin = null;

    public ?float $latencyMax = null;

    public string $statusFilter = '';

    public bool $bookmarkedOnly = false;

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

    public function updatingUserIdFilter(): void
    {
        $this->resetPage();
    }

    public function updatingTokenMin(): void
    {
        $this->resetPage();
    }

    public function updatingTokenMax(): void
    {
        $this->resetPage();
    }

    public function updatingCostMin(): void
    {
        $this->resetPage();
    }

    public function updatingCostMax(): void
    {
        $this->resetPage();
    }

    public function updatingLatencyMin(): void
    {
        $this->resetPage();
    }

    public function updatingLatencyMax(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingBookmarkedOnly(): void
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

    public function toggleBookmark(string $conversationId): void
    {
        $existing = Bookmark::where('conversation_id', $conversationId)->first();

        if ($existing) {
            $existing->delete();
        } else {
            Bookmark::create(['conversation_id' => $conversationId]);
        }
    }

    public function isBookmarked(string $conversationId): bool
    {
        return Bookmark::where('conversation_id', $conversationId)->exists();
    }

    /**
     * @return array<int, string>
     */
    public function availableAgents(): array
    {
        try {
            return DB::table('agent_conversation_messages')
                ->select('agent')
                ->distinct()
                ->whereNotNull('agent')
                ->pluck('agent')
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
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

        if ($this->search !== '') {
            $filters['search'] = $this->search;
        }

        if ($this->userIdFilter !== '') {
            $filters['user_id'] = $this->userIdFilter;
        }

        if ($this->tokenMin !== null) {
            $filters['token_min'] = $this->tokenMin;
        }

        if ($this->tokenMax !== null) {
            $filters['token_max'] = $this->tokenMax;
        }

        $filters['bookmarked_only'] = $this->bookmarkedOnly;

        $conversations = $repository->list($filters);

        return view('ai-orbit::livewire.thread-explorer', [
            'conversations' => $conversations,
        ]);
    }
}

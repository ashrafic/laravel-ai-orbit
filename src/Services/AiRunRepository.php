<?php

namespace Ashrafic\AiOrbit\Services;

use Ashrafic\AiOrbit\Models\AiRun;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AiRunRepository
{
    /**
     * Get a paginated list of runs with optional filters.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, AiRun>
     */
    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = AiRun::query()->latest('started_at')->latest('id');

        foreach (['operation', 'status', 'provider', 'model', 'agent_class'] as $filter) {
            if (! empty($filters[$filter])) {
                $query->where($filter, $filters[$filter]);
            }
        }

        if (($filters['conversation_state'] ?? null) === 'linked') {
            $query->whereNotNull('conversation_id');
        }

        if (($filters['conversation_state'] ?? null) === 'unlinked') {
            $query->whereNull('conversation_id');
        }

        if (! empty($filters['date_from'])) {
            $query->where('started_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('started_at', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function find(int|string $id): ?AiRun
    {
        return AiRun::query()->find($id);
    }

    /**
     * @return Collection<int, AiRun>
     */
    public function forConversation(string $conversationId): Collection
    {
        return AiRun::query()
            ->where('conversation_id', $conversationId)
            ->latest('started_at')
            ->get();
    }

    public function hasRuns(): bool
    {
        return AiRun::query()->exists();
    }
}

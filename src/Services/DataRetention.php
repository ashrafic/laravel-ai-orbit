<?php

namespace Ashrafic\AiOrbit\Services;

use Ashrafic\AiOrbit\Services\Concerns\UsesAiConnection;
use Illuminate\Support\Collection;

class DataRetention
{
    use UsesAiConnection;

    /**
     * Get conversations eligible for purging based on retention policy.
     *
     * @return Collection<int, object>
     */
    public function getDeletableConversations(?int $retentionDays = null): Collection
    {
        $retentionDays = $retentionDays ?? (int) config('ai-orbit.audit.retention_days', 90);
        $cutoff = now()->subDays($retentionDays);

        if (! $this->hasTable('agent_conversations')) {
            return collect();
        }

        return $this->connection()->table('agent_conversations')
            ->where('created_at', '<', $cutoff)
            ->get();
    }

    /**
     * Perform a dry run — returns what would be deleted without actually deleting.
     *
     * @return array{count: int, conversations: Collection<int, object>}
     */
    public function dryRun(?int $retentionDays = null): array
    {
        $conversations = $this->getDeletableConversations($retentionDays);

        return [
            'count' => $conversations->count(),
            'conversations' => $conversations,
        ];
    }

    /**
     * Purge conversations older than the retention period.
     */
    public function purge(?int $retentionDays = null): int
    {
        $conversations = $this->getDeletableConversations($retentionDays);

        if ($conversations->isEmpty()) {
            return 0;
        }

        $ids = $conversations->pluck('id')->toArray();

        if ($this->hasTable('agent_conversation_messages')) {
            $this->connection()->table('agent_conversation_messages')
                ->whereIn('conversation_id', $ids)
                ->delete();
        }

        $this->connection()->table('agent_conversations')
            ->whereIn('id', $ids)
            ->delete();

        return count($ids);
    }
}

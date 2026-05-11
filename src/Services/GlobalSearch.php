<?php

namespace Ashrafic\AiOrbit\Services;

use Ashrafic\AiOrbit\Services\Concerns\UsesAiConnection;
use Illuminate\Pagination\LengthAwarePaginator;

class GlobalSearch
{
    use UsesAiConnection;

    /**
     * Search across all conversations and messages.
     *
     * @return LengthAwarePaginator<int, object>
     */
    public function search(string $query, int $perPage = 20): LengthAwarePaginator
    {
        if (! $this->hasTable('agent_conversation_messages') || ! $this->hasTable('agent_conversations')) {
            return new LengthAwarePaginator([], 0, $perPage);
        }

        $searchTerm = "%{$query}%";

        return $this->connection()->table('agent_conversation_messages')
            ->join('agent_conversations', 'agent_conversations.id', '=', 'agent_conversation_messages.conversation_id')
            ->select([
                'agent_conversation_messages.id',
                'agent_conversation_messages.conversation_id',
                'agent_conversation_messages.role',
                'agent_conversation_messages.content',
                'agent_conversation_messages.created_at',
                'agent_conversations.title as conversation_title',
            ])
            ->where(function ($q) use ($searchTerm) {
                $q->where('agent_conversation_messages.content', 'like', $searchTerm)
                    ->orWhere('agent_conversations.title', 'like', $searchTerm);
            })
            ->orderBy('agent_conversation_messages.created_at', 'desc')
            ->paginate($perPage);
    }
}

<?php

namespace Ashrafic\AiOrbit\Services;

use Ashrafic\AiOrbit\Services\Concerns\UsesAiConnection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ConversationRepository
{
    use UsesAiConnection;

    /**
     * Get a paginated list of conversations with optional filters.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, object>
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        if (! $this->hasTable('agent_conversations')) {
            return new LengthAwarePaginator([], 0, $perPage);
        }

        $query = $this->connection()->table('agent_conversations')
            ->select([
                'agent_conversations.id',
                'agent_conversations.user_id',
                'agent_conversations.title',
                'agent_conversations.created_at',
                'agent_conversations.updated_at',
            ]);

        if ($this->hasTable('agent_conversation_messages')) {
            $query->selectRaw('COUNT(agent_conversation_messages.id) as message_count');

            if ($this->hasColumn('agent_conversation_messages', 'agent')) {
                $query->addSelect(
                    $this->connection()->raw('MAX(agent_conversation_messages.agent) as agent_class')
                );
            }

            if ($this->hasColumn('agent_conversation_messages', 'usage')) {
                $query->addSelect(
                    $this->connection()->raw("COALESCE(SUM(JSON_EXTRACT(agent_conversation_messages.usage, '$.input_tokens')), 0) as total_input_tokens")
                );
                $query->addSelect(
                    $this->connection()->raw("COALESCE(SUM(JSON_EXTRACT(agent_conversation_messages.usage, '$.output_tokens')), 0) as total_output_tokens")
                );
            }

            $query->leftJoin('agent_conversation_messages', 'agent_conversations.id', '=', 'agent_conversation_messages.conversation_id');
        }

        $query->groupBy('agent_conversations.id');

        if (! empty($filters['date_from'])) {
            $query->where('agent_conversations.created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('agent_conversations.created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['agent']) && $this->hasTable('agent_conversation_messages')) {
            $query->where('agent_conversation_messages.agent', $filters['agent']);
        }

        $query->orderBy('agent_conversations.created_at', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Find a single conversation with its messages.
     */
    public function find(string $id): ?object
    {
        if (! $this->hasTable('agent_conversations')) {
            return null;
        }

        $conversation = $this->connection()->table('agent_conversations')
            ->where('id', $id)
            ->first();

        if ($conversation === null) {
            return null;
        }

        $conversation->messages = $this->messages($id);

        return $conversation;
    }

    /**
     * Get messages for a conversation, ordered chronologically.
     *
     * @return Collection<int, object>
     */
    public function messages(string $conversationId): Collection
    {
        if (! $this->hasTable('agent_conversation_messages')) {
            return collect();
        }

        $selectColumns = [
            'id',
            'conversation_id',
            'role',
            'content',
            'created_at',
        ];

        if ($this->hasColumn('agent_conversation_messages', 'agent')) {
            $selectColumns[] = 'agent';
        }

        if ($this->hasColumn('agent_conversation_messages', 'tool_calls')) {
            $selectColumns[] = 'tool_calls';
        }

        if ($this->hasColumn('agent_conversation_messages', 'tool_results')) {
            $selectColumns[] = 'tool_results';
        }

        if ($this->hasColumn('agent_conversation_messages', 'usage')) {
            $selectColumns[] = 'usage';
        }

        if ($this->hasColumn('agent_conversation_messages', 'attachments')) {
            $selectColumns[] = 'attachments';
        }

        if ($this->hasColumn('agent_conversation_messages', 'meta')) {
            $selectColumns[] = 'meta';
        }

        return $this->connection()->table('agent_conversation_messages')
            ->select($selectColumns)
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Delete a conversation and its messages.
     */
    public function delete(string $id): void
    {
        if ($this->hasTable('agent_conversation_messages')) {
            $this->connection()->table('agent_conversation_messages')
                ->where('conversation_id', $id)
                ->delete();
        }

        if ($this->hasTable('agent_conversations')) {
            $this->connection()->table('agent_conversations')
                ->where('id', $id)
                ->delete();
        }
    }
}

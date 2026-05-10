<?php

namespace Ashrafic\AiOrbit\Services;

use Ashrafic\AiOrbit\Services\Concerns\UsesAiConnection;
use Illuminate\Support\Collection;

class TokenAggregator
{
    use UsesAiConnection;

    /**
     * Get token usage statistics for today.
     *
     * @return array<string, int>
     */
    public function todayStats(): array
    {
        $totalConversations = 0;
        $totalMessages = 0;

        if ($this->hasTable('agent_conversations')) {
            $totalConversations = $this->connection()->table('agent_conversations')
                ->whereDate('created_at', today())
                ->count();
        }

        if ($this->hasTable('agent_conversation_messages')) {
            $totalMessages = $this->connection()->table('agent_conversation_messages')
                ->whereDate('created_at', today())
                ->count();
        }

        $inputTokens = 0;
        $outputTokens = 0;

        if ($this->hasTable('agent_conversation_messages') && $this->hasColumn('agent_conversation_messages', 'usage')) {
            $tokenData = $this->connection()->table('agent_conversation_messages')
                ->whereDate('created_at', today())
                ->selectRaw(
                    "COALESCE(SUM(JSON_EXTRACT(usage, '$.input_tokens')), 0) as input_tokens"
                )
                ->selectRaw(
                    "COALESCE(SUM(JSON_EXTRACT(usage, '$.output_tokens')), 0) as output_tokens"
                )
                ->first();

            $inputTokens = (int) ($tokenData->input_tokens ?? 0);
            $outputTokens = (int) ($tokenData->output_tokens ?? 0);
        }

        $providerCount = 0;
        $agentCount = 0;

        if ($this->hasTable('agent_conversation_messages')) {
            if ($this->hasColumn('agent_conversation_messages', 'agent')) {
                $agentData = $this->connection()->table('agent_conversation_messages')
                    ->whereDate('created_at', today())
                    ->selectRaw('COUNT(DISTINCT agent) as agent_count')
                    ->first();

                $agentCount = (int) ($agentData->agent_count ?? 0);
            }

            if ($this->hasColumn('agent_conversation_messages', 'meta')) {
                $providerData = $this->connection()->table('agent_conversation_messages')
                    ->whereDate('created_at', today())
                    ->selectRaw(
                        "COUNT(DISTINCT JSON_EXTRACT(meta, '$.provider')) as provider_count"
                    )
                    ->first();

                $providerCount = (int) ($providerData->provider_count ?? 0);
            }
        }

        return [
            'total_conversations' => $totalConversations,
            'total_messages' => $totalMessages,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'provider_count' => $providerCount,
            'agent_count' => $agentCount,
        ];
    }

    /**
     * Get token usage breakdown by agent class for today.
     *
     * @return Collection<int, object>
     */
    public function agentBreakdown(): Collection
    {
        if (! $this->hasTable('agent_conversation_messages')) {
            return collect();
        }

        if (! $this->hasColumn('agent_conversation_messages', 'agent')) {
            return collect();
        }

        $selects = [
            'agent',
            $this->connection()->raw('COUNT(*) as message_count'),
        ];

        if ($this->hasColumn('agent_conversation_messages', 'usage')) {
            $selects[] = $this->connection()->raw("COALESCE(SUM(JSON_EXTRACT(usage, '$.input_tokens')), 0) as input_tokens");
            $selects[] = $this->connection()->raw("COALESCE(SUM(JSON_EXTRACT(usage, '$.output_tokens')), 0) as output_tokens");
            $selects[] = $this->connection()->raw("COALESCE(SUM(JSON_EXTRACT(usage, '$.input_tokens')), 0) + COALESCE(SUM(JSON_EXTRACT(usage, '$.output_tokens')), 0) as total");
        }

        return $this->connection()->table('agent_conversation_messages')
            ->whereDate('created_at', today())
            ->select($selects)
            ->groupBy('agent')
            ->orderByDesc('total')
            ->get();
    }
}

<?php

namespace Ashrafic\AiOrbit\Services;

use Ashrafic\AiOrbit\Services\Concerns\UsesAiConnection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TokenAggregator
{
    use UsesAiConnection;

    /**
     * Get token usage statistics for a given time period.
     *
     * @return array<string, int>
     */
    public function todayStats(string $period = 'today'): array
    {
        [$from, $to] = $this->resolveDateRange($period);

        $totalConversations = 0;
        $totalMessages = 0;

        if ($this->hasTable('agent_conversations')) {
            $totalConversations = $this->applyDateFilter(
                $this->connection()->table('agent_conversations'), 'created_at', $from, $to
            )->count();
        }

        if ($this->hasTable('agent_conversation_messages')) {
            $totalMessages = $this->applyDateFilter(
                $this->connection()->table('agent_conversation_messages'), 'created_at', $from, $to
            )->count();
        }

        $inputTokens = 0;
        $outputTokens = 0;

        if ($this->hasTable('agent_conversation_messages') && $this->hasColumn('agent_conversation_messages', 'usage')) {
            $tokenData = $this->applyDateFilter(
                $this->connection()->table('agent_conversation_messages'), 'created_at', $from, $to
            )
                ->selectRaw(
                    "COALESCE(SUM(JSON_EXTRACT(`usage`, '$.prompt_tokens')), 0) as input_tokens"
                )
                ->selectRaw(
                    "COALESCE(SUM(JSON_EXTRACT(`usage`, '$.completion_tokens')), 0) as output_tokens"
                )
                ->first();

            $inputTokens = (int) ($tokenData->input_tokens ?? 0);
            $outputTokens = (int) ($tokenData->output_tokens ?? 0);
        }

        $providerCount = 0;
        $agentCount = 0;

        if ($this->hasTable('agent_conversation_messages')) {
            if ($this->hasColumn('agent_conversation_messages', 'agent')) {
                $agentData = $this->applyDateFilter(
                    $this->connection()->table('agent_conversation_messages'), 'created_at', $from, $to
                )
                    ->selectRaw('COUNT(DISTINCT agent) as agent_count')
                    ->first();

                $agentCount = (int) ($agentData->agent_count ?? 0);
            }

            if ($this->hasColumn('agent_conversation_messages', 'meta')) {
                $providerData = $this->applyDateFilter(
                    $this->connection()->table('agent_conversation_messages'), 'created_at', $from, $to
                )
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
     * Get token usage breakdown by agent class for a given time period.
     *
     * @return Collection<int, object>
     */
    public function agentBreakdown(string $period = 'today'): Collection
    {
        [$from, $to] = $this->resolveDateRange($period);

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
            $selects[] = $this->connection()->raw("COALESCE(SUM(JSON_EXTRACT(`usage`, '$.prompt_tokens')), 0) as input_tokens");
            $selects[] = $this->connection()->raw("COALESCE(SUM(JSON_EXTRACT(`usage`, '$.completion_tokens')), 0) as output_tokens");
            $selects[] = $this->connection()->raw("COALESCE(SUM(JSON_EXTRACT(`usage`, '$.prompt_tokens')), 0) + COALESCE(SUM(JSON_EXTRACT(`usage`, '$.completion_tokens')), 0) as total");
        }

        return $this->applyDateFilter(
            $this->connection()->table('agent_conversation_messages'), 'created_at', $from, $to
        )
            ->select($selects)
            ->groupBy('agent')
            ->orderByDesc('total')
            ->get();
    }

    /**
     * Resolve the date range from a period key.
     *
     * @return array{0: Carbon|string|null, 1: Carbon|string|null}
     */
    private function resolveDateRange(string $period): array
    {
        return match ($period) {
            '7d' => [now()->subDays(7)->startOfDay(), null],
            '30d' => [now()->subDays(30)->startOfDay(), null],
            'month' => [now()->startOfMonth()->startOfDay(), null],
            'all' => [null, null],
            default => [today(), today()], // 'today'
        };
    }

    /**
     * Apply date filters to a query builder instance.
     *
     * @param  Builder  $query
     * @param  Carbon|string|null  $from
     * @param  Carbon|string|null  $to
     */
    private function applyDateFilter($query, string $column, $from = null, $to = null): mixed
    {
        if ($from && $to && $from instanceof Carbon && $from->equalTo($to)) {
            return $query->whereDate($column, $from);
        }

        if ($from) {
            $query->where($column, '>=', $from);
        }

        return $query;
    }
}

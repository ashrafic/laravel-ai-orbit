<?php

namespace Ashrafic\AiOrbit\Services;

use Ashrafic\AiOrbit\Models\AiRun;
use Ashrafic\AiOrbit\Services\Concerns\UsesAiConnection;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

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

        if ($this->hasRunData($from, $to)) {
            return $this->runStats($from, $to);
        }

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
     * Get token usage breakdown for a given time period.
     *
     * @return Collection<int, object>
     */
    public function agentBreakdown(string $period = 'today', string $groupBy = 'agent'): Collection
    {
        [$from, $to] = $this->resolveDateRange($period);

        if ($this->hasRunData($from, $to)) {
            return $this->runBreakdown($from, $to, $groupBy);
        }

        if (! $this->hasTable('agent_conversation_messages')) {
            return collect();
        }

        $hasAgent = $this->hasColumn('agent_conversation_messages', 'agent');
        $hasMeta = $this->hasColumn('agent_conversation_messages', 'meta');

        $jsonVal = fn (string $path): string => "REPLACE(JSON_EXTRACT(meta, '{$path}'), '\"', '')";

        $groupColumn = match ($groupBy) {
            'model' => $hasMeta ? $jsonVal('$.model') : null,
            'provider' => $hasMeta ? $jsonVal('$.provider') : null,
            default => $hasAgent ? 'agent' : null,
        };

        if ($groupColumn === null) {
            return collect();
        }

        $selects = [
            $this->connection()->raw("{$groupColumn} as agent"),
            $this->connection()->raw('COUNT(*) as message_count'),
        ];

        if ($hasMeta) {
            $selects[] = $this->connection()->raw("COALESCE(MIN({$jsonVal('$.model')}), 'unknown') as model");
        }

        if ($this->hasColumn('agent_conversation_messages', 'usage')) {
            $selects[] = $this->connection()->raw("COALESCE(SUM(JSON_EXTRACT(`usage`, '$.prompt_tokens')), 0) as input_tokens");
            $selects[] = $this->connection()->raw("COALESCE(SUM(JSON_EXTRACT(`usage`, '$.completion_tokens')), 0) as output_tokens");
            $selects[] = $this->connection()->raw("COALESCE(SUM(JSON_EXTRACT(`usage`, '$.prompt_tokens')), 0) + COALESCE(SUM(JSON_EXTRACT(`usage`, '$.completion_tokens')), 0) as total");
        }

        return $this->applyDateFilter(
            $this->connection()->table('agent_conversation_messages'), 'created_at', $from, $to
        )
            ->select($selects)
            ->groupBy($this->connection()->raw($groupColumn))
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

    /**
     * @param  Carbon|string|null  $from
     * @param  Carbon|string|null  $to
     */
    private function hasRunData($from = null, $to = null): bool
    {
        if (! config('ai-orbit.observability.enabled', true) || ! Schema::hasTable('orbit_ai_runs')) {
            return false;
        }

        return $this->applyRunDateFilter(AiRun::query(), $from, $to)->exists();
    }

    /**
     * @param  Carbon|string|null  $from
     * @param  Carbon|string|null  $to
     * @return array<string, int>
     */
    private function runStats($from = null, $to = null): array
    {
        $query = $this->applyRunDateFilter(AiRun::query(), $from, $to);
        $tokenQuery = clone $query;
        $conversationQuery = clone $query;
        $providerQuery = clone $query;
        $agentQuery = clone $query;

        return [
            'total_conversations' => $conversationQuery->whereNotNull('conversation_id')->distinct('conversation_id')->count('conversation_id'),
            'total_messages' => $query->count(),
            'input_tokens' => (int) $tokenQuery->sum('input_tokens'),
            'output_tokens' => (int) $tokenQuery->sum('output_tokens'),
            'provider_count' => $providerQuery->whereNotNull('provider')->distinct('provider')->count('provider'),
            'agent_count' => $agentQuery->whereNotNull('agent_class')->distinct('agent_class')->count('agent_class'),
        ];
    }

    /**
     * @param  Carbon|string|null  $from
     * @param  Carbon|string|null  $to
     * @return Collection<int, object>
     */
    private function runBreakdown($from = null, $to = null, string $groupBy = 'agent'): Collection
    {
        $groupColumn = match ($groupBy) {
            'model' => 'model',
            'provider' => 'provider',
            default => 'agent_class',
        };

        return $this->applyRunDateFilter(AiRun::query(), $from, $to)
            ->selectRaw("COALESCE({$groupColumn}, 'unknown') as agent")
            ->selectRaw('COUNT(*) as message_count')
            ->selectRaw("COALESCE(MIN(model), 'unknown') as model")
            ->selectRaw('COALESCE(SUM(input_tokens), 0) as input_tokens')
            ->selectRaw('COALESCE(SUM(output_tokens), 0) as output_tokens')
            ->selectRaw('COALESCE(SUM(input_tokens), 0) + COALESCE(SUM(output_tokens), 0) as total')
            ->groupBy($groupColumn)
            ->orderByDesc('total')
            ->get();
    }

    /**
     * @param  EloquentBuilder<AiRun>  $query
     * @param  Carbon|string|null  $from
     * @param  Carbon|string|null  $to
     * @return EloquentBuilder<AiRun>
     */
    private function applyRunDateFilter(EloquentBuilder $query, $from = null, $to = null): EloquentBuilder
    {
        if ($from && $to && $from instanceof Carbon && $from->equalTo($to)) {
            return $query->whereDate('started_at', $from);
        }

        if ($from) {
            $query->where('started_at', '>=', $from);
        }

        return $query;
    }
}

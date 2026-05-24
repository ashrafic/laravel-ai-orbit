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
     * SDK tables provide conversation and conversation-message data.
     * The runs table contributes one-off calls (no conversation_id) and run metrics.
     *
     * @return array<string, int>
     */
    public function periodStats(string $period = 'today'): array
    {
        [$from, $to] = $this->resolveDateRange($period);

        $stats = $this->sdkStats($from, $to);

        if (Schema::hasTable('orbit_ai_runs') && config('ai-orbit.observability.enabled', true)) {
            $oneOff = $this->oneOffRunStats($from, $to);
            $allRuns = $this->runCounts($from, $to);

            $stats['total_messages'] += $oneOff['total_messages'];
            $stats['input_tokens'] += $oneOff['input_tokens'];
            $stats['output_tokens'] += $oneOff['output_tokens'];
            $stats['total_runs'] = $allRuns['total_runs'];
            $stats['completed_runs'] = $allRuns['completed_runs'];
            $stats['failed_runs'] = $allRuns['failed_runs'];
        } else {
            $stats['total_runs'] = 0;
            $stats['completed_runs'] = 0;
            $stats['failed_runs'] = 0;
        }

        return $stats;
    }

    /**
     * Get token usage breakdown for a given time period.
     *
     * Merges SDK conversation-message data with one-off runs.
     *
     * @return Collection<int, object>
     */
    public function agentBreakdown(string $period = 'today', string $groupBy = 'agent'): Collection
    {
        [$from, $to] = $this->resolveDateRange($period);

        if ($this->hasTable('agent_conversation_messages')) {
            $sdk = $this->sdkBreakdown($from, $to, $groupBy);

            $oneOff = (Schema::hasTable('orbit_ai_runs') && config('ai-orbit.observability.enabled', true))
                ? $this->oneOffRunBreakdown($from, $to, $groupBy)
                : collect();

            return $this->mergeBreakdowns($sdk, $oneOff);
        }

        if (Schema::hasTable('orbit_ai_runs') && config('ai-orbit.observability.enabled', true)) {
            return $this->runBreakdown($from, $to, $groupBy);
        }

        return collect();
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
     * @return array<string, int>
     */
    private function sdkStats($from = null, $to = null): array
    {
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
            $selects = [
                $this->connection()->raw("COALESCE(SUM(JSON_EXTRACT(`usage`, '$.prompt_tokens')), 0) as input_tokens"),
                $this->connection()->raw("COALESCE(SUM(JSON_EXTRACT(`usage`, '$.completion_tokens')), 0) as output_tokens"),
            ];

            $tokenData = $this->applyDateFilter(
                $this->connection()->table('agent_conversation_messages'), 'created_at', $from, $to
            )
                ->select($selects)
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
                    ->select($this->connection()->raw('COUNT(DISTINCT agent) as agent_count'))
                    ->first();

                $agentCount = (int) ($agentData->agent_count ?? 0);
            }

            if ($this->hasColumn('agent_conversation_messages', 'meta')) {
                $providerData = $this->applyDateFilter(
                    $this->connection()->table('agent_conversation_messages'), 'created_at', $from, $to
                )
                    ->select($this->connection()->raw(
                        "COUNT(DISTINCT JSON_EXTRACT(meta, '$.provider')) as provider_count"
                    ))
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
     * @param  Carbon|string|null  $from
     * @param  Carbon|string|null  $to
     * @return array{total_runs: int, completed_runs: int, failed_runs: int}
     */
    private function runCounts($from = null, $to = null): array
    {
        $query = $this->applyRunDateFilter(AiRun::query(), $from, $to);
        $completedQuery = clone $query;
        $failedQuery = clone $query;

        return [
            'total_runs' => $query->count(),
            'completed_runs' => $completedQuery->where('status', 'completed')->count(),
            'failed_runs' => $failedQuery->where('status', 'failed')->count(),
        ];
    }

    /**
     * Stats from runs without a conversation_id (one-off prompts).
     *
     * @param  Carbon|string|null  $from
     * @param  Carbon|string|null  $to
     * @return array{total_messages: int, input_tokens: int, output_tokens: int}
     */
    private function oneOffRunStats($from = null, $to = null): array
    {
        $query = $this->applyRunDateFilter(AiRun::query(), $from, $to)->whereNull('conversation_id');
        $tokenQuery = clone $query;

        return [
            'total_messages' => $query->count(),
            'input_tokens' => (int) $tokenQuery->sum('input_tokens'),
            'output_tokens' => (int) $tokenQuery->sum('output_tokens'),
        ];
    }

    /**
     * Breakdown of one-off runs (no conversation_id).
     *
     * @param  Carbon|string|null  $from
     * @param  Carbon|string|null  $to
     * @return Collection<int, object>
     */
    private function oneOffRunBreakdown($from = null, $to = null, string $groupBy = 'agent'): Collection
    {
        $groupColumn = match ($groupBy) {
            'model' => 'model',
            'provider' => 'provider',
            'operation' => 'operation',
            default => 'agent_class',
        };

        $query = $this->applyRunDateFilter(AiRun::query(), $from, $to)->toBase();

        return $query->whereNull('conversation_id')
            ->selectRaw("COALESCE({$groupColumn}, 'unknown') as agent")
            ->selectRaw('COUNT(*) as message_count')
            ->selectRaw("COALESCE(MIN(model), 'unknown') as model")
            ->selectRaw("COALESCE(MIN(provider), 'unknown') as provider")
            ->selectRaw('COALESCE(SUM(input_tokens), 0) as input_tokens')
            ->selectRaw('COALESCE(SUM(output_tokens), 0) as output_tokens')
            ->selectRaw('COALESCE(SUM(input_tokens), 0) + COALESCE(SUM(output_tokens), 0) as total')
            ->selectRaw('COALESCE(SUM(cost), 0) as total_cost')
            ->groupBy($groupColumn)
            ->orderByDesc('total_cost')
            ->get();
    }

    /**
     * Merge two breakdown collections by summing shared group keys.
     *
     * @param  Collection<int, object>  $sdk
     * @param  Collection<int, object>  $oneOff
     * @return Collection<int, object>
     */
    private function mergeBreakdowns(Collection $sdk, Collection $oneOff): Collection
    {
        if ($oneOff->isEmpty()) {
            return $sdk;
        }

        if ($sdk->isEmpty()) {
            return $oneOff;
        }

        $index = [];

        foreach ($sdk as $r) {
            $a = (array) $r;
            $key = (string) ($a['agent'] ?? 'unknown');
            $index[$key] = [
                'agent' => $a['agent'] ?? 'unknown',
                'model' => (string) ($a['model'] ?? 'unknown'),
                'provider' => (string) ($a['provider'] ?? ''),
                'message_count' => (int) ($a['message_count'] ?? 0),
                'input_tokens' => (int) ($a['input_tokens'] ?? 0),
                'output_tokens' => (int) ($a['output_tokens'] ?? 0),
                'total' => (int) ($a['total'] ?? 0),
                'total_cost' => (float) ($a['total_cost'] ?? 0),
            ];
        }

        foreach ($oneOff as $r) {
            $a = (array) $r;
            $key = (string) ($a['agent'] ?? 'unknown');

            if (isset($index[$key])) {
                $index[$key]['message_count'] += (int) ($a['message_count'] ?? 0);
                $index[$key]['input_tokens'] += (int) ($a['input_tokens'] ?? 0);
                $index[$key]['output_tokens'] += (int) ($a['output_tokens'] ?? 0);
                $index[$key]['total'] += (int) ($a['total'] ?? 0);
                $index[$key]['total_cost'] += (float) ($a['total_cost'] ?? 0);
            } else {
                $index[$key] = [
                    'agent' => $a['agent'] ?? 'unknown',
                    'model' => (string) ($a['model'] ?? 'unknown'),
                    'provider' => (string) ($a['provider'] ?? ''),
                    'message_count' => (int) ($a['message_count'] ?? 0),
                    'input_tokens' => (int) ($a['input_tokens'] ?? 0),
                    'output_tokens' => (int) ($a['output_tokens'] ?? 0),
                    'total' => (int) ($a['total'] ?? 0),
                    'total_cost' => (float) ($a['total_cost'] ?? 0),
                ];
            }
        }

        return collect(array_values($index))
            ->map(fn (array $item): object => (object) $item)
            ->sortByDesc('total');
    }

    /**
     * @param  Carbon|string|null  $from
     * @param  Carbon|string|null  $to
     * @return Collection<int, object>
     */
    private function sdkBreakdown($from = null, $to = null, string $groupBy = 'agent'): Collection
    {
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
            $selects[] = $this->connection()->raw("COALESCE(MIN({$jsonVal('$.provider')}), 'unknown') as provider");
        }

        if ($this->hasColumn('agent_conversation_messages', 'usage')) {
            $selects[] = $this->connection()->raw("COALESCE(SUM(JSON_EXTRACT(`usage`, '$.prompt_tokens')), 0) as input_tokens");
            $selects[] = $this->connection()->raw("COALESCE(SUM(JSON_EXTRACT(`usage`, '$.completion_tokens')), 0) as output_tokens");
            $selects[] = $this->connection()->raw("COALESCE(SUM(JSON_EXTRACT(`usage`, '$.prompt_tokens')), 0) + COALESCE(SUM(JSON_EXTRACT(`usage`, '$.completion_tokens')), 0) as total");
        }

        $result = $this->applyDateFilter(
            $this->connection()->table('agent_conversation_messages'), 'created_at', $from, $to
        )
            ->select($selects)
            ->groupBy($this->connection()->raw($groupColumn))
            ->orderByDesc('total')
            ->get();

        if ($result->isNotEmpty() && isset($result->first()->input_tokens)) {
            $calculator = app(CostCalculator::class);

            $result = $result->map(function ($row) use ($calculator) {
                $cost = $calculator->calculate(
                    $row->model ?? 'unknown',
                    (int) ($row->input_tokens ?? 0),
                    (int) ($row->output_tokens ?? 0),
                    $row->provider ?? null,
                );
                $row->total_cost = $cost['total'];

                return $row;
            });
        }

        return $result;
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
            'operation' => 'operation',
            default => 'agent_class',
        };

        $query = $this->applyRunDateFilter(AiRun::query(), $from, $to)->toBase();

        return $query
            ->selectRaw("COALESCE({$groupColumn}, 'unknown') as agent")
            ->selectRaw('COUNT(*) as message_count')
            ->selectRaw("COALESCE(MIN(model), 'unknown') as model")
            ->selectRaw('COALESCE(SUM(input_tokens), 0) as input_tokens')
            ->selectRaw('COALESCE(SUM(output_tokens), 0) as output_tokens')
            ->selectRaw('COALESCE(SUM(input_tokens), 0) + COALESCE(SUM(output_tokens), 0) as total')
            ->selectRaw('COALESCE(SUM(cost), 0) as total_cost')
            ->groupBy($groupColumn)
            ->orderByDesc('total_cost')
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

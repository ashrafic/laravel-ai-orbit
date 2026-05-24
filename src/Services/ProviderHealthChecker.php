<?php

namespace Ashrafic\AiOrbit\Services;

use Ashrafic\AiOrbit\Models\AiRun;
use Ashrafic\AiOrbit\Services\Concerns\UsesAiConnection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProviderHealthChecker
{
    use UsesAiConnection;

    /**
     * Get health metrics per provider, merging data from both AiRuns
     * (one-off LLM calls) and agent_conversation_messages (continuous chats).
     *
     * @return Collection<int, array{provider: string, success_rate: float, error_count: int, rate_limit_count: int, avg_latency_ms: float}>
     */
    public function getHealthMetrics(string $period = '7d'): Collection
    {
        $dateFrom = match ($period) {
            '24h' => now()->subDay(),
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            default => now()->subDays(7),
        };

        $fromRuns = $this->metricsFromRuns($period, $dateFrom);
        $fromConversations = $this->metricsFromConversations($period, $dateFrom);

        return $this->mergeMetrics($fromRuns, $fromConversations);
    }

    /**
     * Get health metrics from the orbit_ai_runs table (one-off LLM calls).
     *
     * @return Collection<int, array{provider: string, total_requests: int, error_count: int, rate_limit_count: int, avg_latency_ms: float, latency_p50: float, latency_p95: float, latency_p99: float}>
     */
    private function metricsFromRuns(string $period, Carbon $dateFrom): Collection
    {
        if (! Schema::hasTable('orbit_ai_runs')) {
            return collect();
        }

        $providers = DB::table('orbit_ai_runs')
            ->where('started_at', '>=', $dateFrom)
            ->whereNotNull('provider')
            ->select('provider')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('provider')
            ->get();

        $results = collect();

        foreach ($providers as $p) {
            $provider = $p->provider ?? 'unknown';
            $total = (int) $p->total;

            $errorCount = AiRun::query()
                ->where('started_at', '>=', $dateFrom)
                ->where('provider', $provider)
                ->where('status', 'failed')
                ->count();

            $rateLimitCount = AiRun::query()
                ->where('started_at', '>=', $dateFrom)
                ->where('provider', $provider)
                ->where('status', 'failed')
                ->where(function ($q) {
                    $q->where('error', 'like', '%rate limit%')
                        ->orWhere('error', 'like', '%429%')
                        ->orWhere('error', 'like', '%too many%');
                })
                ->count();

            $avgLatency = round(
                (float) AiRun::query()
                    ->where('started_at', '>=', $dateFrom)
                    ->where('provider', $provider)
                    ->whereNotNull('latency_ms')
                    ->avg('latency_ms'),
                2
            );

            $results->push([
                'provider' => $provider,
                'total_requests' => $total,
                'error_count' => $errorCount,
                'rate_limit_count' => $rateLimitCount,
                'avg_latency_ms' => $avgLatency,
                'latency_p50' => round($this->latencyPercentile($provider, $dateFrom, 0.50)),
                'latency_p95' => round($this->latencyPercentile($provider, $dateFrom, 0.95)),
                'latency_p99' => round($this->latencyPercentile($provider, $dateFrom, 0.99)),
            ]);
        }

        return $results;
    }

    /**
     * Get health metrics from the agent_conversation_messages table (continuous chat).
     *
     * @return Collection<int, array{provider: string, total_requests: int, error_count: int, rate_limit_count: int, avg_latency_ms: float}>
     */
    private function metricsFromConversations(string $period, Carbon $dateFrom): Collection
    {
        if (! $this->hasTable('agent_conversation_messages')) {
            return collect();
        }

        $jsonProvider = "REPLACE(JSON_EXTRACT(meta, '\$.provider'), '\"', '')";

        $providers = $this->connection()->table('agent_conversation_messages')
            ->where('created_at', '>=', $dateFrom)
            ->whereNotNull('meta')
            ->whereRaw($jsonProvider.' IS NOT NULL')
            ->whereRaw($jsonProvider." != ''")
            ->selectRaw($jsonProvider.' as provider')
            ->selectRaw('COUNT(*) as total')
            ->groupBy($this->connection()->raw($jsonProvider))
            ->get();

        $results = collect();

        foreach ($providers as $p) {
            $provider = $p->provider;

            if ($provider === null || $provider === '') {
                continue;
            }

            $total = (int) $p->total;

            $errorCount = $this->connection()->table('agent_conversation_messages')
                ->where('created_at', '>=', $dateFrom)
                ->whereRaw($jsonProvider.' = ?', [$provider])
                ->where(function ($q) {
                    $q->where('role', 'tool')
                        ->orWhereRaw("JSON_EXTRACT(meta, '$.error') IS NOT NULL");
                })
                ->count();

            $rateLimitCount = $this->connection()->table('agent_conversation_messages')
                ->where('created_at', '>=', $dateFrom)
                ->whereRaw($jsonProvider.' = ?', [$provider])
                ->where(function ($q) {
                    $q->whereRaw("JSON_EXTRACT(meta, '$.error') LIKE '%rate limit%'")
                        ->orWhereRaw("JSON_EXTRACT(meta, '$.error') LIKE '%429%'")
                        ->orWhereRaw("JSON_EXTRACT(meta, '$.error') LIKE '%too many%'");
                })
                ->count();

            $avgLatency = 0;
            $latencyData = $this->connection()->table('agent_conversation_messages')
                ->where('created_at', '>=', $dateFrom)
                ->whereRaw($jsonProvider.' = ?', [$provider])
                ->whereRaw("JSON_EXTRACT(meta, '$.latency_ms') IS NOT NULL")
                ->selectRaw("AVG(JSON_EXTRACT(meta, '$.latency_ms')) as avg_ms")
                ->first();

            if ($latencyData && isset($latencyData->avg_ms)) {
                $avgLatency = round((float) $latencyData->avg_ms, 2);
            }

            $results->push([
                'provider' => $provider,
                'total_requests' => $total,
                'error_count' => $errorCount,
                'rate_limit_count' => $rateLimitCount,
                'avg_latency_ms' => $avgLatency,
            ]);
        }

        return $results;
    }

    /**
     * Merge two metric collections by provider, summing counts and computing
     * weighted averages for latencies. Percentile latencies come from AiRuns
     * only (conversations only store averages, not individual data points).
     *
     * @return Collection<int, array{provider: string, success_rate: float, error_count: int, rate_limit_count: int, avg_latency_ms: float}>
     */
    private function mergeMetrics(Collection $fromRuns, Collection $fromConversations): Collection
    {
        $merged = [];

        foreach ($fromRuns as $metric) {
            $provider = $metric['provider'];
            $merged[$provider] = [
                'total_requests' => $metric['total_requests'],
                'error_count' => $metric['error_count'],
                'rate_limit_count' => $metric['rate_limit_count'],
                'total_latency' => $metric['avg_latency_ms'] * $metric['total_requests'],
                'latency_count' => $metric['total_requests'],
                'latency_p50' => $metric['latency_p50'] ?? null,
                'latency_p95' => $metric['latency_p95'] ?? null,
                'latency_p99' => $metric['latency_p99'] ?? null,
            ];
        }

        foreach ($fromConversations as $metric) {
            $provider = $metric['provider'];

            if (! isset($merged[$provider])) {
                $merged[$provider] = [
                    'total_requests' => 0,
                    'error_count' => 0,
                    'rate_limit_count' => 0,
                    'total_latency' => 0.0,
                    'latency_count' => 0,
                    'latency_p50' => null,
                    'latency_p95' => null,
                    'latency_p99' => null,
                ];
            }

            $merged[$provider]['total_requests'] += $metric['total_requests'];
            $merged[$provider]['error_count'] += $metric['error_count'];
            $merged[$provider]['rate_limit_count'] += $metric['rate_limit_count'];

            if (! empty($metric['avg_latency_ms']) && $metric['total_requests'] > 0) {
                $merged[$provider]['total_latency'] += $metric['avg_latency_ms'] * $metric['total_requests'];
                $merged[$provider]['latency_count'] += $metric['total_requests'];
            }
        }

        $results = collect();

        foreach ($merged as $provider => $data) {
            $total = $data['total_requests'];
            $errorCount = $data['error_count'];

            $successRate = $total > 0
                ? round((($total - $errorCount) / $total) * 100, 2)
                : 100.0;

            $avgLatency = $data['latency_count'] > 0
                ? round($data['total_latency'] / $data['latency_count'], 2)
                : 0;

            $metric = [
                'provider' => $provider,
                'total_requests' => $total,
                'success_rate' => $successRate,
                'error_count' => $errorCount,
                'rate_limit_count' => $data['rate_limit_count'],
                'avg_latency_ms' => $avgLatency,
                'status' => $successRate >= 95 ? 'healthy' : ($successRate >= 80 ? 'degraded' : 'unhealthy'),
            ];

            if ($data['latency_p50'] !== null) {
                $metric['latency_p50'] = $data['latency_p50'];
            }
            if ($data['latency_p95'] !== null) {
                $metric['latency_p95'] = $data['latency_p95'];
            }
            if ($data['latency_p99'] !== null) {
                $metric['latency_p99'] = $data['latency_p99'];
            }

            $results->push($metric);
        }

        return $results;
    }

    private function latencyPercentile(string $provider, Carbon|string $dateFrom, float $percentile): float
    {
        $latencies = AiRun::query()
            ->where('started_at', '>=', $dateFrom)
            ->where('provider', $provider)
            ->whereNotNull('latency_ms')
            ->orderBy('latency_ms')
            ->pluck('latency_ms');

        if ($latencies->isEmpty()) {
            return 0.0;
        }

        $count = $latencies->count();
        $index = (int) ceil($percentile * $count) - 1;

        return (float) ($latencies[$index] ?? $latencies->last());
    }
}

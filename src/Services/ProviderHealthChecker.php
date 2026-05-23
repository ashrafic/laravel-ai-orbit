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
     * Get health metrics per provider.
     *
     * @return Collection<int, array{provider: string, success_rate: float, error_count: int, rate_limit_count: int, avg_latency_ms: float}>
     */
    public function getHealthMetrics(string $period = '7d'): Collection
    {
        if ($this->hasRunHealthData()) {
            return $this->healthFromRuns($period);
        }

        if (! $this->hasTable('agent_conversation_messages')) {
            return collect();
        }

        $dateFrom = match ($period) {
            '24h' => now()->subDay(),
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            default => now()->subDays(7),
        };

        $jsonProvider = "REPLACE(JSON_EXTRACT(meta, '\$.provider'), '\"', '')";

        $providers = $this->connection()->table('agent_conversation_messages')
            ->where('created_at', '>=', $dateFrom)
            ->whereNotNull('meta')
            ->selectRaw($jsonProvider.' as provider')
            ->selectRaw('COUNT(*) as total')
            ->groupBy($this->connection()->raw($jsonProvider))
            ->get();

        $results = collect();

        foreach ($providers as $p) {
            $provider = $p->provider ?? 'unknown';
            $total = (int) $p->total;

            $errorCount = $this->connection()->table('agent_conversation_messages')
                ->where('created_at', '>=', $dateFrom)
                ->whereRaw($jsonProvider.' = ?', [$provider])
                ->where(function ($q) {
                    $q->where('role', 'tool')
                        ->orWhereRaw("JSON_EXTRACT(meta, '$.error') IS NOT NULL");
                })
                ->count();

            $successRate = $total > 0
                ? round((($total - $errorCount) / $total) * 100, 2)
                : 100.0;

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
                'success_rate' => $successRate,
                'error_count' => $errorCount,
                'rate_limit_count' => $rateLimitCount,
                'avg_latency_ms' => $avgLatency,
                'status' => $successRate >= 95 ? 'healthy' : ($successRate >= 80 ? 'degraded' : 'unhealthy'),
            ]);
        }

        return $results;
    }

    private function hasRunHealthData(): bool
    {
        return config('ai-orbit.observability.enabled', true)
            && Schema::hasTable('orbit_ai_runs')
            && AiRun::query()->exists();
    }

    /**
     * @return Collection<int, array{provider: string, success_rate: float, error_count: int, rate_limit_count: int, avg_latency_ms: float}>
     */
    private function healthFromRuns(string $period): Collection
    {
        $dateFrom = match ($period) {
            '24h' => now()->subDay(),
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            default => now()->subDays(7),
        };

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

            $successRate = $total > 0
                ? round((($total - $errorCount) / $total) * 100, 2)
                : 100.0;

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
                'success_rate' => $successRate,
                'error_count' => $errorCount,
                'rate_limit_count' => $rateLimitCount,
                'avg_latency_ms' => $avgLatency,
                'latency_p50' => round($this->latencyPercentile($provider, $dateFrom, 0.50)),
                'latency_p95' => round($this->latencyPercentile($provider, $dateFrom, 0.95)),
                'latency_p99' => round($this->latencyPercentile($provider, $dateFrom, 0.99)),
                'status' => $successRate >= 95 ? 'healthy' : ($successRate >= 80 ? 'degraded' : 'unhealthy'),
            ]);
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

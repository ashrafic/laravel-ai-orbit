<?php

namespace Ashrafic\AiOrbit\Services;

use Ashrafic\AiOrbit\Services\Concerns\UsesAiConnection;
use Illuminate\Support\Collection;

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
        if (! $this->hasTable('agent_conversation_messages')) {
            return collect();
        }

        $dateFrom = match ($period) {
            '24h' => now()->subDay(),
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            default => now()->subDays(7),
        };

        $providers = $this->connection()->table('agent_conversation_messages')
            ->where('created_at', '>=', $dateFrom)
            ->whereNotNull('meta')
            ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(meta, '$.provider')) as provider")
            ->selectRaw('COUNT(*) as total')
            ->groupBy($this->connection()->raw("JSON_UNQUOTE(JSON_EXTRACT(meta, '$.provider'))"))
            ->get();

        $results = collect();

        foreach ($providers as $p) {
            $provider = $p->provider ?? 'unknown';
            $total = (int) $p->total;

            $errorCount = $this->connection()->table('agent_conversation_messages')
                ->where('created_at', '>=', $dateFrom)
                ->whereRaw("JSON_EXTRACT(meta, '$.provider') = ?", [$provider])
                ->where(function ($q) {
                    $q->where('role', 'tool')
                        ->orWhereRaw("JSON_EXTRACT(meta, '$.error') IS NOT NULL");
                })
                ->count();

            $successRate = $total > 0
                ? round((($total - $errorCount) / $total) * 100, 2)
                : 100.0;

            $results->push([
                'provider' => $provider,
                'total_requests' => $total,
                'success_rate' => $successRate,
                'error_count' => $errorCount,
                'rate_limit_count' => 0,
                'avg_latency_ms' => 0,
                'status' => $successRate >= 95 ? 'healthy' : ($successRate >= 80 ? 'degraded' : 'unhealthy'),
            ]);
        }

        return $results;
    }
}

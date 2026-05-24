<?php

namespace Ashrafic\AiOrbit\Services;

use Ashrafic\AiOrbit\Services\Concerns\UsesAiConnection;
use Illuminate\Support\Collection;

class AgentHealthScorer
{
    use UsesAiConnection;

    /**
     * Calculate health score (0-100) for a given agent.
     *
     * @return array{score: int, total_requests: int, error_rate: float, avg_tokens: int, status: string}
     */
    public function score(string $agentClass, string $period = '7d'): array
    {
        $dateFrom = match ($period) {
            '24h' => now()->subDay(),
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            default => now()->subDays(7),
        };

        if (! $this->hasTable('agent_conversation_messages') || ! $this->hasColumn('agent_conversation_messages', 'agent')) {
            return $this->defaultScore();
        }

        $total = $this->connection()->table('agent_conversation_messages')
            ->where('agent', $agentClass)
            ->where('created_at', '>=', $dateFrom)
            ->count();

        if ($total === 0) {
            return $this->defaultScore();
        }

        $errors = $this->connection()->table('agent_conversation_messages')
            ->where('agent', $agentClass)
            ->where('created_at', '>=', $dateFrom)
            ->where(function ($q) {
                $q->where('role', 'tool')
                    ->orWhereRaw("JSON_EXTRACT(meta, '$.error') IS NOT NULL");
            })
            ->count();

        $errorRate = round(($errors / $total) * 100, 2);

        $avgTokens = 0;

        if ($this->hasColumn('agent_conversation_messages', 'usage')) {
            $tokenData = $this->connection()->table('agent_conversation_messages')
                ->where('agent', $agentClass)
                ->where('created_at', '>=', $dateFrom)
                ->selectRaw('AVG(JSON_EXTRACT(`usage`, "$.prompt_tokens") + JSON_EXTRACT(`usage`, "$.completion_tokens")) as avg')
                ->first();

            $avgTokens = (int) ($tokenData->avg ?? 0);
        }

        $score = max(0, (int) round(100 - ($errorRate * 2)));
        $score = min(100, $score);

        return [
            'score' => $score,
            'total_requests' => $total,
            'error_rate' => $errorRate,
            'avg_tokens' => $avgTokens,
            'status' => $score >= 80 ? 'healthy' : ($score >= 50 ? 'warning' : 'critical'),
        ];
    }

    /**
     * Get health scores for all agents.
     *
     * @return Collection<int, array>
     */
    public function scoreAll(string $period = '7d'): Collection
    {
        if (! $this->hasTable('agent_conversation_messages') || ! $this->hasColumn('agent_conversation_messages', 'agent')) {
            return collect();
        }

        $dateFrom = match ($period) {
            '24h' => now()->subDay(),
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            default => now()->subDays(7),
        };

        $agents = $this->connection()->table('agent_conversation_messages')
            ->where('created_at', '>=', $dateFrom)
            ->whereNotNull('agent')
            ->distinct()
            ->pluck('agent');

        return $agents->map(fn ($agent) => array_merge(
            ['agent' => $agent],
            $this->score($agent, $period)
        ))->sortByDesc('score')->values();
    }

    /**
     * @return array{score: int, total_requests: int, error_rate: float, avg_tokens: int, status: string}
     */
    private function defaultScore(): array
    {
        return [
            'score' => 100,
            'total_requests' => 0,
            'error_rate' => 0,
            'avg_tokens' => 0,
            'status' => 'healthy',
        ];
    }
}

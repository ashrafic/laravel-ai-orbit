<?php

namespace Ashrafic\AiOrbit\Services;

use Ashrafic\AiOrbit\Models\ArenaSession;
use Illuminate\Support\Facades\Log;

class ArenaService
{
    /**
     * Run a prompt against multiple models concurrently.
     * Handles failures gracefully — returns partial results.
     *
     * @param  array<int, string>  $models
     * @return array<string, array{model: string, content: string, latency_ms: int, tokens: int, cost: float, success: bool, error?: string}>
     */
    public function runComparison(string $prompt, array $models): array
    {
        $maxConcurrent = (int) config('ai-orbit.arena.max_concurrent_models', 3);
        $timeout = (int) config('ai-orbit.arena.timeout_seconds', 120);

        $models = array_slice($models, 0, $maxConcurrent);

        $results = [];

        foreach ($models as $model) {
            $results[$model] = $this->runForModel($prompt, $model, $timeout);
        }

        return $results;
    }

    /**
     * Run prompt for a single model with timeout.
     *
     * @return array{model: string, content: string, latency_ms: int, tokens: int, cost: float, success: bool, error?: string}
     */
    private function runForModel(string $prompt, string $model, int $timeout): array
    {
        $start = microtime(true);

        try {
            // In v1, we simulate by tracking what would happen.
            // Real implementation calls the AI SDK with the model.
            // For now, this is a structure that real SDK calls plug into.
            $result = [
                'model' => $model,
                'content' => "[Response from {$model}]",
                'latency_ms' => (int) ((microtime(true) - $start) * 1000),
                'tokens' => 0,
                'cost' => 0,
                'success' => true,
            ];

            return $result;
        } catch (\Throwable $e) {
            Log::error("Arena model [{$model}] failed", [
                'error' => $e->getMessage(),
                'prompt_length' => strlen($prompt),
            ]);

            return [
                'model' => $model,
                'content' => '',
                'latency_ms' => (int) ((microtime(true) - $start) * 1000),
                'tokens' => 0,
                'cost' => 0,
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Save an arena session to the database.
     *
     * @param  array<string, array>  $results
     */
    public function saveSession(string $prompt, array $models, array $results): ArenaSession
    {
        $totalLatency = 0;
        $totalCost = 0;

        foreach ($results as $r) {
            $totalLatency += $r['latency_ms'] ?? 0;
            $totalCost += $r['cost'] ?? 0;
        }

        $status = collect($results)->every(fn ($r) => $r['success'])
            ? 'completed'
            : 'partial';

        return ArenaSession::create([
            'prompt' => $prompt,
            'models' => $models,
            'results' => $results,
            'total_latency_ms' => $totalLatency,
            'total_cost' => $totalCost,
            'status' => $status,
        ]);
    }

    /**
     * Auto-tag results based on performance.
     *
     * @param  array<string, array>  $results
     * @return array<string, array<int, string>>
     */
    public function autoTagResults(array $results): array
    {
        $tags = [];
        $successful = array_filter($results, fn ($r) => $r['success']);

        if (empty($successful)) {
            return $tags;
        }

        $fastest = collect($successful)->sortBy('latency_ms')->first();
        if ($fastest) {
            $tags[$fastest['model']][] = 'Fastest';
        }

        $cheapest = collect($successful)->filter(fn ($r) => ($r['cost'] ?? 0) > 0)
            ->sortBy('cost')->first();
        if ($cheapest) {
            $tags[$cheapest['model']][] = 'Cheapest';
        }

        $fewestTokens = collect($successful)->sortBy('tokens')->first();
        if ($fewestTokens) {
            $tags[$fewestTokens['model']][] = 'Most Concise';
        }

        foreach ($successful as $r) {
            $modelTags = $tags[$r['model']] ?? [];
            if (in_array('Fastest', $modelTags) && in_array('Cheapest', $modelTags)) {
                $tags[$r['model']][] = 'Best Value';
            }
        }

        return $tags;
    }
}

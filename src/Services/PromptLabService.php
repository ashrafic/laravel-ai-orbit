<?php

namespace Ashrafic\AiOrbit\Services;

use Ashrafic\AiOrbit\Agent\OrbitPromptLabAgent;
use Ashrafic\AiOrbit\Models\PromptLabSession;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Ai;

class PromptLabService
{
    /**
     * Run comparison across multiple provider+model slots.
     *
     * @param  array<int, array{provider: string, model: string}>  $slots
     * @return array<int, array{provider: string, model: string, content: string, latency_ms: int, tokens: int, cost: float, success: bool, error?: string}>
     */
    public function runComparison(
        string $prompt,
        array $slots,
        string $systemPrompt = '',
        float $temperature = 1.0,
        ?int $maxTokens = null,
        ?array $context = null,
        float $topP = 1.0,
    ): array {
        $timeout = (int) config('ai-orbit.prompt-lab.timeout_seconds', 120);

        $results = [];

        foreach ($slots as $index => $slot) {
            $result = $this->runForSlot($prompt, $slot, $systemPrompt, $temperature, $maxTokens, $context, $topP, $timeout);

            $entry = [
                'provider' => $slot['provider'],
                'model' => $slot['model'],
                'content' => $result['content'],
                'latency_ms' => $result['latency_ms'],
                'tokens' => $result['tokens'],
                'cost' => $result['cost'],
                'success' => $result['success'],
            ];

            if (isset($result['error'])) {
                $entry['error'] = $result['error'];
            }

            $results[$index] = $entry;
        }

        return $results;
    }

    /**
     * Run prompt for a single slot.
     */
    private function runForSlot(
        string $prompt,
        array $slot,
        string $systemPrompt,
        float $temperature,
        ?int $maxTokens,
        ?array $context,
        float $topP,
        int $timeout,
    ): array {
        $start = microtime(true);

        try {
            $agent = new OrbitPromptLabAgent(
                systemPrompt: $systemPrompt,
                model: $slot['model'],
                provider: $slot['provider'],
                labTemperature: $temperature,
                labMaxTokens: $maxTokens,
                labContext: $context,
                labTopP: $topP,
            );

            $response = $agent->prompt($prompt, timeout: $timeout);

            return [
                'content' => $response->text,
                'latency_ms' => (int) ((microtime(true) - $start) * 1000),
                'tokens' => $response->usage->promptTokens + $response->usage->completionTokens,
                'cost' => 0,
                'success' => true,
            ];
        } catch (\Throwable $e) {
            Log::error('Prompt Lab slot failed', [
                'provider' => $slot['provider'] ?? 'unknown',
                'model' => $slot['model'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            return [
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
     * Get available models for a configured provider.
     */
    public function getModelsForProvider(string $providerName): array
    {
        try {
            $provider = Ai::textProvider($providerName);

            return [
                'default' => $provider->defaultTextModel(),
                'cheapest' => $provider->cheapestTextModel(),
                'smartest' => $provider->smartestTextModel(),
            ];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Get all configured provider names from ai config.
     */
    public function getConfiguredProviders(): array
    {
        $providers = config('ai.providers', []);

        return array_keys($providers);
    }

    /**
     * Save a prompt lab session to the database.
     */
    public function saveSession(
        string $prompt,
        array $slots,
        array $results,
        string $systemPrompt = '',
        float $temperature = 1.0,
        ?int $maxTokens = null,
        ?array $context = null,
        float $topP = 1.0,
    ): PromptLabSession {
        $totalLatency = 0;
        $totalCost = 0;

        foreach ($results as $r) {
            $totalLatency += $r['latency_ms'] ?? 0;
            $totalCost += $r['cost'] ?? 0;
        }

        $status = collect($results)->every(fn ($r) => $r['success'])
            ? 'completed'
            : 'partial';

        return PromptLabSession::create([
            'prompt' => $prompt,
            'system_prompt' => $systemPrompt,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
            'top_p' => $topP,
            'context' => $context,
            'slots' => $slots,
            'results' => $results,
            'tags' => $this->autoTagResults($results),
            'total_latency_ms' => $totalLatency,
            'total_cost' => $totalCost,
            'status' => $status,
        ]);
    }

    /**
     * Auto-tag results based on performance.
     */
    public function autoTagResults(array $results): array
    {
        $tags = [];
        $successful = array_values(array_filter($results, fn ($r) => $r['success']));

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

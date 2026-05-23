<?php

namespace Ashrafic\AiOrbit\Services;

use Ashrafic\AiOrbit\Models\PricingRule;
use Illuminate\Support\Collection;

class CostCalculator
{
    /**
     * Calculate cost for given model and token counts.
     *
     * @return array{input_cost: float, output_cost: float, total: float, currency: string, priced: bool, missing_pricing: bool}
     */
    public function calculate(string $model, int $inputTokens, int $outputTokens, ?string $provider = null): array
    {
        $rule = $this->findRule($model, $provider);

        if (! $rule) {
            return [
                'input_cost' => 0.0,
                'output_cost' => 0.0,
                'total' => 0.0,
                'currency' => config('ai-orbit.currency', 'USD'),
                'priced' => false,
                'missing_pricing' => true,
            ];
        }

        $inputCost = (float) $rule->input_cost_per_1m * ($inputTokens / 1_000_000);
        $outputCost = (float) $rule->output_cost_per_1m * ($outputTokens / 1_000_000);

        return [
            'input_cost' => round($inputCost, 6),
            'output_cost' => round($outputCost, 6),
            'total' => round($inputCost + $outputCost, 6),
            'currency' => $rule->currency,
            'priced' => true,
            'missing_pricing' => false,
        ];
    }

    private function findRule(string $model, ?string $provider = null): ?PricingRule
    {
        if ($provider) {
            $rule = PricingRule::query()
                ->where('model', $model)
                ->where('provider', $provider)
                ->first();

            if ($rule) {
                return $rule;
            }
        }

        $rule = PricingRule::query()
            ->where('model', $model)
            ->whereNull('provider')
            ->first();

        if ($rule) {
            return $rule;
        }

        return PricingRule::query()
            ->where(function ($q) use ($model): void {
                $q->where('model', $model)
                    ->orWhere('model', 'like', '%'.$model.'%');
            })
            ->when($provider, function ($q) use ($provider): void {
                $q->where(function ($q) use ($provider): void {
                    $q->where('provider', $provider)
                        ->orWhereNull('provider');
                });
            })
            ->orderByRaw('provider is null')
            ->first();
    }

    /**
     * Calculate total cost for a set of conversations.
     *
     * @param  Collection<int, object>  $conversations
     */
    public function calculateForConversations(Collection $conversations): float
    {
        $total = 0.0;

        foreach ($conversations as $conv) {
            $inputTokens = (int) ($conv->total_input_tokens ?? 0);
            $outputTokens = (int) ($conv->total_output_tokens ?? 0);

            if ($inputTokens > 0 || $outputTokens > 0) {
                $model = $conv->model ?? null;

                if ($model) {
                    $result = $this->calculate($model, $inputTokens, $outputTokens, $conv->provider ?? null);
                    $total += $result['total'];
                }
            }
        }

        return round($total, 4);
    }
}

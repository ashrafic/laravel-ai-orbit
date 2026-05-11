<?php

namespace Ashrafic\AiOrbit\Services;

use Ashrafic\AiOrbit\Models\PricingRule;
use Illuminate\Support\Collection;

class CostCalculator
{
    /**
     * Calculate cost for given model and token counts.
     *
     * @return array{input_cost: float, output_cost: float, total: float, currency: string}
     */
    public function calculate(string $model, int $inputTokens, int $outputTokens): array
    {
        $rule = PricingRule::where('model', $model)->first();

        if (! $rule) {
            $rule = PricingRule::where(function ($q) use ($model) {
                $q->where('model', $model)
                    ->orWhere('model', 'like', '%'.$model.'%');
            })->first();
        }

        if (! $rule) {
            return [
                'input_cost' => 0,
                'output_cost' => 0,
                'total' => 0,
                'currency' => config('ai-orbit.currency', 'USD'),
            ];
        }

        $inputCost = (float) $rule->input_cost_per_1m * ($inputTokens / 1_000_000);
        $outputCost = (float) $rule->output_cost_per_1m * ($outputTokens / 1_000_000);

        return [
            'input_cost' => round($inputCost, 6),
            'output_cost' => round($outputCost, 6),
            'total' => round($inputCost + $outputCost, 6),
            'currency' => $rule->currency,
        ];
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
                $model = $conv->model ?? 'gpt-4';
                $result = $this->calculate($model, $inputTokens, $outputTokens);
                $total += $result['total'];
            }
        }

        return round($total, 4);
    }
}

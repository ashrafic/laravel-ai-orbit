<?php

namespace Ashrafic\AiOrbit\Services;

use Ashrafic\AiOrbit\Models\BudgetAlert;
use Ashrafic\AiOrbit\Notifications\BudgetExceeded;
use Illuminate\Support\Facades\Notification;

class BudgetMonitor
{
    /**
     * Check all budget alerts against current spending.
     * Dispatches notifications via queue (non-blocking).
     */
    public function check(string $period = 'monthly'): void
    {
        $alerts = BudgetAlert::where('enabled', true)
            ->where('period', $period)
            ->get();

        if ($alerts->isEmpty()) {
            return;
        }

        $currentSpend = $this->getCurrentSpend($period);

        foreach ($alerts as $alert) {
            if ($currentSpend >= (float) $alert->threshold_amount) {
                if ($this->shouldNotify($alert)) {
                    Notification::route('mail', config('mail.from.address'))
                        ->notify(new BudgetExceeded($alert, $currentSpend));

                    $alert->update(['last_triggered_at' => now()]);
                }
            }
        }
    }

    /**
     * Get current spending for the given period.
     */
    public function getCurrentSpend(string $period = 'monthly'): float
    {
        // Aggregates cost from conversation data
        // In v1, this is a simplified calculation based on token counts
        $aggregator = app(TokenAggregator::class);
        $stats = $aggregator->todayStats($period === 'monthly' ? 'month' : '30d');
        $calculator = app(CostCalculator::class);

        $model = 'gpt-4';
        $cost = $calculator->calculate($model, $stats['input_tokens'], $stats['output_tokens']);

        return $cost['total'];
    }

    /**
     * Determine if a notification should be sent for this alert.
     */
    private function shouldNotify(BudgetAlert $alert): bool
    {
        if ($alert->last_triggered_at === null) {
            return true;
        }

        // Throttle: only notify once per period
        return match ($alert->period) {
            'daily' => $alert->last_triggered_at->isYesterday(),
            'weekly' => $alert->last_triggered_at->lt(now()->subWeek()),
            'monthly' => $alert->last_triggered_at->lt(now()->subMonth()),
            default => true,
        };
    }
}

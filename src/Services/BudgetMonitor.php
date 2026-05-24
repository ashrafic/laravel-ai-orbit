<?php

namespace Ashrafic\AiOrbit\Services;

use Ashrafic\AiOrbit\Models\AiRun;
use Ashrafic\AiOrbit\Models\BudgetAlert;
use Ashrafic\AiOrbit\Notifications\BudgetExceeded;
use Ashrafic\AiOrbit\Services\Concerns\UsesAiConnection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;

class BudgetMonitor
{
    use UsesAiConnection;

    public function __construct(
        private readonly CostCalculator $costCalculator,
    ) {}

    /**
     * Check all budget alerts against current spending.
     * Dispatches notifications via queue (non-blocking).
     */
    public function check(string $period = 'monthly', float $additionalSpend = 0.0, bool $additionalUnpricedUsage = false): void
    {
        if (! config('ai-orbit.budget.enabled', true)) {
            return;
        }

        $alerts = BudgetAlert::where('enabled', true)
            ->where('period', $period)
            ->get();

        if ($alerts->isEmpty()) {
            return;
        }

        $currentSpend = $this->getCurrentSpend($period) + $additionalSpend;
        $hasUnpricedUsage = $this->hasUnpricedUsage($period) || $additionalUnpricedUsage;

        foreach ($alerts as $alert) {
            if ($currentSpend >= (float) $alert->threshold_amount) {
                if ($this->shouldNotify($alert)) {
                    if (($alert->recipients ?? []) === []) {
                        continue;
                    }

                    foreach ($alert->recipients ?? [] as $recipient) {
                        Notification::route('mail', $recipient)
                            ->notify(new BudgetExceeded($alert, $currentSpend, $hasUnpricedUsage));
                    }

                    $alert->update(['last_triggered_at' => now()]);
                }
            }
        }
    }

    public function checkActivePeriods(): void
    {
        BudgetAlert::query()
            ->where('enabled', true)
            ->select('period')
            ->distinct()
            ->pluck('period')
            ->each(fn (string $period): null => $this->check($period));
    }

    public function checkCompletedEvent(object $event): void
    {
        if (! config('ai-orbit.budget.enabled', true)) {
            return;
        }

        $usage = $this->usageFor($event);
        $model = $this->modelFor($event);

        if (! $model || $usage === []) {
            return;
        }

        $cost = $this->costCalculator->calculate(
            $model,
            (int) ($usage['prompt_tokens'] ?? 0),
            (int) ($usage['completion_tokens'] ?? 0),
            $this->providerFor($event),
        );

        $shouldAddEventCost = ! config('ai-orbit.observability.store_runs', true)
            || ! Schema::hasTable('orbit_ai_runs');

        BudgetAlert::query()
            ->where('enabled', true)
            ->select('period')
            ->distinct()
            ->pluck('period')
            ->each(function (string $period) use ($cost, $shouldAddEventCost): void {
                $this->check(
                    $period,
                    $shouldAddEventCost ? $cost['total'] : 0.0,
                    $cost['missing_pricing'],
                );
            });
    }

    /**
     * Get current spending for the given period.
     */
    public function getCurrentSpend(string $period = 'monthly'): float
    {
        if ($this->hasRunData($period)) {
            return (float) $this->applyRunPeriod(AiRun::query(), $period)->sum('cost');
        }

        return $this->getConversationSpend($period);
    }

    public function hasUnpricedUsage(string $period = 'monthly'): bool
    {
        if (! Schema::hasTable('orbit_ai_runs')) {
            return false;
        }

        return $this->applyRunPeriod(AiRun::query(), $period)
            ->where('missing_pricing', true)
            ->exists();
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

    private function hasRunData(string $period): bool
    {
        return Schema::hasTable('orbit_ai_runs')
            && $this->applyRunPeriod(AiRun::query(), $period)->exists();
    }

    /**
     * @param  Builder<AiRun>  $query
     * @return Builder<AiRun>
     */
    private function applyRunPeriod($query, string $period)
    {
        $from = $this->periodStart($period);

        return $query->where('started_at', '>=', $from);
    }

    private function getConversationSpend(string $period): float
    {
        if (! $this->hasTable('agent_conversation_messages') || ! $this->hasColumn('agent_conversation_messages', 'usage')) {
            return 0.0;
        }

        $hasMeta = $this->hasColumn('agent_conversation_messages', 'meta');

        if (! $hasMeta) {
            return 0.0;
        }

        $jsonProvider = "REPLACE(JSON_EXTRACT(meta, '$.provider'), '\"', '')";
        $jsonModel = "REPLACE(JSON_EXTRACT(meta, '$.model'), '\"', '')";

        $rows = $this->connection()->table('agent_conversation_messages')
            ->where('created_at', '>=', $this->periodStart($period))
            ->selectRaw("{$jsonProvider} as provider")
            ->selectRaw("{$jsonModel} as model")
            ->selectRaw("COALESCE(SUM(JSON_EXTRACT(`usage`, '$.prompt_tokens')), 0) as input_tokens")
            ->selectRaw("COALESCE(SUM(JSON_EXTRACT(`usage`, '$.completion_tokens')), 0) as output_tokens")
            ->groupByRaw($jsonProvider)
            ->groupByRaw($jsonModel)
            ->get();

        return (float) $rows->sum(function (object $row): float {
            if (! $row->model) {
                return 0.0;
            }

            return $this->costCalculator->calculate(
                (string) $row->model,
                (int) $row->input_tokens,
                (int) $row->output_tokens,
                $row->provider ? (string) $row->provider : null,
            )['total'];
        });
    }

    private function periodStart(string $period): Carbon
    {
        return match ($period) {
            'daily' => today(),
            'weekly' => now()->subWeek(),
            default => now()->startOfMonth(),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function usageFor(object $event): array
    {
        if (! property_exists($event, 'response') || ! isset($event->response->usage)) {
            return [];
        }

        $usage = $event->response->usage;

        if (is_object($usage) && method_exists($usage, 'toArray')) {
            return $usage->toArray();
        }

        return [];
    }

    private function modelFor(object $event): ?string
    {
        if (property_exists($event, 'model')) {
            return $event->model;
        }

        if (property_exists($event, 'response') && isset($event->response->meta)) {
            return $event->response->meta->model;
        }

        if (property_exists($event, 'prompt') && isset($event->prompt->model)) {
            return $event->prompt->model;
        }

        return null;
    }

    private function providerFor(object $event): ?string
    {
        if (property_exists($event, 'provider')) {
            return $this->providerName($event->provider);
        }

        if (property_exists($event, 'response') && isset($event->response->meta)) {
            return $event->response->meta->provider;
        }

        if (property_exists($event, 'prompt') && isset($event->prompt->provider)) {
            return $this->providerName($event->prompt->provider);
        }

        return null;
    }

    private function providerName(mixed $provider): ?string
    {
        if (is_object($provider) && method_exists($provider, 'name')) {
            return $provider->name();
        }

        return is_string($provider) ? $provider : null;
    }
}

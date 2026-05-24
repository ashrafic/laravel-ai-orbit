<?php

namespace Ashrafic\AiOrbit\Http\Livewire;

use Ashrafic\AiOrbit\Services\CostCalculator;
use Ashrafic\AiOrbit\Services\TokenAggregator;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CostDashboard extends Component
{
    public string $period = '30d';

    public string $groupBy = 'model';

    public function render(): View
    {
        $aggregator = app(TokenAggregator::class);

        $stats = $aggregator->periodStats($this->period);
        $breakdown = $aggregator->agentBreakdown($this->period, $this->groupBy);

        $calculator = app(CostCalculator::class);
        $totalCost = $calculator->calculateForConversations($breakdown);

        $periods = [
            '7d' => 'Last 7 Days',
            '30d' => 'Last 30 Days',
            'month' => 'This Month',
            'all' => 'All Time',
        ];

        $groups = [
            'model' => 'By Model',
            'provider' => 'By Provider',
            'agent' => 'By Agent',
            'operation' => 'By Operation',
        ];

        return view('ai-orbit::usage.dashboard-livewire', [
            'stats' => $stats,
            'breakdown' => $breakdown,
            'totalCost' => $totalCost,
            'currencySymbol' => config('ai-orbit.currency_symbol', '$'),
            'periods' => $periods,
            'groups' => $groups,
        ]);
    }
}

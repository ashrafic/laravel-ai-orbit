<?php

namespace Ashrafic\AiOrbit\Http\Livewire;

use Ashrafic\AiOrbit\Services\TokenAggregator;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class TodayStats extends Component
{
    public function render(): View
    {
        $aggregator = app(TokenAggregator::class);

        return view('ai-orbit::livewire.today-stats', [
            'stats' => $aggregator->todayStats(),
            'breakdown' => $aggregator->agentBreakdown(),
        ]);
    }
}

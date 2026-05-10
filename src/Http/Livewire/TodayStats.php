<?php

namespace Ashraf\LaravelAiOrbit\Http\Livewire;

use Ashraf\LaravelAiOrbit\Services\TokenAggregator;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class TodayStats extends Component
{
    public function render(): View
    {
        $aggregator = app(TokenAggregator::class);

        return view('laravel-ai-orbit::livewire.today-stats', [
            'stats' => $aggregator->todayStats(),
            'breakdown' => $aggregator->agentBreakdown(),
        ]);
    }
}

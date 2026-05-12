<?php

namespace Ashrafic\AiOrbit\Http\Livewire;

use Ashrafic\AiOrbit\Services\ProviderHealthChecker;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ProviderHealth extends Component
{
    public string $period = '7d';

    public function render(): View
    {
        $checker = app(ProviderHealthChecker::class);
        $metrics = $checker->getHealthMetrics($this->period);

        $periods = [
            '24h' => 'Last 24 Hours',
            '7d' => 'Last 7 Days',
            '30d' => 'Last 30 Days',
        ];

        return view('ai-orbit::livewire.provider-health', [
            'metrics' => $metrics,
            'periods' => $periods,
        ]);
    }
}

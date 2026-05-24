<?php

namespace Ashrafic\AiOrbit\Http\Livewire;

use Ashrafic\AiOrbit\Contracts\AgentRegistryContract;
use Ashrafic\AiOrbit\Services\AgentHealthScorer;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class AgentInspector extends Component
{
    public string $agentClass;

    /** @var array<string, mixed>|null */
    public ?array $agentMeta = null;

    public ?string $overrideProvider = null;

    public ?string $overrideModel = null;

    public ?float $overrideTemperature = null;

    /** @var array<string, int> */
    public array $toolCallCounts = [];

    /** @var array{score: int, total_requests: int, error_rate: float, avg_tokens: int, status: string}|null */
    public ?array $healthScore = null;

    public function mount(string $agentClass): void
    {
        $this->agentClass = $agentClass;
        $this->agentMeta = app(AgentRegistryContract::class)->find($agentClass);

        if (isset($this->agentMeta['temperature'])) {
            $this->overrideTemperature = (float) $this->agentMeta['temperature'];
        }

        $this->healthScore = app(AgentHealthScorer::class)->score($agentClass);

        $this->dispatch('tool-counts-requested');
    }

    public function updatedOverrideProvider(): void
    {
        $this->dispatch('override-provider-updated', provider: $this->overrideProvider);
    }

    public function updatedOverrideModel(): void
    {
        $this->dispatch('override-model-updated', model: $this->overrideModel);
    }

    public function updatedOverrideTemperature(): void
    {
        $this->dispatch('override-temperature-updated', temperature: $this->overrideTemperature);
    }

    public function clearOverrides(): void
    {
        $this->overrideProvider = null;
        $this->overrideModel = null;
        $this->overrideTemperature = isset($this->agentMeta['temperature'])
            ? (float) $this->agentMeta['temperature']
            : null;
        $this->dispatch('overrides-cleared');
    }

    #[On('tool-call-counts-updated')]
    public function handleToolCallCounts(array $counts): void
    {
        $this->toolCallCounts = $counts;
    }

    /**
     * @return list<string>
     */
    public function getConfiguredProviders(): array
    {
        return array_map('strval', array_keys(config('ai.providers', [])));
    }

    public function render(): View
    {
        return view('ai-orbit::livewire.agent-inspector');
    }
}

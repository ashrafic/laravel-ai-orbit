<?php

namespace Ashrafic\AiOrbit\Http\Livewire;

use Ashrafic\AiOrbit\Contracts\AgentRegistryContract;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AgentInspector extends Component
{
    public string $agentClass;

    /** @var array<string, mixed>|null */
    public ?array $agentMeta = null;

    public function mount(string $agentClass): void
    {
        $this->agentClass = $agentClass;
        $this->agentMeta = app(AgentRegistryContract::class)->find($agentClass);
    }

    public function render(): View
    {
        return view('ai-orbit::livewire.agent-inspector');
    }
}

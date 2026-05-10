<?php

namespace Ashraf\Orbit\Http\Livewire;

use Ashraf\Orbit\Contracts\AgentRegistryContract;
use Illuminate\Contracts\View\View;
use Laravel\Ai\Contracts\Agent;
use Livewire\Component;

class AgentSandbox extends Component
{
    public string $agentClass;

    public string $prompt = '';

    /** @var array<string, mixed>|null */
    public ?array $agentMeta = null;

    /** @var array<int, array{role: string, content: string}> */
    public array $history = [];

    public bool $sending = false;

    public ?string $error = null;

    public function mount(string $agentClass): void
    {
        $this->agentClass = $agentClass;
        $this->agentMeta = app(AgentRegistryContract::class)->find($agentClass);
    }

    public function send(): void
    {
        $this->validate(['prompt' => 'required|string|min:1']);

        $this->sending = true;
        $this->error = null;

        $userPrompt = $this->prompt;
        $this->history[] = ['role' => 'user', 'content' => $userPrompt];
        $this->prompt = '';

        try {
            if (! class_exists($this->agentClass)) {
                throw new \RuntimeException("Agent class [{$this->agentClass}] not found.");
            }

            /** @var Agent $agent */
            $agent = app($this->agentClass);
            $response = $agent->prompt($userPrompt);

            $this->history[] = [
                'role' => 'assistant',
                'content' => (string) $response,
            ];
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
            $this->history[] = [
                'role' => 'error',
                'content' => 'Error: '.$e->getMessage(),
            ];
        }

        $this->sending = false;
    }

    public function clear(): void
    {
        $this->history = [];
        $this->error = null;
        $this->prompt = '';
    }

    public function render(): View
    {
        return view('orbit::livewire.agent-sandbox');
    }
}

<?php

namespace Ashrafic\AiOrbit\Http\Livewire;

use Ashrafic\AiOrbit\Contracts\AgentRegistryContract;
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

    public bool $proMode = true;

    public ?string $overrideSystemPrompt = null;

    public ?string $overrideModel = null;

    public ?string $overrideProvider = null;

    public ?float $overrideTemperature = null;

    public ?int $overrideMaxTokens = null;

    public bool $debuggerEnabled = false;

    public array $pendingToolCalls = [];

    public ?string $contextJson = null;

    public array $forkPoints = [];

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
            $this->error = 'Agent execution failed: '.$e->getMessage();
            $this->history[] = [
                'role' => 'error',
                'content' => 'Agent execution failed: '.$e->getMessage(),
            ];
        } finally {
            $this->sending = false;
        }
    }

    public function clear(): void
    {
        $this->history = [];
        $this->error = null;
        $this->prompt = '';
    }

    public function applyOverrides(): void
    {
        $this->dispatch('overrides-applied', overrides: [
            'system_prompt' => $this->overrideSystemPrompt,
            'model' => $this->overrideModel,
            'provider' => $this->overrideProvider,
            'temperature' => $this->overrideTemperature,
            'max_tokens' => $this->overrideMaxTokens,
        ]);
    }

    public function clearOverrides(): void
    {
        $this->overrideSystemPrompt = null;
        $this->overrideModel = null;
        $this->overrideProvider = null;
        $this->overrideTemperature = null;
        $this->overrideMaxTokens = null;
    }

    public function toggleDebugger(): void
    {
        $this->debuggerEnabled = ! $this->debuggerEnabled;
    }

    public function approveToolCall(string $toolCallId): void
    {
        unset($this->pendingToolCalls[$toolCallId]);
    }

    public function editToolCall(string $toolCallId, array $editedArgs): void
    {
        $this->pendingToolCalls[$toolCallId]['args'] = $editedArgs;
    }

    public function mockToolResponse(string $toolCallId, string $mockResponse): void
    {
        $this->pendingToolCalls[$toolCallId]['mock'] = $mockResponse;
    }

    public function injectContextJson(string $json): void
    {
        $this->contextJson = $json;
    }

    public function forkConversation(int $messageIndex): void
    {
        $this->forkPoints[] = $messageIndex;
    }

    public function render(): View
    {
        return view('ai-orbit::livewire.agent-sandbox');
    }
}

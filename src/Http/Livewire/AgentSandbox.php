<?php

namespace Ashrafic\AiOrbit\Http\Livewire;

use Ashrafic\AiOrbit\Contracts\AgentRegistryContract;
use Ashrafic\AiOrbit\Services\AgentIntrospector;
use Ashrafic\AiOrbit\Services\SandboxRunner;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AgentSandbox extends Component
{
    public string $agentClass;

    public string $prompt = '';

    /** @var array<string, mixed>|null */
    public ?array $agentMeta = null;

    /** @var array<int, array<string, mixed>> */
    public array $history = [];

    public bool $sending = false;

    public ?string $error = null;

    public ?string $overrideSystemPrompt = null;

    public ?string $overrideModel = null;

    public ?string $overrideProvider = null;

    public ?float $overrideTemperature = null;

    public ?int $overrideMaxTokens = null;

    public array $constructorParams = [];

    public array $paramInputs = [];

    public string $simulationMode = 'pending';

    public bool $needsInput = false;

    public ?string $sdkConversationId = null;

    public int $sandboxSessionId = 0;

    public function mount(string $agentClass): void
    {
        $this->agentClass = $agentClass;
        $this->agentMeta = app(AgentRegistryContract::class)->find($agentClass);
        $this->sandboxSessionId = 0;

        $introspector = app(AgentIntrospector::class);
        $analysis = $introspector->analyzeConstructor($agentClass);

        $this->constructorParams = $analysis['params'];
        $this->needsInput = $analysis['needs_input'];
        $this->simulationMode = $analysis['needs_input'] ? 'pending' : 'ready';

        if (! $analysis['resolvable']) {
            $this->simulationMode = 'prompt_only';
        }

        $this->restoreSession();
    }

    public function updatedParamInputs(): void
    {
        if ($this->simulationMode !== 'pending') {
            return;
        }

        if ($this->allRequiredInputsProvided()) {
            $this->simulationMode = 'ready';
        }
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
            $runner = app(SandboxRunner::class);
            $participant = (object) ['id' => $this->sandboxSessionId];

            $result = $runner->execute(
                agentClass: $this->agentClass,
                prompt: $userPrompt,
                paramInputs: $this->paramInputs,
                overrides: array_filter([
                    'model' => $this->overrideModel,
                    'provider' => $this->overrideProvider,
                ]),
                sdkConversationId: $this->sdkConversationId,
                participant: $participant,
            );

            $this->sdkConversationId = $result->sdkConversationId;
            $this->simulationMode = $result->mode;

            $this->persistSession();

            if ($result->warning) {
                $this->history[] = [
                    'role' => 'warning',
                    'content' => $result->warning,
                ];
            }

            foreach ($result->toolCalls as $toolCall) {
                $this->history[] = [
                    'role' => 'tool_call',
                    'content' => $toolCall['name'],
                    'arguments' => is_array($toolCall['arguments']) ? json_encode($toolCall['arguments'], JSON_PRETTY_PRINT) : (string) $toolCall['arguments'],
                ];
            }

            foreach ($result->toolResults as $toolResult) {
                $this->history[] = [
                    'role' => 'tool_result',
                    'content' => $toolResult['content'],
                    'name' => $toolResult['name'] ?? '',
                ];
            }

            $this->history[] = [
                'role' => 'assistant',
                'content' => $result->content,
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
        if ($this->sdkConversationId) {
            try {
                DB::table('agent_conversation_messages')
                    ->where('conversation_id', $this->sdkConversationId)->delete();
                DB::table('agent_conversations')
                    ->where('id', $this->sdkConversationId)->delete();
            } catch (\Throwable) {
                // Tables may not exist if SDK migrations haven't run
            }
        }

        $this->sdkConversationId = null;
        $this->history = [];
        $this->error = null;
        $this->prompt = '';
        $this->sandboxSessionId = 0;

        $this->forgetSession();
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

    /**
     * @return Collection<int, Model>
     */
    public function getModelRecords(string $modelClass): Collection
    {
        return app(AgentIntrospector::class)->getModelRecords(
            $modelClass,
            (int) config('ai-orbit.sandbox.records_per_picker', 20)
        );
    }

    /**
     * @return array<int, string>
     */
    public function getDisplayValues(Model $record): array
    {
        return app(AgentIntrospector::class)->getDisplayValues($record);
    }

    public function render(): View
    {
        return view('ai-orbit::livewire.agent-sandbox');
    }

    private function persistSession(): void
    {
        if ($this->sdkConversationId) {
            session()->put(
                $this->sessionKey(),
                $this->sdkConversationId
            );
        }
    }

    private function restoreSession(): void
    {
        $conversationId = session()->get($this->sessionKey());

        if ($conversationId !== null) {
            $this->sdkConversationId = $conversationId;
            $this->simulationMode = 'full';
        }
    }

    private function forgetSession(): void
    {
        session()->forget($this->sessionKey());
    }

    private function sessionKey(): string
    {
        return 'orbit-sandbox-conversation.'.md5($this->agentClass);
    }

    private function allRequiredInputsProvided(): bool
    {
        foreach ($this->constructorParams as $param) {
            if (! in_array($param['strategy'], ['eloquent_picker', 'input'], true)) {
                continue;
            }

            if (empty($this->paramInputs[$param['name']] ?? null) && ! isset($param['default'])) {
                return false;
            }
        }

        return true;
    }
}

<?php

namespace Ashrafic\AiOrbit\Services;

use Ashrafic\AiOrbit\Support\SandboxResult;
use Illuminate\Support\Facades\App;
use Laravel\Ai\Contracts\Agent;

class SandboxRunner
{
    public function __construct(private AgentIntrospector $introspector) {}

    /**
     * Execute the agent in the sandbox, routing to the appropriate tier.
     *
     * @param  array<string, mixed>  $paramInputs
     * @param  array{model?: string, provider?: string}  $overrides
     */
    public function execute(
        string $agentClass,
        string $prompt,
        array $paramInputs = [],
        array $overrides = [],
        ?string $sdkConversationId = null,
        ?object $participant = null,
    ): SandboxResult {
        $analysis = $this->introspector->analyzeConstructor($agentClass);

        if (! $analysis['resolvable']) {
            throw new \RuntimeException(
                "Agent [{$agentClass}] has unresolvable constructor dependencies and cannot be simulated. ".
                'Ensure all constructor parameters are either container-resolvable, Eloquent models, or scalar types.'
            );
        }

        if (! $this->allRequiredInputsProvided($analysis['params'], $paramInputs)) {
            throw new \RuntimeException(
                'Required constructor parameters have not been provided. Fill in all required inputs before sending.'
            );
        }

        return $this->executeFullAgent(
            $agentClass,
            $prompt,
            $paramInputs,
            $overrides,
            $sdkConversationId,
            $participant,
        );
    }

    /**
     * Execute the real agent with fully resolved dependencies.
     */
    private function executeFullAgent(
        string $agentClass,
        string $prompt,
        array $paramInputs,
        array $overrides,
        ?string $sdkConversationId,
        ?object $participant,
    ): SandboxResult {
        $resolved = $this->introspector->resolveParams($agentClass, $paramInputs);
        /** @var object $agent */
        $agent = App::makeWith($agentClass, $resolved);

        if (! ($agent instanceof Agent)) {
            throw new \RuntimeException("Agent class [{$agentClass}] does not implement Agent interface.");
        }

        $provider = $overrides['provider'] ?? null;
        $model = $overrides['model'] ?? null;

        if ($participant !== null) {
            if (
                method_exists($agent, 'forUser')
                && method_exists($agent, 'currentConversation')
                && method_exists($agent, 'hasConversationParticipant')
            ) {
                if ($sdkConversationId !== null) {
                    /* @phpstan-ignore-next-line method.notFound */
                    $agent->continue($sdkConversationId, as: $participant);
                } else {
                    $agent->forUser($participant);
                }
            }
        }

        $response = $agent->prompt(
            $prompt,
            provider: $provider,
            model: $model,
        );

        $conversationId = null;
        if (method_exists($agent, 'currentConversation')) {
            $conversationId = $agent->currentConversation();
        }

        [$toolCalls, $toolResults] = $this->extractToolCalls($response);

        return new SandboxResult(
            content: (string) $response,
            mode: 'full',
            sdkConversationId: $conversationId,
            metadata: [
                'model' => $model,
                'provider' => $provider,
            ],
            toolCalls: $toolCalls,
            toolResults: $toolResults,
        );
    }

    /**
     * Extract tool calls and results from an agent response.
     *
     * @return array{array<int, array>, array<int, array>}
     */
    private function extractToolCalls(mixed $response): array
    {
        $toolCalls = [];
        $toolResults = [];

        if (property_exists($response, 'toolCalls') && $response->toolCalls !== null) {
            foreach ($response->toolCalls as $toolCall) {
                $toolCalls[] = [
                    'name' => $toolCall->name ?? $toolCall->function?->name ?? 'unknown',
                    'arguments' => $toolCall->arguments ?? $toolCall->function?->arguments ?? $toolCall->input ?? [],
                ];
            }
        }

        if (property_exists($response, 'toolResults') && $response->toolResults !== null) {
            foreach ($response->toolResults as $toolResult) {
                $toolResults[] = [
                    'name' => $toolResult->name ?? '',
                    'content' => is_string($toolResult) ? $toolResult : ($toolResult->result ?? json_encode($toolResult->toArray())),
                ];
            }
        }

        return [$toolCalls, $toolResults];
    }

    /**
     * Check if all required user inputs have been provided.
     *
     * @param  array<int, array>  $params
     * @param  array<string, mixed>  $inputs
     */
    private function allRequiredInputsProvided(array $params, array $inputs): bool
    {
        foreach ($params as $param) {
            if (! in_array($param['strategy'], ['eloquent_picker', 'input'], true)) {
                continue;
            }

            if (empty($inputs[$param['name']] ?? null) && ! isset($param['default'])) {
                return false;
            }
        }

        return true;
    }
}

<?php

namespace Ashrafic\AiOrbit\Support;

/**
 * Parses the SDK 1.0 "steps" column on conversation messages.
 *
 * Each assistant message stores one step per model round trip. Tool calls
 * carry their own result; a call with an approval reason and no result is
 * still pending a human decision.
 */
class StepParser
{
    /**
     * Flatten the steps JSON of a message into a list of tool calls with
     * their resolved state.
     *
     * @return list<array{id: ?string, name: string, arguments: array<string, mixed>, result: mixed, pending: bool, denied: bool, failed: bool, approval_reason: ?string, step: int}>
     */
    public static function toolCalls(?string $steps): array
    {
        $decoded = is_array($parsed = json_decode((string) $steps, true)) ? $parsed : [];

        $calls = [];

        foreach (array_values($decoded) as $index => $step) {
            if (! is_array($step) || ! isset($step['tool_calls']) || ! is_array($step['tool_calls'])) {
                continue;
            }

            foreach ($step['tool_calls'] as $call) {
                if (! is_array($call)) {
                    continue;
                }

                $pending = array_key_exists('approval_reason', $call)
                    && $call['approval_reason'] !== null
                    && ! array_key_exists('result', $call);

                $calls[] = [
                    'id' => isset($call['id']) && is_string($call['id']) ? $call['id'] : null,
                    'name' => (string) ($call['name'] ?? $call['function']['name'] ?? 'unknown'),
                    'arguments' => is_array($call['arguments'] ?? null) ? $call['arguments'] : [],
                    'result' => $call['result'] ?? null,
                    'pending' => $pending,
                    'denied' => (bool) ($call['denied'] ?? false),
                    'failed' => (bool) ($call['failed'] ?? false),
                    'approval_reason' => $call['approval_reason'] ?? null,
                    'step' => $index + 1,
                ];
            }
        }

        return $calls;
    }
}

<?php

namespace Ashrafic\AiOrbit\Services;

use Ashrafic\AiOrbit\Models\AiRun;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use JsonSerializable;
use Laravel\Ai\Events\AgentFailedOver;
use Laravel\Ai\Events\AgentPrompted;
use Laravel\Ai\Events\AgentStreamed;
use Laravel\Ai\Events\InvokingTool;
use Laravel\Ai\Events\ProviderFailedOver;
use Laravel\Ai\Events\ToolInvoked;
use Throwable;

class AiRunRecorder
{
    public function __construct(
        private readonly CostCalculator $costCalculator,
    ) {}

    public function recordStarting(object $event, string $operation): void
    {
        if (! $this->shouldRecord($operation)) {
            return;
        }

        $this->upsertRun($event, [
            'operation' => $operation,
            'status' => 'running',
            'started_at' => now(),
            'payload' => $this->payloadFor($event, false),
        ]);
    }

    public function recordCompleted(object $event, string $operation): void
    {
        if (! $this->shouldRecord($operation)) {
            return;
        }

        $usage = $this->usageFor($event);
        $model = $this->modelFor($event);
        $provider = $this->providerFor($event);
        $promptTokens = (int) ($usage['prompt_tokens'] ?? $usage['input_tokens'] ?? $usage['promptTokens'] ?? $usage['inputTokens'] ?? 0);
        $completionTokens = (int) ($usage['completion_tokens'] ?? $usage['output_tokens'] ?? $usage['completionTokens'] ?? $usage['outputTokens'] ?? 0);

        $cost = $model ? $this->costCalculator->calculate(
            $model,
            $promptTokens,
            $completionTokens,
            $provider,
        ) : ['total' => 0, 'priced' => false, 'missing_pricing' => false];

        $startedAt = $this->existingStartedAt($this->invocationIdFor($event));
        $completedAt = now();

        $this->upsertRun($event, [
            'operation' => $operation,
            'status' => 'completed',
            'input_tokens' => $promptTokens,
            'output_tokens' => $completionTokens,
            'usage' => $usage,
            'cost' => $cost['total'],
            'priced' => $cost['priced'],
            'missing_pricing' => $cost['missing_pricing'],
            'payload' => $this->payloadFor($event, true),
            'conversation_id' => $this->conversationIdFor($event),
            'user_id' => $this->userIdFor($event),
            'latency_ms' => $startedAt ? (int) $startedAt->diffInMilliseconds($completedAt) : null,
            'completed_at' => $completedAt,
        ]);
    }

    public function recordToolEvent(InvokingTool|ToolInvoked $event): void
    {
        if (! $this->shouldRecord('tool')) {
            return;
        }

        $entry = [
            'type' => $event instanceof InvokingTool ? 'tool_invoking' : 'tool_invoked',
            'tool_invocation_id' => $event->toolInvocationId,
            'tool' => $event->tool::class,
            'arguments' => $this->summarize($event->arguments),
            'recorded_at' => now()->toISOString(),
        ];

        if ($event instanceof ToolInvoked) {
            $entry['result'] = $this->summarize($event->result);
        }

        $run = AiRun::query()->where('invocation_id', $event->invocationId)->first();

        if (! $run) {
            $this->upsertRun($event, [
                'operation' => 'agent_text',
                'status' => 'running',
                'agent_class' => $event->agent::class,
                'started_at' => now(),
                'events' => [$entry],
            ]);

            return;
        }

        $events = $run->events ?? [];
        $events[] = $entry;
        $run->update(['events' => $events]);
    }

    public function recordFailover(ProviderFailedOver $event): void
    {
        if (! $this->shouldRecord('failover') || ! Schema::hasTable('orbit_ai_runs')) {
            return;
        }

        $agent = $event instanceof AgentFailedOver ? $event->agent::class : null;
        $payload = [
            'type' => 'failover',
            'provider' => $this->providerName($event->provider ?? null),
            'model' => $event->model ?? null,
            'agent_class' => $agent,
            'error' => $event->exception instanceof Throwable
                ? $event->exception->getMessage()
                : $event->exception::class,
            'recorded_at' => now()->toISOString(),
        ];

        $run = property_exists($event, 'invocationId')
            ? AiRun::query()->where('invocation_id', $event->invocationId)->first()
            : null;

        if (! $run) {
            AiRun::query()->create([
                'operation' => 'failover',
                'status' => 'failed',
                'provider' => $payload['provider'],
                'model' => $payload['model'],
                'agent_class' => $agent,
                'events' => [$payload],
                'error' => $payload['error'],
                'started_at' => now(),
                'completed_at' => now(),
            ]);

            return;
        }

        $events = $run->events ?? [];
        $events[] = $payload;
        $run->update(['events' => $events]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function upsertRun(object $event, array $attributes): void
    {
        if (! Schema::hasTable('orbit_ai_runs')) {
            return;
        }

        $invocationId = $this->invocationIdFor($event);

        $base = array_filter([
            'invocation_id' => $invocationId,
            'provider' => $this->providerFor($event),
            'model' => $this->modelFor($event),
            'agent_class' => $this->agentClassFor($event),
            'user_id' => $this->userIdFor($event),
            'conversation_id' => $this->conversationIdFor($event),
        ], fn ($value) => $value !== null);

        if ($invocationId) {
            AiRun::query()->updateOrCreate(
                ['invocation_id' => $invocationId],
                array_merge($base, $attributes)
            );

            return;
        }

        AiRun::query()->create(array_merge($base, $attributes));
    }

    private function shouldRecord(string $operation): bool
    {
        if (! config('ai-orbit.observability.enabled', true)) {
            return false;
        }

        if (! config('ai-orbit.observability.store_runs', true)) {
            return false;
        }

        return ! in_array($operation, config('ai-orbit.observability.excluded_operations', []), true);
    }

    private function invocationIdFor(object $event): ?string
    {
        return property_exists($event, 'invocationId') ? $event->invocationId : null;
    }

    private function providerFor(object $event): ?string
    {
        if (property_exists($event, 'provider')) {
            return $this->providerName($event->provider);
        }

        if (property_exists($event, 'response') && isset($event->response->meta)) {
            return $event->response->meta->provider;
        }

        if (property_exists($event, 'prompt') && isset($event->prompt->provider)) {
            return $this->providerName($event->prompt->provider);
        }

        return null;
    }

    private function modelFor(object $event): ?string
    {
        if (property_exists($event, 'model')) {
            return $event->model;
        }

        if (property_exists($event, 'response') && isset($event->response->meta)) {
            return $event->response->meta->model;
        }

        if (property_exists($event, 'prompt') && isset($event->prompt->model)) {
            return $event->prompt->model;
        }

        return null;
    }

    private function agentClassFor(object $event): ?string
    {
        if (property_exists($event, 'agent')) {
            return $event->agent::class;
        }

        if (property_exists($event, 'prompt') && isset($event->prompt->agent)) {
            return $event->prompt->agent::class;
        }

        return null;
    }

    private function conversationIdFor(object $event): ?string
    {
        if (property_exists($event, 'response') && isset($event->response->conversationId)) {
            return $event->response->conversationId;
        }

        return null;
    }

    private function userIdFor(object $event): ?string
    {
        if (! property_exists($event, 'response') || ! isset($event->response->conversationUser)) {
            return auth()->id() ? (string) auth()->id() : null;
        }

        $user = $event->response->conversationUser;

        if (is_object($user) && method_exists($user, 'getAuthIdentifier')) {
            return (string) $user->getAuthIdentifier();
        }

        if (is_object($user) && isset($user->id)) {
            return (string) $user->id;
        }

        return auth()->id() ? (string) auth()->id() : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function usageFor(object $event): array
    {
        if (! property_exists($event, 'response') || ! isset($event->response->usage)) {
            return [];
        }

        return $this->toArray($event->response->usage);
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFor(object $event, bool $completed): array
    {
        $captureText = config('ai-orbit.observability.capture_text_payloads', true);
        $payload = [
            'metadata_only' => ! $captureText,
        ];

        if (property_exists($event, 'prompt')) {
            $payload['prompt'] = $captureText
                ? $this->truncate((string) ($event->prompt->prompt ?? ''))
                : null;
            $payload['attachments_count'] = isset($event->prompt->attachments)
                ? $event->prompt->attachments->count()
                : null;
        }

        if ($completed && $captureText && $event instanceof AgentPrompted) {
            $payload['response'] = $this->truncate((string) $event->response->text);
            $payload['streamed'] = $event instanceof AgentStreamed;
        }

        return array_filter($payload, fn ($value) => $value !== null);
    }

    private function existingStartedAt(?string $invocationId): ?Carbon
    {
        if (! $invocationId) {
            return null;
        }

        $run = AiRun::query()->where('invocation_id', $invocationId)->first();

        return $run?->started_at;
    }

    private function providerName(mixed $provider): ?string
    {
        if (is_object($provider) && method_exists($provider, 'name')) {
            return $provider->name();
        }

        return is_string($provider) ? $provider : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function toArray(mixed $value): array
    {
        if ($value instanceof Arrayable) {
            return $value->toArray();
        }

        if ($value instanceof JsonSerializable) {
            $json = $value->jsonSerialize();

            return is_array($json) ? $json : ['value' => $json];
        }

        return is_array($value) ? $value : [];
    }

    private function summarize(mixed $value): mixed
    {
        try {
            return json_decode(json_encode($value, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return is_object($value) ? $value::class : gettype($value);
        }
    }

    private function truncate(string $value): string
    {
        return mb_substr($value, 0, (int) config('ai-orbit.observability.max_payload_length', 10000));
    }
}

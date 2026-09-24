<?php

namespace Ashrafic\AiOrbit\Services;

use Ashrafic\AiOrbit\Models\AiRun;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use JsonSerializable;
use Laravel\Ai\Events\AgentFailed;
use Laravel\Ai\Events\AgentFailedOver;
use Laravel\Ai\Events\AgentPrompted;
use Laravel\Ai\Events\AgentStreamed;
use Laravel\Ai\Events\InvokingTool;
use Laravel\Ai\Events\ProviderFailedOver;
use Laravel\Ai\Events\StartingStep;
use Laravel\Ai\Events\StepCompleted;
use Laravel\Ai\Events\StepFailed;
use Laravel\Ai\Events\ToolApprovalRequested;
use Laravel\Ai\Events\ToolApprovalResolved;
use Laravel\Ai\Events\ToolFailed;
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
            $entry['time_ms'] = $event->time;
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
            'error' => $event->exception->getMessage(),
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
     * Record terminal agent failures from the SDK's failure events.
     */
    public function recordFailed(AgentFailed|StepFailed $event): void
    {
        if (! $this->shouldRecord('agent_text') || ! Schema::hasTable('orbit_ai_runs')) {
            return;
        }

        $isFinal = $event instanceof AgentFailed || $event->isFinalStep;

        $entry = $event instanceof StepFailed ? [
            'type' => 'step_failed',
            'step_number' => $event->stepNumber,
            'is_final' => $event->isFinalStep,
            'error' => $this->summarize($event->exception),
            'time_ms' => $event->time,
            'recorded_at' => now()->toISOString(),
        ] : null;

        $run = AiRun::query()->where('invocation_id', $event->invocationId)->first();

        if ($run) {
            $attributes = array_filter([
                'status' => $isFinal ? 'failed' : null,
                'error' => $isFinal ? $event->exception->getMessage() : null,
                'completed_at' => $isFinal ? now() : null,
                'latency_ms' => $isFinal && $run->started_at
                    ? (int) $run->started_at->diffInMilliseconds(now())
                    : null,
            ]);

            if ($entry) {
                $events = $run->events ?? [];
                $events[] = $entry;
                $attributes['events'] = $events;
            }

            $run->update($attributes);

            return;
        }

        if (! $isFinal) {
            return;
        }

        AiRun::query()->create([
            'invocation_id' => $event->invocationId,
            'operation' => 'agent_text',
            'status' => 'failed',
            'provider' => $this->providerFor($event),
            'model' => $this->modelFor($event),
            'agent_class' => $this->agentClassFor($event),
            ...$this->participantFor($event),
            'error' => $event->exception->getMessage(),
            'payload' => $this->payloadFor($event, false),
            'started_at' => now(),
            'completed_at' => now(),
        ]);
    }

    /**
     * Record tool failures from the SDK's tool failure events.
     */
    public function recordToolFailed(ToolFailed $event): void
    {
        if (! $this->shouldRecord('tool') || ! Schema::hasTable('orbit_ai_runs')) {
            return;
        }

        $entry = [
            'type' => 'tool_failed',
            'tool_invocation_id' => $event->toolInvocationId,
            'tool' => $event->tool::class,
            'arguments' => $this->summarize($event->arguments),
            'error' => $this->summarize($event->exception),
            'time_ms' => $event->time,
            'recorded_at' => now()->toISOString(),
        ];

        $run = AiRun::query()->where('invocation_id', $event->invocationId)->first();

        if (! $run) {
            $this->upsertRun($event, [
                'operation' => 'agent_text',
                'status' => 'running',
                'started_at' => now(),
                'events' => [$entry],
            ]);

            return;
        }

        $events = $run->events ?? [];
        $events[] = $entry;
        $run->update(['events' => $events]);
    }

    /**
     * Record a generation step starting (SDK 0.11+ step reporting).
     */
    public function recordStepStarting(StartingStep $event): void
    {
        if (! $this->shouldRecord('agent_text') || ! Schema::hasTable('orbit_ai_runs')) {
            return;
        }

        $this->appendRunEvent($event->invocationId, $event, [
            'type' => 'step_started',
            'step_number' => $event->stepNumber,
            'is_final' => $event->isFinalStep,
            'provider' => $this->providerName($event->provider),
            'model' => $event->model,
            'recorded_at' => now()->toISOString(),
        ]);
    }

    /**
     * Record a generation step completing with its wall time (SDK 0.11+).
     */
    public function recordStepCompleted(StepCompleted $event): void
    {
        if (! $this->shouldRecord('agent_text') || ! Schema::hasTable('orbit_ai_runs')) {
            return;
        }

        $this->appendRunEvent($event->invocationId, $event, [
            'type' => 'step_completed',
            'step_number' => $event->stepNumber,
            'is_final' => $event->isFinalStep,
            'model' => $event->model,
            'time_ms' => $event->time,
            'recorded_at' => now()->toISOString(),
        ]);
    }

    /**
     * Record a run pausing for human-in-the-loop tool approval (SDK 0.11+).
     */
    public function recordApprovalRequested(ToolApprovalRequested $event): void
    {
        if (! $this->shouldRecord('agent_text') || ! Schema::hasTable('orbit_ai_runs')) {
            return;
        }

        $entry = [
            'type' => 'approval_requested',
            'approvals' => $event->pendingApprovals
                ->map(fn ($approval) => array_filter([
                    'id' => $approval->id,
                    'tool' => $approval->tool,
                    'reason' => $approval->reason,
                ]))
                ->all(),
            'recorded_at' => now()->toISOString(),
        ];

        $run = AiRun::query()->where('invocation_id', $event->invocationId)->first();

        if ($run) {
            $events = $run->events ?? [];
            $events[] = $entry;
            $run->update(['events' => $events, 'status' => 'pending_approval']);

            return;
        }

        $this->upsertRun($event, [
            'operation' => 'agent_text',
            'status' => 'pending_approval',
            'conversation_id' => $event->conversationId,
            ...$this->participantFor($event),
            'started_at' => now(),
            'events' => [$entry],
        ]);
    }

    /**
     * Record a run resuming after tool approval (SDK 0.11+).
     */
    public function recordApprovalResolved(ToolApprovalResolved $event): void
    {
        if (! $this->shouldRecord('agent_text') || ! Schema::hasTable('orbit_ai_runs')) {
            return;
        }

        $this->appendRunEvent($event->invocationId, $event, [
            'type' => 'approval_resolved',
            'tool_results' => $event->toolResults->count(),
            'recorded_at' => now()->toISOString(),
        ], ['status' => 'running']);
    }

    /**
     * Append an entry to a run's event trace, creating a minimal run when none exists.
     *
     * @param  array<string, mixed>  $entry
     * @param  array<string, mixed>  $attributes
     */
    private function appendRunEvent(string $invocationId, object $event, array $entry, array $attributes = []): void
    {
        $run = AiRun::query()->where('invocation_id', $invocationId)->first();

        if ($run) {
            $events = $run->events ?? [];
            $events[] = $entry;
            $run->update(array_filter(array_merge(['events' => $events], $attributes), fn ($value) => $value !== null));

            return;
        }

        $this->upsertRun($event, array_merge([
            'operation' => 'agent_text',
            'status' => 'running',
            'started_at' => now(),
            'events' => [$entry],
        ], $attributes));
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
            'conversation_id' => $this->conversationIdFor($event),
            ...$this->participantFor($event),
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

    /**
     * Resolve the run's participant following the SDK's own resolution:
     * the conversation participant object when present, the authenticated
     * principal otherwise.
     *
     * @return array{participant_type?: string, participant_id?: int}
     */
    private function participantFor(object $event): array
    {
        $participant = null;

        if (property_exists($event, 'response') && isset($event->response->conversationUser)) {
            $participant = $event->response->conversationUser;
        } elseif (property_exists($event, 'conversationUser') && $event->conversationUser !== null) {
            $participant = $event->conversationUser;
        }

        if ($participant === null) {
            $participant = auth()->user();
        }

        if ($participant === null || ! is_object($participant)) {
            return [];
        }

        $id = $participant instanceof Model
            ? $participant->getKey()
            : ($participant->id ?? null);

        return array_filter([
            'participant_type' => $participant instanceof Model
                ? $participant->getMorphClass()
                : $participant::class,
            'participant_id' => is_numeric($id) ? (int) $id : null,
        ]);
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

    private function existingStartedAt(?string $invocationId): ?CarbonInterface
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

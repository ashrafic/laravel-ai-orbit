<?php

namespace Ashrafic\AiOrbit\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $invocation_id
 * @property string $operation
 * @property string $status
 * @property string|null $provider
 * @property string|null $model
 * @property string|null $agent_class
 * @property string|null $user_id
 * @property string|null $conversation_id
 * @property int $input_tokens
 * @property int $output_tokens
 * @property string $cost
 * @property bool $priced
 * @property bool $missing_pricing
 * @property int|null $latency_ms
 * @property array<string, mixed>|null $payload
 * @property array<string, mixed>|null $usage
 * @property array<int, array<string, mixed>>|null $events
 * @property string|null $error
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 */
class AiRun extends Model
{
    protected $table = 'orbit_ai_runs';

    protected $fillable = [
        'invocation_id',
        'operation',
        'status',
        'provider',
        'model',
        'agent_class',
        'user_id',
        'conversation_id',
        'input_tokens',
        'output_tokens',
        'cost',
        'priced',
        'missing_pricing',
        'latency_ms',
        'payload',
        'usage',
        'events',
        'error',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
        'cost' => 'decimal:6',
        'priced' => 'boolean',
        'missing_pricing' => 'boolean',
        'latency_ms' => 'integer',
        'payload' => 'array',
        'usage' => 'array',
        'events' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}

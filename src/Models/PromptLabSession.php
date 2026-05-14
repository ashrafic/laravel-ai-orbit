<?php

namespace Ashrafic\AiOrbit\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $prompt
 * @property string|null $system_prompt
 * @property float $temperature
 * @property int|null $max_tokens
 * @property float $top_p
 * @property array|null $context
 * @property array $slots
 * @property array|null $results
 * @property array|null $tags
 * @property string|null $user_id
 * @property string|null $total_cost
 * @property int|null $total_latency_ms
 * @property string $status
 */
class PromptLabSession extends Model
{
    protected $table = 'orbit_prompt_lab_sessions';

    protected $fillable = [
        'prompt',
        'system_prompt',
        'temperature',
        'max_tokens',
        'top_p',
        'context',
        'slots',
        'results',
        'tags',
        'user_id',
        'total_cost',
        'total_latency_ms',
        'status',
    ];

    protected $casts = [
        'slots' => 'json',
        'results' => 'json',
        'tags' => 'json',
        'context' => 'json',
        'temperature' => 'float',
        'top_p' => 'float',
        'max_tokens' => 'integer',
        'total_cost' => 'decimal:8',
        'total_latency_ms' => 'integer',
    ];
}

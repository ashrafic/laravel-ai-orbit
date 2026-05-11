<?php

namespace Ashrafic\AiOrbit\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $prompt
 * @property array $models
 * @property array|null $results
 * @property array|null $tags
 * @property string|null $user_id
 * @property string|null $total_cost
 * @property int|null $total_latency_ms
 * @property string $status
 */
class ArenaSession extends Model
{
    protected $table = 'orbit_arena_sessions';

    protected $fillable = [
        'prompt',
        'models',
        'results',
        'tags',
        'user_id',
        'total_cost',
        'total_latency_ms',
        'status',
    ];

    protected $casts = [
        'models' => 'json',
        'results' => 'json',
        'tags' => 'json',
    ];
}

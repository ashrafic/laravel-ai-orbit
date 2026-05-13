<?php

namespace Ashrafic\AiOrbit\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $content
 * @property string|null $agent_class
 * @property array|null $tags
 * @property string|null $user_id
 */
class SavedPrompt extends Model
{
    protected $table = 'orbit_saved_prompts';

    protected $fillable = [
        'name',
        'content',
        'agent_class',
        'tags',
        'user_id',
    ];

    protected $casts = [
        'tags' => 'json',
    ];
}

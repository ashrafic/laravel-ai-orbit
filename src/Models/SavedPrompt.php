<?php

namespace Ashrafic\AiOrbit\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $content
 * @property string|null $instruction
 * @property array|null $meta
 * @property array|null $tags
 * @property string|null $user_id
 */
class SavedPrompt extends Model
{
    protected $table = 'orbit_saved_prompts';

    protected $fillable = [
        'name',
        'content',
        'instruction',
        'meta',
        'tags',
        'user_id',
    ];

    protected $casts = [
        'meta' => 'json',
        'tags' => 'json',
    ];
}

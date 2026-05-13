<?php

namespace Ashrafic\AiOrbit\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $conversation_id
 * @property string|null $user_id
 * @property string|null $notes
 */
class Bookmark extends Model
{
    protected $table = 'orbit_bookmarks';

    protected $fillable = [
        'conversation_id',
        'user_id',
        'notes',
    ];
}

<?php

namespace Ashrafic\AiOrbit\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $threshold_amount
 * @property string $period
 * @property array|null $channels
 * @property bool $enabled
 * @property string|null $last_triggered_at
 */
class BudgetAlert extends Model
{
    protected $table = 'orbit_budget_alerts';

    protected $fillable = [
        'threshold_amount',
        'period',
        'channels',
        'enabled',
    ];

    protected $casts = [
        'channels' => 'json',
        'enabled' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];
}

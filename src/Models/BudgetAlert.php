<?php

namespace Ashrafic\AiOrbit\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $threshold_amount
 * @property string $period
 * @property array|null $channels
 * @property array|null $recipients
 * @property bool $enabled
 * @property CarbonInterface|null $last_triggered_at
 */
class BudgetAlert extends Model
{
    protected $table = 'orbit_budget_alerts';

    protected $fillable = [
        'threshold_amount',
        'period',
        'channels',
        'recipients',
        'enabled',
    ];

    protected $casts = [
        'channels' => 'json',
        'recipients' => 'json',
        'enabled' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];
}

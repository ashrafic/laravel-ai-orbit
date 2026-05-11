<?php

namespace Ashrafic\AiOrbit\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $model
 * @property string|null $provider
 * @property string $input_cost_per_1m
 * @property string $output_cost_per_1m
 * @property string $currency
 * @property bool $is_default
 */
class PricingRule extends Model
{
    protected $table = 'orbit_pricing_rules';

    protected $fillable = [
        'model',
        'provider',
        'input_cost_per_1m',
        'output_cost_per_1m',
        'currency',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];
}

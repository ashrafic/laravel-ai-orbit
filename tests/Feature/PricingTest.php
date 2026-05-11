<?php

use Ashrafic\AiOrbit\Models\PricingRule;
use Ashrafic\AiOrbit\Services\CostCalculator;

test('PricingRule model can be created and read', function () {
    $rule = PricingRule::create([
        'model' => 'gpt-4o',
        'provider' => 'openai',
        'input_cost_per_1m' => '2.50',
        'output_cost_per_1m' => '10.00',
        'currency' => 'USD',
    ]);

    $found = PricingRule::find($rule->id);

    expect($found->model)->toBe('gpt-4o');
    expect((float) $found->input_cost_per_1m)->toBe(2.50);
});

test('CostCalculator calculates cost correctly', function () {
    PricingRule::create([
        'model' => 'gpt-4o',
        'input_cost_per_1m' => '2.50',
        'output_cost_per_1m' => '10.00',
        'currency' => 'USD',
    ]);

    $calculator = app(CostCalculator::class);

    $result = $calculator->calculate('gpt-4o', 1_000_000, 1_000_000);

    expect($result['input_cost'])->toBe(2.50);
    expect($result['output_cost'])->toBe(10.00);
    expect($result['total'])->toBe(12.50);
    expect($result['currency'])->toBe('USD');
});

test('PricingMatrix can create pricing rule via model', function () {
    PricingRule::create([
        'model' => 'claude-3-opus',
        'provider' => 'anthropic',
        'input_cost_per_1m' => '15.00',
        'output_cost_per_1m' => '75.00',
    ]);

    expect(PricingRule::where('model', 'claude-3-opus')->exists())->toBeTrue();
});

<?php

use Ashrafic\AiOrbit\Http\Livewire\BudgetAlerts;
use Ashrafic\AiOrbit\Models\Bookmark;
use Ashrafic\AiOrbit\Models\BudgetAlert;
use Illuminate\Database\QueryException;

test('Bookmark model can be created and deleted', function () {
    $bookmark = Bookmark::create([
        'conversation_id' => 1,
        'user_id' => '1',
        'notes' => 'Important conversation',
    ]);

    expect(Bookmark::where('conversation_id', 1)->exists())->toBeTrue();

    $bookmark->delete();

    expect(Bookmark::where('conversation_id', 1)->exists())->toBeFalse();
});

test('Bookmark has unique constraint on conversation and user', function () {
    Bookmark::create(['conversation_id' => 1, 'user_id' => '1']);

    expect(fn () => Bookmark::create(['conversation_id' => 1, 'user_id' => '1']))
        ->toThrow(QueryException::class);
});

test('BudgetAlert model can be created', function () {
    $alert = BudgetAlert::create([
        'threshold_amount' => '50.00',
        'period' => 'monthly',
        'channels' => ['mail'],
        'enabled' => true,
    ]);

    expect($alert->threshold_amount)->toBe('50.00');
    expect($alert->period)->toBe('monthly');
    expect($alert->channels)->toBe(['mail']);
    expect($alert->enabled)->toBeTrue();
});

test('BudgetAlerts component can be instantiated', function () {
    $component = new BudgetAlerts;

    expect($component)->toBeInstanceOf(BudgetAlerts::class);
});

test('BudgetAlerts can create alert via model', function () {
    BudgetAlert::create([
        'threshold_amount' => '100.00',
        'period' => 'monthly',
        'channels' => ['mail'],
        'enabled' => true,
    ]);

    expect(BudgetAlert::where('threshold_amount', '100.00')->exists())->toBeTrue();
});

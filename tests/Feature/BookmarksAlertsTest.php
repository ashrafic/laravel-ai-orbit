<?php

use Ashrafic\AiOrbit\Http\Livewire\BudgetAlerts;
use Ashrafic\AiOrbit\Models\Bookmark;
use Ashrafic\AiOrbit\Models\BudgetAlert;
use Ashrafic\AiOrbit\Notifications\BudgetExceeded;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

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
        'recipients' => ['ops@example.com'],
        'enabled' => true,
    ]);

    expect($alert->threshold_amount)->toBe('50.00');
    expect($alert->period)->toBe('monthly');
    expect($alert->channels)->toBe(['mail']);
    expect($alert->recipients)->toBe(['ops@example.com']);
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
        'recipients' => ['ops@example.com'],
        'enabled' => true,
    ]);

    expect(BudgetAlert::where('threshold_amount', '100.00')->exists())->toBeTrue();
});

test('BudgetAlerts requires at least one recipient email', function () {
    Livewire::test(BudgetAlerts::class)
        ->set('showForm', true)
        ->set('thresholdAmount', '100.00')
        ->set('period', 'monthly')
        ->set('channels', ['mail'])
        ->set('recipients', [])
        ->call('save')
        ->assertHasErrors(['recipients']);
});

test('BudgetExceeded notification uses Orbit mail views', function () {
    $alert = BudgetAlert::create([
        'threshold_amount' => '100.00',
        'period' => 'monthly',
        'channels' => ['mail'],
        'recipients' => ['ops@example.com'],
        'enabled' => true,
    ]);

    $message = (new BudgetExceeded($alert, 125.25, true))->toMail((object) []);

    expect($message->view)->toBe([
        'html' => 'ai-orbit::emails.budget-exceeded',
        'text' => 'ai-orbit::emails.budget-exceeded-text',
    ])
        ->and($message->viewData['hasUnpricedUsage'])->toBeTrue()
        ->and($message->viewData['currentSpend'])->toBe(125.25);
});

test('BudgetAlerts can send a test email to configured recipients', function () {
    Notification::fake();

    $alert = BudgetAlert::create([
        'threshold_amount' => '100.00',
        'period' => 'monthly',
        'channels' => ['mail'],
        'recipients' => ['ops@example.com'],
        'enabled' => true,
    ]);

    Livewire::test(BudgetAlerts::class)
        ->call('sendTest', $alert->id)
        ->assertHasNoErrors()
        ->assertSee('Test budget alert email queued.');

    Notification::assertSentOnDemand(BudgetExceeded::class, function (BudgetExceeded $notification, array $channels, object $notifiable): bool {
        return $channels === ['mail']
            && $notifiable->routeNotificationFor('mail') === 'ops@example.com'
            && $notification->toArray($notifiable)['test'] === true;
    });
});

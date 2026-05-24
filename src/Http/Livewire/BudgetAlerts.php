<?php

namespace Ashrafic\AiOrbit\Http\Livewire;

use Ashrafic\AiOrbit\Models\BudgetAlert;
use Ashrafic\AiOrbit\Notifications\BudgetExceeded;
use Ashrafic\AiOrbit\Services\BudgetMonitor;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;

class BudgetAlerts extends Component
{
    public string $thresholdAmount = '';

    public string $period = 'monthly';

    public array $channels = ['mail'];

    public array $recipients = [];

    public string $recipientEmail = '';

    public bool $enabled = true;

    public ?int $editingId = null;

    public bool $showForm = false;

    protected $rules = [
        'thresholdAmount' => 'required|numeric|min:0.01',
        'period' => 'required|string|in:daily,weekly,monthly',
        'channels' => 'required|array|min:1',
        'recipients' => 'required|array|min:1',
        'recipients.*' => 'required|email',
        'enabled' => 'boolean',
    ];

    public function edit(?int $id = null): void
    {
        if ($id) {
            $alert = BudgetAlert::findOrFail($id);
            $this->editingId = $id;
            $this->showForm = true;
            $this->thresholdAmount = (string) $alert->threshold_amount;
            $this->period = $alert->period;
            $this->channels = $alert->channels ?? ['mail'];
            $this->recipients = $alert->recipients ?? [];
            $this->enabled = $alert->enabled;
        } else {
            $this->reset(['editingId', 'thresholdAmount', 'period', 'channels', 'recipients', 'recipientEmail', 'enabled']);
            $this->channels = ['mail'];
            $this->enabled = true;
        }
    }

    public function toggleChannel(string $channel): void
    {
        if (in_array($channel, $this->channels, true)) {
            $this->channels = array_values(array_filter($this->channels, fn ($c) => $c !== $channel));
        } else {
            $this->channels[] = $channel;
        }
    }

    public function addRecipient(): void
    {
        $this->validateOnly('recipientEmail', [
            'recipientEmail' => 'required|email',
        ]);

        if (! in_array($this->recipientEmail, $this->recipients, true)) {
            $this->recipients[] = $this->recipientEmail;
        }

        $this->recipientEmail = '';
    }

    public function removeRecipient(string $email): void
    {
        $this->recipients = array_values(array_filter(
            $this->recipients,
            fn (string $recipient): bool => $recipient !== $email
        ));
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'threshold_amount' => $this->thresholdAmount,
            'period' => $this->period,
            'channels' => $this->channels,
            'recipients' => $this->recipients,
            'enabled' => $this->enabled,
        ];

        if ($this->editingId) {
            BudgetAlert::findOrFail($this->editingId)->update($data);
        } else {
            BudgetAlert::create($data);
        }

        $this->reset(['editingId', 'showForm', 'thresholdAmount', 'period', 'channels', 'recipients', 'recipientEmail', 'enabled']);
    }

    public function delete(int $id): void
    {
        BudgetAlert::findOrFail($id)->delete();
    }

    public function sendTest(int $id): void
    {
        $alert = BudgetAlert::findOrFail($id);
        $recipients = $alert->recipients ?? [];

        if ($recipients === []) {
            $this->addError('recipients', 'Add at least one recipient before sending a test email.');

            return;
        }

        $monitor = app(BudgetMonitor::class);
        $currentSpend = $monitor->getCurrentSpend($alert->period);
        $hasUnpricedUsage = $monitor->hasUnpricedUsage($alert->period);

        foreach ($recipients as $recipient) {
            Notification::route('mail', $recipient)
                ->notify(new BudgetExceeded($alert, $currentSpend, $hasUnpricedUsage, test: true));
        }

        session()->flash('budget-alert-status', 'Test budget alert email queued.');
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'showForm', 'thresholdAmount', 'period', 'channels', 'recipients', 'recipientEmail', 'enabled']);
    }

    public function render(): View
    {
        $alerts = BudgetAlert::orderBy('created_at')->get();

        return view('ai-orbit::livewire.budget-alerts', [
            'alerts' => $alerts,
        ]);
    }
}

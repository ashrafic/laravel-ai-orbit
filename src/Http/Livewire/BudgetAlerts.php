<?php

namespace Ashrafic\AiOrbit\Http\Livewire;

use Ashrafic\AiOrbit\Models\BudgetAlert;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class BudgetAlerts extends Component
{
    public string $thresholdAmount = '';

    public string $period = 'monthly';

    public array $channels = ['mail'];

    public bool $enabled = true;

    public ?int $editingId = null;

    protected $rules = [
        'thresholdAmount' => 'required|numeric|min:0.01',
        'period' => 'required|string|in:daily,weekly,monthly',
        'channels' => 'required|array|min:1',
        'enabled' => 'boolean',
    ];

    public function edit(?int $id = null): void
    {
        if ($id) {
            $alert = BudgetAlert::findOrFail($id);
            $this->editingId = $id;
            $this->thresholdAmount = (string) $alert->threshold_amount;
            $this->period = $alert->period;
            $this->channels = $alert->channels ?? ['mail'];
            $this->enabled = $alert->enabled;
        } else {
            $this->reset(['editingId', 'thresholdAmount', 'period', 'channels', 'enabled']);
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

    public function save(): void
    {
        $this->validate();

        $data = [
            'threshold_amount' => $this->thresholdAmount,
            'period' => $this->period,
            'channels' => $this->channels,
            'enabled' => $this->enabled,
        ];

        if ($this->editingId) {
            BudgetAlert::findOrFail($this->editingId)->update($data);
        } else {
            BudgetAlert::create($data);
        }

        $this->reset(['editingId', 'thresholdAmount', 'period', 'channels', 'enabled']);
    }

    public function delete(int $id): void
    {
        BudgetAlert::findOrFail($id)->delete();
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'thresholdAmount', 'period', 'channels', 'enabled']);
    }

    public function render(): View
    {
        $alerts = BudgetAlert::orderBy('created_at')->get();

        return view('ai-orbit::usage.alerts', [
            'alerts' => $alerts,
        ]);
    }
}

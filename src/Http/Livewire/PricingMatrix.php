<?php

namespace Ashrafic\AiOrbit\Http\Livewire;

use Ashrafic\AiOrbit\Models\PricingRule;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PricingMatrix extends Component
{
    public string $model = '';

    public ?string $provider = null;

    public string $inputCost = '0';

    public string $outputCost = '0';

    public string $currency = 'USD';

    public ?int $editingId = null;

    public bool $showForm = false;

    protected $rules = [
        'model' => 'required|string|max:255',
        'provider' => 'nullable|string|max:255',
        'inputCost' => 'required|numeric|min:0',
        'outputCost' => 'required|numeric|min:0',
        'currency' => 'required|string|max:3',
    ];

    public function edit(?int $id = null): void
    {
        if ($id) {
            $rule = PricingRule::findOrFail($id);
            $this->editingId = $id;
            $this->showForm = true;
            $this->model = $rule->model;
            $this->provider = $rule->provider;
            $this->inputCost = (string) $rule->input_cost_per_1m;
            $this->outputCost = (string) $rule->output_cost_per_1m;
            $this->currency = $rule->currency;
        } else {
            $this->reset(['editingId', 'model', 'provider', 'inputCost', 'outputCost', 'currency']);
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'model' => $this->model,
            'provider' => $this->provider,
            'input_cost_per_1m' => $this->inputCost,
            'output_cost_per_1m' => $this->outputCost,
            'currency' => $this->currency,
        ];

        if ($this->editingId) {
            PricingRule::findOrFail($this->editingId)->update($data);
        } else {
            PricingRule::create($data);
        }

        $this->reset(['editingId', 'showForm', 'model', 'provider', 'inputCost', 'outputCost', 'currency']);
    }

    public function delete(int $id): void
    {
        PricingRule::findOrFail($id)->delete();
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'showForm', 'model', 'provider', 'inputCost', 'outputCost', 'currency']);
    }

    public function render(): View
    {
        $rules = PricingRule::orderBy('model')->get();

        return view('ai-orbit::livewire.pricing-matrix', [
            'rules' => $rules,
        ]);
    }
}

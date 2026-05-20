<div>
    {{-- Add New Button --}}
    @if(! $showForm)
    <div class="mb-4">
        <button wire:click="$set('showForm', true)"
            class="orbit-btn-primary">
            + Add Pricing Rule
        </button>
    </div>
    @endif

    {{-- Form --}}
    @if($showForm)
    <x-ai-orbit::card class="mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-50 tracking-tight mb-4">
            {{ $editingId ? 'Edit Pricing Rule' : 'Add Pricing Rule' }}
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Model</label>
                <input wire:model="model" type="text"
                    class="orbit-input w-full"
                    placeholder="e.g. gpt-4o">
                @error('model') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Provider</label>
                <select wire:model="provider"
                    class="orbit-input w-full">
                    <option value="">Any / Default</option>
                    @foreach($this->getConfiguredProviders() as $p)
                        <option value="{{ $p }}">{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Input Cost (per 1M tokens)</label>
                <input wire:model="inputCost" type="number" step="0.0001" min="0"
                    class="orbit-input w-full">
                @error('inputCost') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Output Cost (per 1M tokens)</label>
                <input wire:model="outputCost" type="number" step="0.0001" min="0"
                    class="orbit-input w-full">
                @error('outputCost') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="flex gap-2">
            <button wire:click="save"
                class="orbit-btn-primary">
                {{ $editingId ? 'Update' : 'Save' }}
            </button>
            <button wire:click="cancelEdit"
                class="orbit-btn-secondary">
                Cancel
            </button>
        </div>
    </x-ai-orbit::card>
    @endif

    {{-- Table --}}
    <x-ai-orbit::card padding="p-0">
        <div class="overflow-x-auto">
            <table class="orbit-table w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200/60 dark:border-white/8">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Model</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Provider</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Input / 1M</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Output / 1M</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/4">
                    @forelse($rules as $rule)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-50">{{ $rule->model }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $rule->provider ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-500 dark:text-gray-400">{{ $rule->currency }} {{ $rule->input_cost_per_1m }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-500 dark:text-gray-400">{{ $rule->currency }} {{ $rule->output_cost_per_1m }}</td>
                        <td class="px-4 py-3 text-sm text-right space-x-1">
                            <button wire:click="edit({{ $rule->id }})" class="text-orbit-500 hover:text-orbit-600 dark:text-orbit-400 dark:hover:text-orbit-300 text-xs font-medium">Edit</button>
                            <button wire:click="delete({{ $rule->id }})" wire:confirm="Delete this pricing rule?" class="text-red-600 dark:text-red-400 hover:underline text-xs font-medium">Delete</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No pricing rules defined yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ai-orbit::card>
</div>

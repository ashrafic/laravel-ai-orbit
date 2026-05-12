<div>
    {{-- Form --}}
    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            {{ $editingId ? 'Edit Pricing Rule' : 'Add Pricing Rule' }}
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Model</label>
                <input wire:model="model" type="text"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 text-sm"
                    placeholder="e.g. gpt-4o">
                @error('model') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Provider</label>
                <input wire:model="provider" type="text"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 text-sm"
                    placeholder="e.g. openai">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Input Cost (per 1M tokens)</label>
                <input wire:model="inputCost" type="number" step="0.0001" min="0"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 text-sm">
                @error('inputCost') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Output Cost (per 1M tokens)</label>
                <input wire:model="outputCost" type="number" step="0.0001" min="0"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 text-sm">
                @error('outputCost') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="flex gap-2">
            <button wire:click="save"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                {{ $editingId ? 'Update' : 'Save' }}
            </button>
            @if($editingId)
            <button wire:click="cancelEdit"
                class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">
                Cancel
            </button>
            @endif
        </div>
    </div>

    {{-- Table --}}
    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Model</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Provider</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Input / 1M</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Output / 1M</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($rules as $rule)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-300">{{ $rule->model }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $rule->provider ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-600 dark:text-gray-400">{{ $rule->currency }} {{ $rule->input_cost_per_1m }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-600 dark:text-gray-400">{{ $rule->currency }} {{ $rule->output_cost_per_1m }}</td>
                        <td class="px-4 py-3 text-sm text-right space-x-1">
                            <button wire:click="edit({{ $rule->id }})" class="text-blue-600 dark:text-blue-400 hover:underline text-xs font-medium">Edit</button>
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
    </div>
</div>

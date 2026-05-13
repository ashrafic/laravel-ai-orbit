<div>
    {{-- Add New Button --}}
    @if(! $showForm)
    <div class="mb-4">
        <button wire:click="$set('showForm', true)"
            class="orbit-btn-primary">
            + New Budget Alert
        </button>
    </div>
    @endif

    {{-- Form --}}
    @if($showForm)
    <x-ai-orbit::card class="mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-50 tracking-tight mb-4">
            {{ $editingId ? 'Edit Budget Alert' : 'New Budget Alert' }}
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Threshold Amount</label>
                <input wire:model="thresholdAmount" type="number" step="0.01" min="0.01"
                    class="orbit-input w-full">
                @error('thresholdAmount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Period</label>
                <select wire:model="period"
                    class="orbit-input w-full">
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                </select>
                @error('period') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notification Channels</label>
            <div class="flex gap-3">
                <label wire:click="toggleChannel('mail')" class="flex items-center gap-1.5 cursor-pointer text-sm text-gray-700 dark:text-gray-300">
                    <span class="w-4 h-4 rounded border flex items-center justify-center {{ in_array('mail', $channels) ? 'bg-orbit-500 border-orbit-500' : 'border-gray-300 dark:border-gray-600' }}">
                        @if(in_array('mail', $channels))
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </span>
                    Email
                </label>
            </div>
            @error('channels') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-300">
                <input wire:model="enabled" type="checkbox"
                    class="rounded border-gray-300 dark:border-gray-600 text-orbit-500">
                Enabled
            </label>
        </div>

        <div class="flex gap-2">
            <button wire:click="save"
                class="orbit-btn-primary">
                {{ $editingId ? 'Update' : 'Create Alert' }}
            </button>
            <button wire:click="cancelEdit"
                class="orbit-btn-secondary">
                Cancel
            </button>
        </div>
    </x-ai-orbit::card>
    @endif

    {{-- Alerts List --}}
    <x-ai-orbit::card padding="p-0">
        <div class="overflow-x-auto">
            <table class="orbit-table w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200/60 dark:border-white/8">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Threshold</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Period</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Channels</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/4">
                    @forelse($alerts as $alert)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-50">{{ config('ai-orbit.currency_symbol', '$') }}{{ number_format($alert->threshold_amount, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 capitalize">{{ $alert->period }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ implode(', ', $alert->channels ?? ['mail']) }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $alert->enabled ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400' }}">
                                {{ $alert->enabled ? 'Active' : 'Disabled' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-right space-x-1">
                            <button wire:click="edit({{ $alert->id }})" class="text-orbit-500 hover:text-orbit-600 dark:text-orbit-400 dark:hover:text-orbit-300 text-xs font-medium">Edit</button>
                            <button wire:click="delete({{ $alert->id }})" wire:confirm="Delete this budget alert?" class="text-red-600 dark:text-red-400 hover:underline text-xs font-medium">Delete</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No budget alerts configured yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ai-orbit::card>
</div>

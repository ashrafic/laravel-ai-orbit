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
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notification Channels</label>
            <div class="flex gap-4">
                <button type="button" wire:click="toggleChannel('mail')"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg border text-sm font-medium transition-all
                           {{ in_array('mail', $channels)
                               ? 'bg-orbit-50 dark:bg-orbit-900/20 border-orbit-300 dark:border-orbit-700 text-orbit-700 dark:text-orbit-300'
                               : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-500' }}">
                    <span class="w-4 h-4 rounded border-2 flex items-center justify-center flex-shrink-0
                                 {{ in_array('mail', $channels)
                                     ? 'bg-orbit-500 border-orbit-500'
                                     : 'border-gray-300 dark:border-gray-500' }}">
                        @if(in_array('mail', $channels))
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Email
                    </span>
                </button>
            </div>
            @error('channels') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Recipients</label>
            <div class="flex gap-2">
                <input wire:model="recipientEmail" wire:keydown.enter.prevent="addRecipient" type="email"
                    class="orbit-input w-full"
                    placeholder="ops@example.com">
                <button type="button" wire:click="addRecipient" class="orbit-btn-secondary">
                    Add
                </button>
            </div>
            @error('recipientEmail') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            @error('recipients') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            @error('recipients.*') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

            @if($recipients)
                <div class="flex flex-wrap gap-2 mt-3">
                    @foreach($recipients as $recipient)
                        <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-md bg-gray-100 dark:bg-white/8 text-xs text-gray-700 dark:text-gray-300">
                            {{ $recipient }}
                            <button type="button" wire:click="removeRecipient('{{ $recipient }}')" class="text-gray-400 hover:text-red-500 dark:hover:text-red-300">
                                &times;
                            </button>
                        </span>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="mb-4 pt-2 border-t border-gray-100 dark:border-white/5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Alert Status</label>
            <button type="button" wire:click="$set('enabled', {{ $enabled ? 'false' : 'true' }})"
                class="flex items-center gap-2 px-3 py-2 rounded-lg border text-sm font-medium transition-all
                       {{ $enabled
                           ? 'bg-green-50 dark:bg-green-900/20 border-green-300 dark:border-green-700 text-green-700 dark:text-green-300'
                           : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-500' }}">
                <span class="w-4 h-4 rounded border-2 flex items-center justify-center flex-shrink-0
                             {{ $enabled
                                 ? 'bg-green-500 border-green-500'
                                 : 'border-gray-300 dark:border-gray-500' }}">
                    @if($enabled)
                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    @endif
                </span>
                {{ $enabled ? 'Active' : 'Disabled' }}
            </button>
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
    @if(session('budget-alert-status'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300">
            {{ session('budget-alert-status') }}
        </div>
    @endif

    <x-ai-orbit::card padding="p-0">
        <div class="overflow-x-auto">
            <table class="orbit-table w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200/60 dark:border-white/8">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Threshold</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Period</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Channels</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Recipients</th>
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
                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ implode(', ', $alert->recipients ?? []) }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $alert->enabled ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400' }}">
                                {{ $alert->enabled ? 'Active' : 'Disabled' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-right space-x-1">
                            <button wire:click="sendTest({{ $alert->id }})" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 text-xs font-medium">Send Test</button>
                            <button wire:click="edit({{ $alert->id }})" class="text-orbit-500 hover:text-orbit-600 dark:text-orbit-400 dark:hover:text-orbit-300 text-xs font-medium">Edit</button>
                            <button wire:click="delete({{ $alert->id }})" wire:confirm="Delete this budget alert?" class="text-red-600 dark:text-red-400 hover:underline text-xs font-medium">Delete</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No budget alerts configured yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ai-orbit::card>
</div>

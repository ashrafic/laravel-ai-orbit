<div>
    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Cost Dashboard</h2>
            <div class="flex gap-2">
                <select wire:model.live="period" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 px-3 py-1.5 text-sm">
                    @foreach($periods as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <select wire:model.live="groupBy" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 px-3 py-1.5 text-sm">
                    @foreach($groups as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <x-ai-orbit::card>
                <span class="text-sm text-gray-500 dark:text-gray-400">Total Cost</span>
                <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $currencySymbol }}{{ number_format($totalCost, 4) }}</span>
            </x-ai-orbit::card>
            <x-ai-orbit::card>
                <span class="text-sm text-gray-500 dark:text-gray-400">Conversations</span>
                <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_conversations']) }}</span>
            </x-ai-orbit::card>
            <x-ai-orbit::card>
                <span class="text-sm text-gray-500 dark:text-gray-400">Input Tokens</span>
                <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['input_tokens']) }}</span>
            </x-ai-orbit::card>
            <x-ai-orbit::card>
                <span class="text-sm text-gray-500 dark:text-gray-400">Output Tokens</span>
                <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['output_tokens']) }}</span>
            </x-ai-orbit::card>
        </div>

        @if($breakdown->isNotEmpty())
        <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ ucfirst($groupBy) }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Messages</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Input Tokens</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Output Tokens</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                    @foreach($breakdown as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300">{{ class_basename($row->agent ?? 'Unknown') }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-600 dark:text-gray-400">{{ number_format($row->message_count ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-600 dark:text-gray-400">{{ number_format($row->input_tokens ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-600 dark:text-gray-400">{{ number_format($row->output_tokens ?? 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <x-ai-orbit::empty-state message="No data for the selected period." />
        @endif
    </div>
</div>

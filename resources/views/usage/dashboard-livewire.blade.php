<div>
    <x-ai-orbit::card>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50 tracking-tight">Cost Dashboard</h2>
            <div class="flex gap-2">
                <select wire:model.live="period" class="orbit-input">
                    @foreach($periods as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <select wire:model.live="groupBy" class="orbit-input">
                    @foreach($groups as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-8">
            <x-ai-orbit::stat label="Total Cost" value="{{ $currencySymbol }}{{ number_format($totalCost, 4) }}" />
            <x-ai-orbit::stat label="Conversations" value="{{ number_format($stats['total_conversations']) }}" />
            <x-ai-orbit::stat label="Input Tokens" value="{{ number_format($stats['input_tokens']) }}" />
            <x-ai-orbit::stat label="Output Tokens" value="{{ number_format($stats['output_tokens']) }}" />
        </div>

        @if($breakdown->isNotEmpty())
        <x-ai-orbit::card padding="p-0">
            <div class="overflow-x-auto">
                <table class="orbit-table w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200/60 dark:border-white/8">
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ ucfirst($groupBy) }}</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Messages</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Input Tokens</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Output Tokens</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/4">
                        @foreach($breakdown as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-50">{{ class_basename($row->agent ?? 'Unknown') }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-500 dark:text-gray-400">{{ number_format($row->message_count ?? 0) }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-500 dark:text-gray-400">{{ number_format($row->input_tokens ?? 0) }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-500 dark:text-gray-400">{{ number_format($row->output_tokens ?? 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ai-orbit::card>
        @else
        <x-ai-orbit::empty-state message="No data for the selected period." />
        @endif
    </x-ai-orbit::card>
</div>

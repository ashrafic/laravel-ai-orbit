<x-ai-orbit::layout>
    @slot('breadcrumb', 'Runs')

    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-50 tracking-tight">Runs</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Inspect SDK invocations captured by Orbit observability.</p>
        </div>

        <x-ai-orbit::card padding="p-4">
            <form method="GET" action="{{ route('orbit.runs.index') }}" class="grid grid-cols-1 md:grid-cols-4 xl:grid-cols-7 gap-3">
                @foreach ([
                    'operation' => 'Operation',
                    'status' => 'Status',
                    'provider' => 'Provider',
                    'model' => 'Model',
                    'agent_class' => 'Agent class',
                ] as $name => $label)
                    <label class="block">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $label }}</span>
                        <input
                            name="{{ $name }}"
                            value="{{ $filters[$name] ?? '' }}"
                            class="mt-1 w-full rounded-lg border border-gray-200/70 dark:border-white/10 bg-white/70 dark:bg-white/5 px-3 py-2 text-sm text-gray-700 dark:text-gray-200"
                        >
                    </label>
                @endforeach

                <label class="block">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Conversation</span>
                    <select name="conversation_state" class="mt-1 w-full rounded-lg border border-gray-200/70 dark:border-white/10 bg-white/70 dark:bg-white/5 px-3 py-2 text-sm text-gray-700 dark:text-gray-200">
                        <option value="">Any</option>
                        <option value="linked" @selected(($filters['conversation_state'] ?? '') === 'linked')>Linked</option>
                        <option value="unlinked" @selected(($filters['conversation_state'] ?? '') === 'unlinked')>Unlinked</option>
                    </select>
                </label>

                <div class="flex items-end gap-2">
                    <button class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-500">Filter</button>
                    <a href="{{ route('orbit.runs.index') }}" class="rounded-lg border border-gray-200/70 dark:border-white/10 px-3 py-2 text-sm text-gray-500 dark:text-gray-300">Reset</a>
                </div>
            </form>
        </x-ai-orbit::card>

        <x-ai-orbit::card padding="p-0">
            @if ($runs->isEmpty())
                <x-ai-orbit::empty-state title="No runs recorded">
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">
                        Future SDK activity will appear here when observability is enabled.
                    </p>
                </x-ai-orbit::empty-state>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200/60 dark:divide-white/8">
                        <thead class="bg-gray-50/80 dark:bg-white/5">
                            <tr>
                                @foreach (['Run', 'Status', 'Provider', 'Model', 'Agent', 'Tokens', 'Cost', 'Started'] as $heading)
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $heading }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200/60 dark:divide-white/8">
                            @foreach ($runs as $run)
                                <tr class="hover:bg-gray-50/80 dark:hover:bg-white/5">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('orbit.runs.show', $run) }}" class="text-sm font-semibold text-indigo-600 dark:text-indigo-300">{{ $run->operation }}</a>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ $run->invocation_id ?? 'manual' }}</p>
                                    </td>
                                    <td class="px-4 py-3"><x-ai-orbit::badge :label="$run->status" :color="$run->status === 'completed' ? 'green' : 'yellow'" /></td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $run->provider ?? 'unknown' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $run->model ?? 'unknown' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $run->agent_class ? class_basename($run->agent_class) : 'None' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ number_format($run->input_tokens + $run->output_tokens) }}</td>
                                    <td class="px-4 py-3 text-sm {{ $run->missing_pricing ? 'text-amber-600 dark:text-amber-300' : 'text-gray-600 dark:text-gray-300' }}">
                                        {{ config('ai-orbit.currency_symbol', '$') }}{{ number_format((float) $run->cost, 6) }}
                                        @if($run->missing_pricing)
                                            <span class="block text-xs">unpriced</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $run->started_at?->diffForHumans() ?? 'unknown' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t border-gray-200/60 dark:border-white/8">
                    {{ $runs->links() }}
                </div>
            @endif
        </x-ai-orbit::card>
    </div>
</x-ai-orbit::layout>

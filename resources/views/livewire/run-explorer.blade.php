<div>
    <style>
        .run-filters { display: flex; flex-wrap: wrap; gap: 0.75rem; }
        .run-search { width: 100%; }
        .run-select { flex: 1 1 120px; min-width: 0; }
        @media (min-width: 640px) {
            .run-search { width: 320px; flex-shrink: 0; }
        }
    </style>

    {{-- Filters --}}
    <div class="run-filters mb-5">
        <div class="run-search">
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Search runs..."
                class="orbit-input"
            >
        </div>

        <select wire:model.live="operation" class="orbit-input run-select">
            <option value="">All Ops</option>
            @foreach ($this->availableOperations() as $op)
                <option value="{{ $op }}">{{ $op }}</option>
            @endforeach
        </select>

        <select wire:model.live="status" class="orbit-input run-select">
            <option value="">All Status</option>
            @foreach ($this->availableStatuses() as $st)
                <option value="{{ $st }}">{{ ucfirst($st) }}</option>
            @endforeach
        </select>

        <select wire:model.live="conversationState" class="orbit-input run-select">
            <option value="">Any Conv</option>
            <option value="linked">Linked</option>
            <option value="unlinked">Unlinked</option>
        </select>

        <select wire:model.live="dateRange" class="orbit-input run-select">
            <option value="">All Time</option>
            <option value="today">Today</option>
            <option value="7d">Last 7 Days</option>
            <option value="30d">Last 30 Days</option>
        </select>

        <select wire:model.live="provider" class="orbit-input run-select">
            <option value="">All Providers</option>
            @foreach ($this->availableProviders() as $prov)
                <option value="{{ $prov }}">{{ $prov }}</option>
            @endforeach
        </select>

        <select wire:model.live="model" class="orbit-input run-select">
            <option value="">All Models</option>
            @foreach ($this->availableModels() as $mdl)
                <option value="{{ $mdl }}">{{ $mdl }}</option>
            @endforeach
        </select>

        <select wire:model.live="agentClass" class="orbit-input run-select">
            <option value="">All Agents</option>
            @foreach ($this->availableAgentClasses() as $agent)
                <option value="{{ $agent }}">{{ class_basename($agent) }}</option>
            @endforeach
        </select>

        @if ($this->operation || $this->status || $this->provider || $this->model || $this->agentClass || $this->conversationState)
            <button wire:click="$set('operation', ''); $set('status', ''); $set('provider', ''); $set('model', ''); $set('agentClass', ''); $set('conversationState', ''); $set('search', ''); $set('dateRange', '')"
                class="rounded-lg border border-gray-200/70 dark:border-white/10 px-3 py-2 text-sm text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors whitespace-nowrap">
                Clear
            </button>
        @endif
    </div>

    {{-- Table --}}
    <x-ai-orbit::card padding="p-0">
        <div class="overflow-x-auto">
            <table class="orbit-table w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200/60 dark:border-white/8">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Run</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">Status</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Provider</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Model</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden xl:table-cell">Agent</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer select-none"
                            wire:click="sortBy('input_tokens')">
                            Tokens
                            @if ($sortField === 'input_tokens')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '&#8593;' : '&#8595;' }}</span>
                            @endif
                        </th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer select-none"
                            wire:click="sortBy('cost')">
                            Cost
                            @if ($sortField === 'cost')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '&#8593;' : '&#8595;' }}</span>
                            @endif
                        </th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/4">
                    @forelse ($runs as $run)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-3">
                                <a href="{{ route('orbit.runs.show', $run) }}"
                                   class="text-orbit-500 hover:text-orbit-600 dark:text-orbit-400 dark:hover:text-orbit-300 font-medium">
                                    {{ $run->operation }}
                                </a>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ \Illuminate\Support\Str::limit($run->invocation_id ?? 'manual', 8, '') }}</p>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <x-ai-orbit::badge :label="$run->status" :color="$run->status === 'completed' ? 'green' : ($run->status === 'failed' ? 'red' : 'yellow')" />
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <span class="text-gray-500 dark:text-gray-400">
                                    {{ $run->provider ?? 'unknown' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <span class="text-gray-500 dark:text-gray-400">
                                    {{ $run->model ?? 'unknown' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 hidden xl:table-cell">
                                <span class="text-gray-500 dark:text-gray-400">
                                    {{ $run->agent_class ? class_basename($run->agent_class) : '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                {{ number_format($run->input_tokens + $run->output_tokens) }}
                            </td>
                            <td class="px-4 py-3 {{ $run->missing_pricing ? 'text-amber-600 dark:text-amber-300' : 'text-gray-500 dark:text-gray-400' }}">
                                <span>{{ config('ai-orbit.currency_symbol', '$') }}{{ number_format((float) $run->cost, 6) }}</span>
                                @if ($run->missing_pricing)
                                    <span class="block text-xs">unpriced</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    @if ($run->conversation_id)
                                        <a href="{{ route('orbit.conversations.show', $run->conversation_id) }}"
                                           class="p-1.5 text-gray-400 hover:text-indigo-500 dark:hover:text-indigo-400 rounded hover:bg-gray-100 dark:hover:bg-gray-700"
                                           title="View Conversation">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                                            </svg>
                                        </a>
                                    @endif
                                    <button
                                        wire:click="deleteRun({{ $run->id }})"
                                        wire:confirm="Are you sure you want to delete this run?"
                                        class="p-1.5 text-gray-400 hover:text-red-500 dark:hover:text-red-400 rounded hover:bg-gray-100 dark:hover:bg-gray-700"
                                        title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12">
                                <x-ai-orbit::empty-state title="No runs found"
                                    description="SDK activity will appear here when observability is enabled." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($runs->hasPages())
            <div class="px-4 py-3 border-t border-gray-200/60 dark:border-white/8">
                {{ $runs->links() }}
            </div>
        @endif
    </x-ai-orbit::card>
</div>

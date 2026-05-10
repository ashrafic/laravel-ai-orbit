<div>
    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-4">
        <div class="flex-1">
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Search conversations..."
                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-orbit-500 focus:border-orbit-500"
            >
        </div>

        <select
            wire:model.live="dateRange"
            class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-orbit-500 focus:border-orbit-500"
        >
            <option value="">All Time</option>
            <option value="today">Today</option>
            <option value="7d">Last 7 Days</option>
            <option value="30d">Last 30 Days</option>
        </select>
    </div>

    {{-- Table --}}
    <x-laravel-ai-orbit::card padding="p-0">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-800">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Conversation</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">Agent</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Messages</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Tokens</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($conversations as $conversation)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-4 py-3">
                                <a href="{{ route('orbit.conversations.show', $conversation->id) }}"
                                   class="text-orbit-500 hover:text-orbit-600 dark:text-orbit-400 dark:hover:text-orbit-300 font-medium">
                                    {{ \Illuminate\Support\Str::limit($conversation->title, 40) }}
                                </a>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ \Illuminate\Support\Str::limit($conversation->id, 8, '') }}</p>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <span class="text-gray-600 dark:text-gray-400">
                                    {{ $conversation->agent_class ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <span class="text-gray-600 dark:text-gray-400">
                                    {{ $conversation->message_count ?? 0 }}
                                </span>
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <span class="text-gray-600 dark:text-gray-400">
                                    {{ number_format(($conversation->total_input_tokens ?? 0) + ($conversation->total_output_tokens ?? 0)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($conversation->created_at)->diffForHumans() }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('orbit.traces.show', $conversation->id) }}"
                                       class="p-1.5 text-gray-400 hover:text-yellow-500 dark:hover:text-yellow-400 rounded hover:bg-gray-100 dark:hover:bg-gray-700"
                                       title="View Trace">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </a>
                                    <button
                                        wire:click="deleteConversation('{{ $conversation->id }}')"
                                        wire:confirm="Are you sure you want to delete this conversation?"
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
                            <td colspan="6" class="px-4 py-12">
                                <x-laravel-ai-orbit::empty-state title="No conversations found"
                                    description="Agent conversations will appear here once activity is recorded." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($conversations->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-800">
                {{ $conversations->links() }}
            </div>
        @endif
    </x-laravel-ai-orbit::card>
</div>

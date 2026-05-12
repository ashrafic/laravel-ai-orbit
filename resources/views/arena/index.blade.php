<x-ai-orbit::layout>
    @slot('breadcrumb', 'Arena')

    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-50 tracking-tight">Arena</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Compare prompts across multiple AI models side-by-side.</p>
        </div>

        <livewire:ai-orbit.arena-compare />

        @if($sessions->isNotEmpty())
        <div class="mt-12">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50 tracking-tight mb-4">Session History</h2>
            <x-ai-orbit::card padding="p-0">
                <div class="overflow-x-auto">
                    <table class="orbit-table w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200/60 dark:border-white/8">
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Models</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/4">
                            @foreach($sessions as $session)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-50">#{{ $session->id }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ implode(', ', $session->models) }}</td>
                                <td class="px-4 py-3">
                                    <x-ai-orbit::badge :variant="$session->status === 'completed' ? 'success' : 'warning'">
                                        {{ ucfirst($session->status) }}
                                    </x-ai-orbit::badge>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $session->created_at->format('M d, Y H:i') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('orbit.arena.show', $session->id) }}" class="text-orbit-500 hover:text-orbit-600 dark:text-orbit-400 dark:hover:text-orbit-300 text-sm font-medium">View</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ai-orbit::card>
            {{ $sessions->links() }}
        </div>
        @endif
    </div>
</x-ai-orbit::layout>

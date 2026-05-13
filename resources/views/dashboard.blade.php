<x-ai-orbit::layout>
    @slot('breadcrumb', 'Dashboard')

    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-50 tracking-tight">Dashboard</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Overview of your AI agent activity today.</p>
        </div>

        <livewire:ai-orbit.today-stats />

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <a href="{{ route('orbit.conversations.index') }}" class="quick-link-indigo">
                <div class="w-10 h-10 icon-container-indigo flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-indigo-600 dark:text-indigo-300">Conversations</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Browse and inspect conversations</p>
                </div>
            </a>

            <a href="{{ route('orbit.playground.index') }}" class="quick-link-emerald">
                <div class="w-10 h-10 icon-container-emerald flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-emerald-600 dark:text-emerald-300">Playground</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Test agents in an interactive sandbox</p>
                </div>
            </a>

            <a href="{{ route('orbit.usage.index') }}" class="quick-link-purple">
                <div class="w-10 h-10 icon-container-purple flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-purple-600 dark:text-purple-300">Usage</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Track token consumption and costs</p>
                </div>
            </a>
        </div>
    </div>
</x-ai-orbit::layout>

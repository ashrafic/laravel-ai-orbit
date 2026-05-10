<x-laravel-ai-orbit::layout>
    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Dashboard</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Overview of your AI agent activity today.</p>
        </div>

        <livewire:orbit.today-stats />

        {{-- Quick Links --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="{{ route('orbit.conversations.index') }}"
               class="block p-4 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 hover:border-orbit-300 dark:hover:border-orbit-700 transition-colors shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="p-2 bg-blue-100 dark:bg-blue-900/40 rounded-lg text-blue-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Conversations</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Browse and inspect conversations</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('orbit.playground.index') }}"
               class="block p-4 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 hover:border-orbit-300 dark:hover:border-orbit-700 transition-colors shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="p-2 bg-green-100 dark:bg-green-900/40 rounded-lg text-green-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Playground</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Test agents in an interactive sandbox</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('orbit.usage.index') }}"
               class="block p-4 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 hover:border-orbit-300 dark:hover:border-orbit-700 transition-colors shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="p-2 bg-purple-100 dark:bg-purple-900/40 rounded-lg text-purple-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Usage</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Track token consumption and costs</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</x-laravel-ai-orbit::layout>

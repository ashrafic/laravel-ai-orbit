<x-ai-orbit::layout>
    @slot('breadcrumb', 'Dashboard')

    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-50 tracking-tight">Dashboard</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Overview of your AI agent activity today.</p>
        </div>

        <livewire:ai-orbit.today-stats />

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
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

            <a href="{{ route('orbit.usage.pricing') }}" class="quick-link-purple">
                <div class="w-10 h-10 icon-container-purple flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-purple-600 dark:text-purple-300">Pricing</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Editable per-model token costs</p>
                </div>
            </a>

            <a href="{{ route('orbit.runs.index') }}" class="quick-link-cyan">
                <div class="w-10 h-10 icon-container-cyan flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-cyan-600 dark:text-cyan-300">Runs</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Inspect SDK invocation records</p>
                </div>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <a href="{{ route('orbit.usage.index') }}"
               class="quick-link-indigo block p-4">
                <div class="flex items-center gap-3">
                    <span class="icon-container-indigo">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-50">Usage</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Cost and token analytics</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('orbit.usage.alerts') }}"
               class="quick-link-purple block p-4">
                <div class="flex items-center gap-3">
                    <span class="icon-container-purple">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-50">Budget Alerts</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Spending notifications</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('orbit.usage.health') }}"
               class="block p-4 glass-card hover:border-orbit-300/50 dark:hover:border-orbit-700/50 transition-colors">
                <div class="flex items-center gap-3">
                    <span class="p-2 bg-orange-100 dark:bg-orange-900/40 rounded-lg text-orange-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-50">Provider Health</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Provider reliability tracking</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</x-ai-orbit::layout>

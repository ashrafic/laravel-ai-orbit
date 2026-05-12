<x-ai-orbit::layout>
    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Usage</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Token consumption and agent activity analytics.</p>
        </div>

        {{-- Today's Stats --}}
        <livewire:ai-orbit.today-stats />

        {{-- Advanced Feature Links --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('orbit.usage.dashboard') }}"
               class="block p-4 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 hover:border-orbit-300 dark:hover:border-orbit-700 transition-colors shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="p-2 bg-blue-100 dark:bg-blue-900/40 rounded-lg text-blue-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Full Analytics</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Historical token usage trends</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('orbit.usage.pricing') }}"
               class="block p-4 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 hover:border-orbit-300 dark:hover:border-orbit-700 transition-colors shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="p-2 bg-green-100 dark:bg-green-900/40 rounded-lg text-green-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Pricing Matrix</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Editable per-model costs</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('orbit.usage.alerts') }}"
               class="block p-4 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 hover:border-orbit-300 dark:hover:border-orbit-700 transition-colors shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="p-2 bg-purple-100 dark:bg-purple-900/40 rounded-lg text-purple-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Budget Alerts</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Spending notifications</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('orbit.usage.health') }}"
               class="block p-4 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 hover:border-orbit-300 dark:hover:border-orbit-700 transition-colors shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="p-2 bg-orange-100 dark:bg-orange-900/40 rounded-lg text-orange-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Provider Health</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Provider reliability tracking</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</x-ai-orbit::layout>

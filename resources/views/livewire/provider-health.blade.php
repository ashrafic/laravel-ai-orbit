<div>
    {{-- Period Selector --}}
    <div class="flex items-center gap-1 mb-6">
        @foreach ($periods as $value => $label)
            <button
                wire:click="$set('period', '{{ $value }}')"
                wire:key="period-{{ $value }}"
                @class([
                    'px-3 py-1.5 text-xs font-medium rounded-md transition-colors',
                    'bg-orbit-500 text-white' => $period === $value,
                    'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' => $period !== $value,
                ])
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    @if($metrics->isNotEmpty())
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        @foreach($metrics as $metric)
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white capitalize">{{ $metric['provider'] }}</h3>
                <span @class([
                    'inline-flex px-2 py-0.5 rounded-full text-xs font-medium',
                    'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' => $metric['status'] === 'healthy',
                    'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300' => $metric['status'] === 'degraded',
                    'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' => $metric['status'] === 'unhealthy',
                ])>
                    {{ ucfirst($metric['status']) }}
                </span>
            </div>

            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Success Rate</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $metric['success_rate'] }}%</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Total Requests</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ number_format($metric['total_requests']) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Errors</span>
                    <span class="font-medium {{ $metric['error_count'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">{{ number_format($metric['error_count']) }}</span>
                </div>
            </div>

            {{-- Progress bar --}}
            <div class="mt-3 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                <div class="h-1.5 rounded-full {{ $metric['success_rate'] >= 95 ? 'bg-green-500' : ($metric['success_rate'] >= 80 ? 'bg-yellow-500' : 'bg-red-500') }}"
                     style="width: {{ $metric['success_rate'] }}%"></div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <x-ai-orbit::empty-state message="No provider data available for the selected period." />
    @endif
</div>

<div>
    {{-- Period Selector --}}
    <div class="period-selector flex items-center gap-1 mb-6">
        @foreach ($periods as $value => $label)
            <button
                wire:click="$set('period', '{{ $value }}')"
                wire:key="period-{{ $value }}"
                @class([
                    'period-btn px-3 py-1.5 text-xs font-medium rounded-md transition-colors',
                    'period-btn-active' => $period === $value,
                    'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/[0.02]' => $period !== $value,
                ])
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    @if($metrics->isNotEmpty())
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-8">
        @foreach($metrics as $metric)
        <x-ai-orbit::card>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-50 capitalize">{{ $metric['provider'] }}</h3>
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
                    <span class="font-medium text-gray-900 dark:text-gray-50">{{ $metric['success_rate'] }}%</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Total Requests</span>
                    <span class="font-medium text-gray-900 dark:text-gray-50">{{ number_format($metric['total_requests']) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Errors</span>
                    <span class="font-medium {{ $metric['error_count'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-50' }}">{{ number_format($metric['error_count']) }}</span>
                </div>
                @if (!empty($metric['avg_latency_ms']))
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Avg Latency</span>
                    <span class="font-medium text-gray-900 dark:text-gray-50">{{ number_format($metric['avg_latency_ms']) }}ms</span>
                </div>
                @endif
                @if (!empty($metric['latency_p95']))
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">P95 Latency</span>
                    <span class="font-medium text-gray-900 dark:text-gray-50">{{ number_format($metric['latency_p95']) }}ms</span>
                </div>
                @endif
                @if (!empty($metric['latency_p99']))
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">P99 Latency</span>
                    <span class="font-medium text-gray-900 dark:text-gray-50">{{ number_format($metric['latency_p99']) }}ms</span>
                </div>
                @endif
            </div>

            {{-- Progress bar --}}
            <div class="mt-3 w-full bg-gray-200/60 dark:bg-white/8 rounded-full h-1.5">
                <div class="h-1.5 rounded-full {{ $metric['success_rate'] >= 95 ? 'bg-green-500' : ($metric['success_rate'] >= 80 ? 'bg-yellow-500' : 'bg-red-500') }}"
                     style="width: {{ $metric['success_rate'] }}%"></div>
            </div>
        </x-ai-orbit::card>
        @endforeach
    </div>
    @else
    <x-ai-orbit::empty-state message="No provider data available for the selected period." />
    @endif
</div>

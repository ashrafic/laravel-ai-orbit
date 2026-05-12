<div>
    <div class="period-selector w-fit mb-5">
        @foreach ($periods as $value => $label)
            <button
                wire:click="$set('period', '{{ $value }}')"
                wire:key="period-{{ $value }}"
                @class([
                    'rounded-lg text-xs font-medium transition-all duration-150 cursor-pointer',
                    'period-btn-active' => $period === $value,
                    'px-3.5 py-1.5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' => $period !== $value,
                ])
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <x-ai-orbit::stat
            :value="$stats['total_conversations']"
            label="Conversations"
            color="blue"
            :trend="($stats['total_conversations'] > 0 ? '↑ Active today' : '')"
        >
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
            </x-slot:icon>
        </x-ai-orbit::stat>

        <x-ai-orbit::stat
            :value="$stats['total_messages']"
            label="Messages"
            color="green"
        >
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
            </x-slot:icon>
        </x-ai-orbit::stat>

        <x-ai-orbit::stat
            :value="number_format($stats['input_tokens'] / 1000000, 1) . 'M'"
            label="Input Tokens"
            color="purple"
        >
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"></path></svg>
            </x-slot:icon>
        </x-ai-orbit::stat>

        <x-ai-orbit::stat
            :value="number_format($stats['output_tokens'] / 1000, 0) . 'K'"
            label="Output Tokens"
            color="orange"
        >
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </x-slot:icon>
        </x-ai-orbit::stat>
    </div>

    @if ($breakdown->isNotEmpty())
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <x-ai-orbit::card title="Agent Token Breakdown" padding="p-4">
                <div class="h-64">
                    <canvas id="agentBreakdownChart"></canvas>
                </div>
            </x-ai-orbit::card>

            <x-ai-orbit::card padding="p-0">
                <div class="px-4 py-3 border-b border-gray-200/60 dark:border-white/8">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Agent Summary</h3>
                </div>
                <div class="divide-y divide-gray-200/60 dark:divide-white/8">
                    @foreach ($breakdown as $item)
                        @php
                            $shortName = class_basename($item->agent);
                            $total = $item->input_tokens + $item->output_tokens;
                        @endphp
                        <div class="flex items-center justify-between px-4 py-3">
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $shortName }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ $item->message_count }} messages</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-50">{{ number_format($total) }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">
                                    <span class="text-purple-500">{{ number_format($item->input_tokens) }} in</span>
                                    <span class="mx-1">&middot;</span>
                                    <span class="text-amber-500">{{ number_format($item->output_tokens) }} out</span>
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-ai-orbit::card>
        </div>

        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
            <script>
                document.addEventListener('livewire:navigated', function () {
                    const ctx = document.getElementById('agentBreakdownChart');
                    if (!ctx) return;

                    const isDark = document.documentElement.classList.contains('dark');

                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: {!! json_encode($breakdown->map(fn($i) => class_basename($i->agent))) !!},
                            datasets: [{
                                data: {!! json_encode($breakdown->map(fn($i) => $i->input_tokens + $i->output_tokens)) !!},
                                backgroundColor: ['#6366f1', '#818cf8', '#a5b4fc', '#8b5cf6', '#a78bfa', '#c4b5fd', '#10b981'],
                                borderColor: isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.04)',
                                borderWidth: 2,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        color: isDark ? '#9ca3af' : '#64748b',
                                        padding: 16,
                                        font: { size: 12 }
                                    }
                                }
                            },
                            cutout: '65%',
                        }
                    });
                });
            </script>
        @endpush
    @else
        <x-ai-orbit::empty-state title="No data for {{ strtolower($periods[$period]) }}">
            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">
                Agent activity will appear here once conversations are recorded.
            </p>
        </x-ai-orbit::empty-state>
    @endif
</div>

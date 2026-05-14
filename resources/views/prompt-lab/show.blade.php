<x-ai-orbit::layout>
    @slot('breadcrumb', 'Prompt Lab Session')

    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('orbit.prompt-lab.index') }}" class="text-sm text-orbit-500 hover:text-orbit-600 dark:text-orbit-400 dark:hover:text-orbit-300 font-medium">&larr; Back to Prompt Lab</a>
        </div>

        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-50 tracking-tight">Session #{{ $session->id }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $session->created_at->format('M d, Y H:i') }}
                &middot;
                {{ collect($session->slots)->pluck('model')->implode(', ') }}
            </p>
        </div>

        <x-ai-orbit::card>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-50 mb-2">System Prompt</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 whitespace-pre-wrap">{{ $session->system_prompt }}</p>
        </x-ai-orbit::card>

        <x-ai-orbit::card>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-50 mb-2">Instruction</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 whitespace-pre-wrap">{{ $session->prompt }}</p>
        </x-ai-orbit::card>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($session->results as $index => $result)
            <x-ai-orbit::card padding="p-0" class="{{ !$result['success'] ? '!border-red-300/50 dark:!border-red-700/50' : '' }}">
                <div class="px-4 py-3 border-b border-gray-200/60 dark:border-white/8 flex items-center justify-between">
                    <div>
                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-50">{{ $result['model'] }}</span>
                        <span class="ml-1.5 text-xs text-gray-400 dark:text-gray-500">{{ $result['provider'] ?? '' }}</span>
                    </div>
                    @if($result['success'])
                        @if(isset($session->tags[$result['model']]))
                            <div class="flex gap-1">
                            @foreach($session->tags[$result['model']] as $tag)
                                <x-ai-orbit::badge variant="info">{{ $tag }}</x-ai-orbit::badge>
                            @endforeach
                            </div>
                        @endif
                    @else
                        <x-ai-orbit::badge variant="danger">Error</x-ai-orbit::badge>
                    @endif
                </div>
                <div class="p-4">
                    @if($result['success'])
                        <p class="text-sm text-gray-500 dark:text-gray-400 whitespace-pre-wrap">{{ $result['content'] }}</p>
                    @else
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $result['error'] ?? 'Unknown error' }}</p>
                    @endif
                </div>
                <div class="px-4 py-2 bg-gray-50/50 dark:bg-white/[0.02] text-xs text-gray-500 dark:text-gray-400 flex justify-between">
                    <span>{{ $result['latency_ms'] }}ms</span>
                    <span>{{ $result['tokens'] }} tokens</span>
                </div>
            </x-ai-orbit::card>
            @endforeach
        </div>
    </div>
</x-ai-orbit::layout>

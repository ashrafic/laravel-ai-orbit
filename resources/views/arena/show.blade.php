<x-ai-orbit::layout>
    @slot('breadcrumb', 'Arena Session')

    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('orbit.arena.index') }}" class="text-sm text-orbit-500 hover:text-orbit-600 dark:text-orbit-400 dark:hover:text-orbit-300 font-medium">&larr; Back to Arena</a>
        </div>

        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-50 tracking-tight">Session #{{ $session->id }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $session->created_at->format('M d, Y H:i') }} &middot; {{ implode(', ', $session->models) }}</p>
        </div>

        <x-ai-orbit::card>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-50 mb-2">Prompt</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 whitespace-pre-wrap">{{ $session->prompt }}</p>
        </x-ai-orbit::card>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($session->results as $model => $result)
            <x-ai-orbit::card padding="p-0" class="{{ !$result['success'] ? '!border-red-300/50 dark:!border-red-700/50' : '' }}">
                <div class="px-4 py-3 border-b border-gray-200/60 dark:border-white/8">
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-50">{{ $model }}</span>
                </div>
                <div class="p-4">
                    @if($result['success'])
                        <p class="text-sm text-gray-500 dark:text-gray-400 whitespace-pre-wrap">{{ $result['content'] }}</p>
                    @else
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $result['error'] ?? 'Unknown error' }}</p>
                    @endif
                </div>
                <div class="px-4 py-2 bg-gray-50/50 dark:bg-white/[0.02] text-xs text-gray-500 dark:text-gray-400">
                    {{ $result['latency_ms'] }}ms &middot; {{ $result['tokens'] }} tokens
                </div>
            </x-ai-orbit::card>
            @endforeach
        </div>
    </div>
</x-ai-orbit::layout>

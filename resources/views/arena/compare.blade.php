<div>
    <x-ai-orbit::card>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50 tracking-tight mb-4">New Comparison</h2>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prompt</label>
            <textarea wire:model="prompt" rows="4"
                class="orbit-input w-full"
                placeholder="Enter your prompt to compare across models..."></textarea>
            @error('prompt') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Models (select up to 3)</label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                @foreach($availableModels as $key => $label)
                <button wire:click="toggleModel('{{ $key }}')"
                    class="px-3 py-2 rounded-lg border text-sm font-medium transition
                    {{ in_array($key, $selectedModels) ? 'border-orbit-300/50 bg-orbit-50 dark:bg-orbit-900/30 text-orbit-700 dark:text-orbit-300' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/[0.02]' }}">
                    {{ $label }}
                </button>
                @endforeach
            </div>
            @error('selectedModels') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <button wire:click="runComparison" wire:loading.attr="disabled"
            class="orbit-btn-primary w-full sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed">
            <span wire:loading.remove wire:target="runComparison">Run Comparison</span>
            <span wire:loading wire:target="runComparison">Running...</span>
        </button>
    </x-ai-orbit::card>

    @if($results)
    <div class="mt-8">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50 tracking-tight mb-4">Results</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($results as $model => $result)
            <x-ai-orbit::card padding="p-0" class="{{ !$result['success'] ? '!border-red-300/50 dark:!border-red-700/50' : '' }}">
                <div class="px-4 py-3 border-b border-gray-200/60 dark:border-white/8 flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-50">{{ $result['model'] }}</span>
                    @if($result['success'])
                        @if(isset($autoTags[$model]))
                            @foreach($autoTags[$model] as $tag)
                                <x-ai-orbit::badge variant="info">{{ $tag }}</x-ai-orbit::badge>
                            @endforeach
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
    @endif
</div>

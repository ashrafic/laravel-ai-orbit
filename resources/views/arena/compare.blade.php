<div>
    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">New Comparison</h2>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prompt</label>
            <textarea wire:model="prompt" rows="4"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                placeholder="Enter your prompt to compare across models..."></textarea>
            @error('prompt') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Models (select up to 3)</label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                @foreach($availableModels as $key => $label)
                <button wire:click="toggleModel('{{ $key }}')"
                    class="px-3 py-2 rounded-lg border text-sm font-medium transition
                    {{ in_array($key, $selectedModels) ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                    {{ $label }}
                </button>
                @endforeach
            </div>
            @error('selectedModels') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <button wire:click="runComparison" wire:loading.attr="disabled"
            class="w-full sm:w-auto px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg disabled:opacity-50 disabled:cursor-not-allowed">
            <span wire:loading.remove wire:target="runComparison">Run Comparison</span>
            <span wire:loading wire:target="runComparison">Running...</span>
        </button>
    </div>

    @if($results)
    <div class="mt-8">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Results</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($results as $model => $result)
            <div class="rounded-lg border {{ $result['success'] ? 'border-gray-200 dark:border-gray-700' : 'border-red-300 dark:border-red-700' }} bg-white dark:bg-gray-900 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $result['model'] }}</span>
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
                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $result['content'] }}</p>
                    @else
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $result['error'] ?? 'Unknown error' }}</p>
                    @endif
                </div>
                <div class="px-4 py-2 bg-gray-50 dark:bg-gray-800 text-xs text-gray-500 dark:text-gray-400 flex justify-between">
                    <span>{{ $result['latency_ms'] }}ms</span>
                    <span>{{ $result['tokens'] }} tokens</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

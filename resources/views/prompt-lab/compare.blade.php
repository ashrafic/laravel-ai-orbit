<div>
    <x-ai-orbit::card>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50 tracking-tight mb-6">Agent Configuration</h2>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">System Prompt</label>
                <textarea wire:model="systemPrompt" rows="3"
                    class="orbit-input w-full"
                    placeholder="You are a helpful assistant..."></textarea>
                @error('systemPrompt') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Instruction / User Prompt</label>
                <textarea wire:model="prompt" rows="3"
                    class="orbit-input w-full"
                    placeholder="Write a poem about..."></textarea>
                @error('prompt') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Temperature: {{ number_format($temperature, 1) }}
                    </label>
                    <input type="range" wire:model.live="temperature" min="0" max="2" step="0.1"
                        class="w-full accent-orbit-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Max Tokens</label>
                    <input type="number" wire:model="maxTokens"
                        class="orbit-input w-full"
                        placeholder="Model default">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Top P: {{ number_format($topP, 1) }}
                    </label>
                    <input type="range" wire:model.live="topP" min="0" max="1" step="0.1"
                        class="w-full accent-orbit-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Context / History <span class="text-gray-400 font-normal">(optional, JSON array of prior messages)</span>
                </label>
                <textarea wire:model="context" rows="3"
                    class="orbit-input w-full font-mono text-xs"
                    placeholder='[{"role": "user", "content": "Hello"}, {"role": "assistant", "content": "Hi!"}]'></textarea>
            </div>
        </div>
    </x-ai-orbit::card>

    <div class="mt-6">
        <x-ai-orbit::card>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50 tracking-tight mb-1">Compare Models</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Select up to 3 provider and model pairs.</p>

            <div class="space-y-3">
                @foreach($slots as $index => $slot)
                <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-200/60 dark:border-white/8 bg-gray-50/50 dark:bg-white/[0.02]">
                    <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 w-5">{{ $index + 1 }}</span>
                    <div class="flex-1 grid grid-cols-2 gap-3">
                        <select wire:model.live="slots.{{ $index }}.provider"
                            class="orbit-input text-sm">
                            <option value="">Provider...</option>
                            @foreach($configuredProviders as $provider)
                                <option value="{{ $provider }}">{{ ucfirst($provider) }}</option>
                            @endforeach
                        </select>
                        <select wire:model="slots.{{ $index }}.model"
                            class="orbit-input text-sm">
                            <option value="">Model...</option>
                            @if(isset($modelsForProvider[$index]))
                                <option value="{{ $modelsForProvider[$index]['smartest'] }}">
                                    {{ $modelsForProvider[$index]['smartest'] }} (smartest)
                                </option>
                                <option value="{{ $modelsForProvider[$index]['default'] }}">
                                    {{ $modelsForProvider[$index]['default'] }} (default)
                                </option>
                                <option value="{{ $modelsForProvider[$index]['cheapest'] }}">
                                    {{ $modelsForProvider[$index]['cheapest'] }} (cheapest)
                                </option>
                            @endif
                            <option value="__custom__">Custom…</option>
                        </select>
                    </div>
                </div>
                @endforeach
            </div>
            @error('slots') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

            <button wire:click="runComparison" wire:loading.attr="disabled"
                class="orbit-btn-primary w-full sm:w-auto mt-4 disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="runComparison">Run Comparison</span>
                <span wire:loading wire:target="runComparison">Running...</span>
            </button>
        </x-ai-orbit::card>
    </div>

    @if($results)
    <div class="mt-8">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50 tracking-tight mb-4">Results</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($results as $index => $result)
            <x-ai-orbit::card padding="p-0" class="{{ !$result['success'] ? '!border-red-300/50 dark:!border-red-700/50' : '' }}">
                <div class="px-4 py-3 border-b border-gray-200/60 dark:border-white/8 flex items-center justify-between">
                    <div>
                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-50">{{ $result['model'] }}</span>
                        <span class="ml-1.5 text-xs text-gray-400 dark:text-gray-500">{{ $result['provider'] }}</span>
                    </div>
                    @if($result['success'])
                        @if(isset($autoTags[$result['model']]))
                            <div class="flex gap-1">
                            @foreach($autoTags[$result['model']] as $tag)
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
    @endif
</div>

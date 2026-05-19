<div>
    {{-- Saved Prompts Quick Load --}}
    @if ($recentPrompts->isNotEmpty())
    <div class="mb-4" x-data="{ open: false }">
        <button @click="open = !open"
            class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
            <svg class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            Load from library ({{ $recentPrompts->count() }})
        </button>
        <div x-show="open" x-collapse class="mt-2">
            <div class="flex flex-wrap gap-2">
                @foreach ($recentPrompts as $saved)
                    <button wire:click="loadFromLibrary({{ $saved->id }})"
                        class="group flex items-center gap-2 px-3 py-1.5 rounded-lg border text-xs font-medium transition-all
                               border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800
                               text-gray-600 dark:text-gray-400 hover:border-orbit-300 dark:hover:border-orbit-700 hover:text-orbit-600 dark:hover:text-orbit-400">
                        <span class="max-w-[140px] truncate">{{ $saved->name }}</span>
                        <button wire:click.stop="deleteSaved({{ $saved->id }})" wire:confirm="Delete this saved prompt?"
                            class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-500 transition-all">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </button>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <x-ai-orbit::card>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50 tracking-tight">Agent Configuration</h2>
            <button wire:click="startSave"
                class="text-xs text-gray-400 hover:text-orbit-500 dark:hover:text-orbit-400 font-medium transition-colors flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                Save to library
            </button>
        </div>

        @if ($showSaveForm)
        <div class="mb-4 p-3 rounded-lg border border-orbit-200 dark:border-orbit-800 bg-orbit-50/50 dark:bg-orbit-900/10">
            <div class="flex items-center gap-2">
                <input wire:model="saveName" type="text"
                    class="orbit-input flex-1 text-sm"
                    placeholder="Give this prompt a name...">
                <button wire:click="saveToLibrary"
                    class="orbit-btn-primary text-xs px-3 py-1.5">Save</button>
                <button wire:click="cancelSave"
                    class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 px-2">Cancel</button>
            </div>
            @error('saveName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        @endif

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
                @foreach($modelSlots as $index => $slot)
                <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-200/60 dark:border-white/8 bg-gray-50/50 dark:bg-white/[0.02]">
                    <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 w-5">{{ $index + 1 }}</span>
                    <div class="flex-1 grid grid-cols-2 gap-3">
                        <select wire:model.live="modelSlots.{{ $index }}.provider"
                            class="orbit-input text-sm">
                            <option value="">Provider...</option>
                            @foreach($configuredProviders as $provider)
                                <option value="{{ $provider }}">{{ ucfirst($provider) }}</option>
                            @endforeach
                        </select>
                        <input type="text" wire:model="modelSlots.{{ $index }}.model"
                            class="orbit-input text-sm"
                            placeholder="e.g. gpt-5.4">
                    </div>
                </div>
                @endforeach
            </div>
            @error('modelSlots') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

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

<div class="flex flex-col h-[calc(100vh-10rem)]"
    x-data="{ optimisticMessage: '' }"
    x-init="
        $wire.$watch('history', () => {
            $nextTick(() => {
                const el = document.getElementById('sandbox-messages');
                if (el) el.scrollTop = el.scrollHeight;
            });
        });
    ">
    {{-- Dependency Resolution Panel --}}
    @if ($needsInput && count($constructorParams) > 0)
    <div class="flex-shrink-0 mb-3 rounded-lg border border-amber-200 dark:border-amber-800/50 overflow-hidden"
         x-data="{
            open: {{ $simulationMode === 'pending' ? 'true' : 'false' }},
            get filledCount() {
                return [...document.querySelectorAll('[data-param-input]')].filter(el => el.value).length;
            }
         }">
        <button @click="open = !open"
            class="w-full flex items-center gap-2.5 px-3 py-2.5 text-xs font-medium
                   bg-amber-50/50 dark:bg-amber-900/10
                   text-amber-700 dark:text-amber-300
                   hover:bg-amber-100/50 dark:hover:bg-amber-900/20
                   transition-colors duration-150 group">
            <span class="flex-shrink-0 w-6 h-6 rounded-md bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center
                        group-hover:bg-amber-200 dark:group-hover:bg-amber-900/50 transition-colors">
                <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </span>
            <span class="flex-1 text-left">
                Configure agent context
                <span class="text-amber-600/60 dark:text-amber-400/60 font-normal ml-1">({{ count($constructorParams) }} parameter{{ count($constructorParams) !== 1 ? 's' : '' }})</span>
            </span>
            @if ($simulationMode === 'ready')
                <span class="px-1.5 py-0.5 text-[10px] font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full">Ready</span>
            @elseif ($simulationMode === 'pending')
                <span class="px-1.5 py-0.5 text-[10px] font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 rounded-full">Required</span>
            @endif
            <svg class="w-3.5 h-3.5 flex-shrink-0 text-amber-400/60 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        <div x-show="open" x-collapse>
            <div class="bg-amber-50/30 dark:bg-amber-900/5 p-4 space-y-3">
                @foreach ($constructorParams as $param)
                    @if ($param['strategy'] === 'eloquent_picker')
                    <div class="glass-card rounded-lg p-3">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1.5">
                            {{ $param['label'] }}
                            <span class="font-normal text-gray-400 ml-1">Eloquent Model</span>
                        </label>
                        <select wire:model.live="paramInputs.{{ $param['name'] }}"
                            data-param-input
                            class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg
                                   bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                                   focus:ring-2 focus:ring-orbit-500 focus:border-orbit-500 transition-shadow">
                            <option value="">Select a {{ $param['label'] }}...</option>
                            @foreach ($this->getModelRecords($param['type']) as $record)
                                <option value="{{ $record->getKey() }}">
                                    #{{ $record->getKey() }}
                                    @foreach ($this->getDisplayValues($record) as $val)
                                        — {{ $val }}
                                    @endforeach
                                    @if (method_exists($record, 'created_at') && $record->created_at)
                                        — {{ $record->created_at->diffForHumans() }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @elseif ($param['strategy'] === 'input')
                    <div class="glass-card rounded-lg p-3">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1.5">
                            {{ $param['name'] }}
                            <span class="font-normal text-gray-400 ml-1">{{ $param['type'] }}</span>
                        </label>
                        <input wire:model.live="paramInputs.{{ $param['name'] }}"
                            data-param-input
                            type="{{ $param['input_type'] ?? 'text' }}"
                            placeholder="Enter value..."
                            class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg
                                   bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                                   focus:ring-2 focus:ring-orbit-500 focus:border-orbit-500 transition-shadow" />
                    </div>
                    @elseif ($param['strategy'] === 'default')
                    <div class="glass-card rounded-lg p-3">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1.5">
                            {{ $param['name'] }}
                            <span class="font-normal text-gray-400 ml-1">{{ $param['type'] }}</span>
                            <span class="text-gray-400 ml-2">— default: {{ is_scalar($param['default']) ? (string) $param['default'] : json_encode($param['default']) }}</span>
                        </label>
                        <input wire:model.live="paramInputs.{{ $param['name'] }}"
                            data-param-input
                            type="{{ $param['input_type'] ?? 'text' }}"
                            placeholder="{{ is_scalar($param['default']) ? (string) $param['default'] : '' }}"
                            class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg
                                   bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                                   focus:ring-2 focus:ring-orbit-500 focus:border-orbit-500 transition-shadow" />
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Simulation Mode Badge --}}
    <div class="flex-shrink-0 flex items-center gap-2 mb-3 text-xs">
        @if ($simulationMode === 'full' || $simulationMode === 'ready')
            <span class="px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full font-medium">
                Full Simulation
            </span>
        @elseif ($simulationMode === 'prompt_only')
            <span class="px-2 py-0.5 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 rounded-full font-medium">
                Prompt Only
            </span>
            <span class="text-gray-500 dark:text-gray-400">Tools and structured output disabled</span>
        @elseif ($simulationMode === 'pending')
            <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded-full font-medium">
                Waiting for context
            </span>
        @endif
    </div>

    {{-- Chat Messages --}}
    <div class="flex-1 overflow-y-auto space-y-3 mb-4 pr-2" id="sandbox-messages">
        @if (empty($history))
            <div wire:loading.remove wire:target="send" class="flex items-center justify-center h-full">
                <x-ai-orbit::empty-state title="Start a conversation"
                    description="Send a message to begin chatting with this agent." />
            </div>
        @endif

        @foreach ($history as $message)
            @if ($message['role'] === 'user')
                <div class="flex justify-end">
                    <div class="max-w-[80%] bg-gradient-to-br from-orbit-500 to-orbit-600 text-white rounded-xl px-4 py-3">
                        <p class="text-sm whitespace-pre-wrap">{{ $message['content'] }}</p>
                    </div>
                </div>
            @elseif ($message['role'] === 'error')
                <div class="flex justify-center">
                    <div class="max-w-[80%] glass-card border-red-200/50 dark:border-red-800/50 text-red-700 dark:text-red-300 rounded-xl px-4 py-3">
                        <p class="text-sm font-mono whitespace-pre-wrap">{{ $message['content'] }}</p>
                    </div>
                </div>
            @elseif ($message['role'] === 'warning')
                <div class="flex justify-center">
                    <div class="max-w-[80%] bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300 rounded-xl px-4 py-3">
                        <p class="text-sm whitespace-pre-wrap">{{ $message['content'] }}</p>
                    </div>
                </div>
            @elseif ($message['role'] === 'tool_call')
                <div class="flex justify-start"
                     x-data="{ open: false }">
                    <div class="max-w-[80%] glass-card border border-purple-200/40 dark:border-purple-800/40 rounded-xl overflow-hidden">
                        <button @click="open = !open"
                            class="w-full px-4 py-2.5 flex items-center gap-2 text-xs font-medium text-purple-600 dark:text-purple-300 hover:bg-purple-50/50 dark:hover:bg-purple-900/30 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span>Tool call: <span class="font-mono">{{ $message['content'] }}</span></span>
                            <svg class="w-3 h-3 ml-auto transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" x-collapse>
                            <div class="px-4 py-3 border-t border-purple-200/30 dark:border-purple-800/30">
                                <div class="rounded-lg bg-gray-900/50 dark:bg-black/40 border border-gray-700/30 dark:border-gray-600/20">
                                    <div class="px-3 pt-2">
                                        <span class="text-[10px] text-gray-500 font-mono uppercase tracking-wider">Arguments</span>
                                    </div>
                                    <pre class="p-3 text-xs font-mono text-gray-200 whitespace-pre-wrap overflow-x-auto">{{ $message['arguments'] ?? '{}' }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif ($message['role'] === 'tool_result')
                <div class="flex justify-start"
                     x-data="{ open: false }">
                    <div class="max-w-[80%] glass-card border border-blue-200/40 dark:border-blue-800/40 rounded-xl overflow-hidden">
                        <button @click="open = !open"
                            class="w-full px-4 py-2.5 flex items-center gap-2 text-xs font-medium text-blue-600 dark:text-blue-300 hover:bg-blue-50/50 dark:hover:bg-blue-900/30 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Tool result
                            @if (!empty($message['name'] ?? ''))
                                from <span class="font-mono">{{ $message['name'] }}</span>
                            @endif
                            </span>
                            <svg class="w-3 h-3 ml-auto transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" x-collapse>
                            <div class="px-4 py-3 border-t border-blue-200/30 dark:border-blue-800/30">
                                <div class="rounded-lg bg-gray-900/50 dark:bg-black/40 border border-gray-700/30 dark:border-gray-600/20">
                                    <div class="px-3 pt-2">
                                        <span class="text-[10px] text-gray-500 font-mono uppercase tracking-wider">Output</span>
                                    </div>
                                    <pre class="p-3 text-xs font-mono text-gray-200 whitespace-pre-wrap overflow-x-auto">{{ $message['content'] }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                @php
                    $jsonContent = null;
                    $decoded = json_decode($message['content'], true);
                    $isJson = json_last_error() === JSON_ERROR_NONE && (is_array($decoded) || is_object($decoded));
                    if ($isJson) {
                        $jsonContent = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    }
                @endphp
                @if ($jsonContent !== null)
                    <div class="flex justify-start"
                         x-data="{ open: true }">
                        <div class="max-w-[80%] glass-card rounded-xl overflow-hidden">
                            <button @click="open = !open"
                                class="w-full px-3 py-2 flex items-center gap-2 text-xs font-medium text-green-600 dark:text-green-300 hover:bg-green-50/50 dark:hover:bg-green-900/30 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                                </svg>
                                <span>Structured Output</span>
                                <span class="text-[10px] text-green-500/70 font-mono">JSON</span>
                                <svg class="w-3 h-3 ml-auto transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="open" x-collapse>
                                <div class="rounded-lg bg-gray-900/50 dark:bg-black/40 border border-gray-700/30 dark:border-gray-600/20">
                                    <pre class="p-3 text-xs font-mono text-gray-200 whitespace-pre-wrap overflow-x-auto">{{ $jsonContent }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="flex justify-start">
                        <div class="max-w-[80%] glass-card text-gray-900 dark:text-gray-50 rounded-xl px-4 py-3">
                            <p class="text-sm whitespace-pre-wrap">{{ $message['content'] }}</p>
                        </div>
                    </div>
                @endif
            @endif
        @endforeach

        {{-- Optimistic user message (appears at bottom before server re-renders) --}}
        <template x-if="optimisticMessage && $wire.sending">
            <div class="flex justify-end">
                <div class="max-w-[80%] bg-gradient-to-br from-orbit-500 to-orbit-600 text-white rounded-xl px-4 py-3">
                    <p class="text-sm whitespace-pre-wrap" x-text="optimisticMessage"></p>
                </div>
            </div>
        </template>

        <div wire:loading wire:target="send" class="flex justify-start">
            <div class="glass-card rounded-xl px-4 py-3">
                <div class="flex items-center gap-1">
                    <span class="w-2 h-2 bg-gray-400 dark:bg-gray-500 rounded-full animate-pulse"></span>
                    <span class="w-2 h-2 bg-gray-400 dark:bg-gray-500 rounded-full animate-pulse" style="animation-delay: 0.2s;"></span>
                    <span class="w-2 h-2 bg-gray-400 dark:bg-gray-500 rounded-full animate-pulse" style="animation-delay: 0.4s;"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Input --}}
    <div class="flex-shrink-0">
        @if ($error)
            <div class="mb-2 p-2 glass-card border-red-200/50 dark:border-red-800/50 text-xs text-red-600 dark:text-red-400">
                <p class="font-medium">{{ $error }}</p>
                <p class="mt-1 text-red-400 dark:text-red-500">
                    This is likely an issue with your agent class. Check your agent's implementation.
                </p>
            </div>
        @endif

        <form wire:submit.prevent="send"
              @submit="optimisticMessage = $refs.promptInput.value"
              class="flex gap-2">
            <textarea
                wire:model="prompt"
                x-ref="promptInput"
                rows="1"
                placeholder="Type a message..."
                wire:loading.attr="disabled" wire:target="send"
                :disabled="$wire.simulationMode === 'pending' && $wire.needsInput"
                @keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); optimisticMessage = $refs.promptInput.value; $wire.send(); }"
                class="orbit-input flex-1 disabled:opacity-50 resize-none"
            ></textarea>
            <button
                type="submit"
                wire:loading.attr="disabled" wire:target="send"
                :disabled="$wire.prompt === '' || ($wire.simulationMode === 'pending' && $wire.needsInput)"
                class="orbit-btn-primary disabled:opacity-50 disabled:cursor-not-allowed transition-colors inline-flex items-center justify-center gap-1.5 flex-shrink-0 w-[115px]"
            >
                <span wire:loading.remove wire:target="send" class="whitespace-nowrap">Send</span>
                <span wire:loading wire:target="send" class="inline-flex items-center gap-1.5 whitespace-nowrap">
                    <svg class="w-3.5 h-3.5 animate-spin flex-shrink-0" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Sending...
                </span>
            </button>
            @if (!empty($history))
                <button
                    wire:click="clear"
                    type="button"
                    class="orbit-btn-secondary whitespace-nowrap"
                >
                    Clear
                </button>
            @endif
        </form>
    </div>
</div>

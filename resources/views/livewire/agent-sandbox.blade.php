<div class="flex flex-col h-[calc(100vh-10rem)]">
    {{-- Section A: Dependency Resolution Panel --}}
    @if ($needsInput && $simulationMode === 'pending')
    <div class="flex-shrink-0 mb-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
        <h3 class="text-sm font-semibold text-amber-800 dark:text-amber-200 mb-2 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            This agent requires context to run
        </h3>
        <p class="text-xs text-amber-600 dark:text-amber-400 mb-3">
            Select the data this agent needs for a full simulation.
        </p>

        @foreach ($constructorParams as $param)
            @if ($param['strategy'] === 'eloquent_picker')
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ $param['label'] }}
                </label>
                <select wire:model.live="paramInputs.{{ $param['name'] }}"
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg
                           bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                           focus:ring-2 focus:ring-orbit-500 focus:border-orbit-500">
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
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ $param['name'] }} <span class="text-gray-400">({{ $param['type'] }})</span>
                </label>
                <input wire:model.live="paramInputs.{{ $param['name'] }}"
                    type="{{ $param['input_type'] ?? 'text' }}"
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg
                           bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                           focus:ring-2 focus:ring-orbit-500 focus:border-orbit-500" />
            </div>
            @elseif ($param['strategy'] === 'default')
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ $param['name'] }} <span class="text-gray-400">({{ $param['type'] }}, default: {{ json_encode($param['default']) }})</span>
                </label>
                <input wire:model.live="paramInputs.{{ $param['name'] }}"
                    type="{{ $param['input_type'] ?? 'text' }}"
                    placeholder="{{ is_scalar($param['default']) ? (string) $param['default'] : '' }}"
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg
                           bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                           focus:ring-2 focus:ring-orbit-500 focus:border-orbit-500" />
            </div>
            @endif
        @endforeach
    </div>
    @endif

    {{-- Section B: Simulation Mode Badge --}}
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

    {{-- Section C: Chat Messages --}}
    <div class="flex-1 overflow-y-auto space-y-3 mb-4 pr-2" id="sandbox-messages">
        @if (empty($history) && !$sending)
            <div class="flex items-center justify-center h-full">
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
                    <div class="max-w-[80%] bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-xl overflow-hidden">
                        <button @click="open = !open"
                            class="w-full px-4 py-2 flex items-center gap-2 text-xs font-medium text-purple-700 dark:text-purple-300 hover:bg-purple-100 dark:hover:bg-purple-900/40 transition-colors">
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
                            <div class="px-4 py-2 border-t border-purple-200 dark:border-purple-800">
                                <p class="text-xs text-purple-500 dark:text-purple-400 font-medium mb-1">Arguments</p>
                                <pre class="text-xs font-mono text-purple-800 dark:text-purple-200 whitespace-pre-wrap">{{ $message['arguments'] ?? '{}' }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif ($message['role'] === 'tool_result')
                <div class="flex justify-start"
                     x-data="{ open: false }">
                    <div class="max-w-[80%] bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl overflow-hidden">
                        <button @click="open = !open"
                            class="w-full px-4 py-2 flex items-center gap-2 text-xs font-medium text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors">
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
                            <div class="px-4 py-2 border-t border-blue-200 dark:border-blue-800">
                                <pre class="text-xs font-mono text-blue-800 dark:text-blue-200 whitespace-pre-wrap">{{ $message['content'] }}</pre>
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
        @endforeach

        @if ($sending)
            <div class="flex justify-start">
                <div class="glass-card rounded-xl px-4 py-3">
                    <div class="flex items-center gap-1">
                        <span class="w-2 h-2 bg-gray-400 dark:bg-gray-500 rounded-full animate-pulse"></span>
                        <span class="w-2 h-2 bg-gray-400 dark:bg-gray-500 rounded-full animate-pulse" style="animation-delay: 0.2s;"></span>
                        <span class="w-2 h-2 bg-gray-400 dark:bg-gray-500 rounded-full animate-pulse" style="animation-delay: 0.4s;"></span>
                    </div>
                </div>
            </div>
        @endif
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

        <form wire:submit="send" class="flex gap-2">
            <input
                wire:model="prompt"
                type="text"
                placeholder="Type a message..."
                :disabled="$wire.sending || ($wire.simulationMode === 'pending' && $wire.needsInput)"
                class="orbit-input flex-1 disabled:opacity-50"
            >
            <button
                type="submit"
                :disabled="$wire.sending || $wire.prompt === '' || ($wire.simulationMode === 'pending' && $wire.needsInput)"
                class="orbit-btn-primary disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-2"
            >
                @if ($sending)
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                @endif
                Send
            </button>
            @if (!empty($history))
                <button
                    wire:click="clear"
                    type="button"
                    class="orbit-btn-secondary"
                >
                    Clear
                </button>
            @endif
        </form>
    </div>
</div>

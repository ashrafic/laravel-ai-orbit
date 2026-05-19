<div class="space-y-6">
    {{-- Conversation Header --}}
    @if ($conversation)
        <x-ai-orbit::card>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-50 tracking-tight">{{ $conversation->title }}</h2>
                    <div class="flex flex-wrap items-center gap-2 mt-1">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ \Carbon\Carbon::parse($conversation->created_at)->format('M d, Y H:i') }}
                        </span>
                        @if (!empty($conversation->agent_class))
                            <span class="text-xs text-gray-500 dark:text-gray-400">&middot;</span>
                            <x-ai-orbit::badge :label="class_basename($conversation->agent_class)" color="blue" />
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        wire:click="toggleRawPayload"
                        class="orbit-btn-secondary"
                    >
                        {{ $showRawPayload ? 'Hide' : 'Show' }} Raw Payload
                    </button>
                </div>
            </div>
        </x-ai-orbit::card>
    @endif

    {{-- Messages Timeline --}}
    @if ($messages->isEmpty())
        <x-ai-orbit::empty-state title="No messages" description="This conversation has no recorded messages." />
    @else
        <div class="space-y-4">
            @foreach ($messages as $message)
                @php
                    $roleConfig = match ($message->role) {
                        'user' => [
                            'label' => 'User',
                            'dot' => 'bg-indigo-500',
                            'avatar' => 'user',
                            'alignment' => 'ml-auto',
                            'bubble' => 'ml-auto bg-gradient-to-br from-orbit-500 to-orbit-600 text-white',
                            'maxWidth' => 'max-w-2xl',
                        ],
                        'assistant' => [
                            'label' => 'Assistant',
                            'dot' => 'bg-gray-400 dark:bg-gray-500',
                            'avatar' => 'ai',
                            'alignment' => 'mr-auto',
                            'bubble' => 'mr-auto glass-card text-gray-900 dark:text-gray-50',
                            'maxWidth' => 'max-w-2xl',
                        ],
                        'system' => [
                            'label' => 'System',
                            'dot' => 'bg-yellow-500',
                            'avatar' => null,
                            'alignment' => 'mx-auto',
                            'bubble' => 'mx-auto glass-card border-yellow-200/30 dark:border-yellow-800/30 text-yellow-800 dark:text-yellow-200',
                            'maxWidth' => 'max-w-lg',
                        ],
                        'tool' => [
                            'label' => 'Tool',
                            'dot' => 'bg-purple-500',
                            'avatar' => null,
                            'alignment' => 'mr-auto',
                            'bubble' => 'mr-auto glass-card border-purple-200/30 dark:border-purple-800/30 text-purple-800 dark:text-purple-200',
                            'maxWidth' => 'max-w-2xl',
                        ],
                        default => [
                            'label' => ucfirst($message->role),
                            'dot' => 'bg-gray-400',
                            'avatar' => null,
                            'alignment' => 'mr-auto',
                            'bubble' => 'mr-auto glass-card text-gray-900 dark:text-gray-50',
                            'maxWidth' => 'max-w-2xl',
                        ],
                    };
                @endphp

                <div class="{{ $roleConfig['alignment'] }} {{ $roleConfig['maxWidth'] }}">
                    <div class="{{ $roleConfig['bubble'] }} rounded-xl p-4">
                        {{-- Message Header --}}
                        <div class="flex items-center gap-2 mb-2">
                            @if ($roleConfig['avatar'] === 'user')
                                <div class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                            @elseif ($roleConfig['avatar'] === 'ai')
                                <div class="w-5 h-5 rounded-full bg-orbit-500/20 dark:bg-orbit-400/20 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-3 h-3 text-orbit-500 dark:text-orbit-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                            <span class="text-xs font-semibold uppercase tracking-wider opacity-70 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full {{ $roleConfig['dot'] }}"></span>
                                {{ $roleConfig['label'] }}
                            </span>
                            <span class="text-xs opacity-50 ml-auto">{{ \Carbon\Carbon::parse($message->created_at)->format('H:i:s') }}</span>
                        </div>

                        {{-- Message Content --}}
                        @php
                            $trimmed = preg_replace('/^\s+|\s+$/u', '', $message->content ?? '');
                            $decodedContent = json_decode($trimmed, true);
                            $isJson = json_last_error() === JSON_ERROR_NONE && (is_array($decodedContent) || is_object($decodedContent));
                            if ($isJson) {
                                $prettyContent = json_encode($decodedContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                            }
                        @endphp

                        @if ($isJson)
                            <div x-data="{ open: true }">
                                <button @click="open = !open"
                                    class="flex items-center gap-2 text-xs font-medium text-green-600 dark:text-green-300 hover:text-green-700 dark:hover:text-green-200 transition-colors mb-2">
                                    <svg class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                    Structured Output (JSON)
                                </button>
                                <div x-show="open" x-collapse>
                                    <pre class="text-xs font-mono text-gray-200 bg-gray-900/80 dark:bg-black/50 rounded-lg p-3 overflow-x-auto whitespace-pre-wrap">{{ $prettyContent }}</pre>
                                </div>
                            </div>
                        @else
                            <div class="text-sm whitespace-pre-wrap {{ $message->role === 'system' ? 'font-mono' : '' }}">
                                {{ $trimmed }}
                            </div>
                        @endif

                        {{-- Tool Calls --}}
                        @if (!empty($message->tool_calls) && $message->tool_calls !== 'null')
                            @php
                                $toolCalls = json_decode($message->tool_calls, true) ?? [];
                            @endphp
                            @foreach ($toolCalls as $index => $toolCall)
                                <div
                                    x-data="{ open: false }"
                                    class="mt-3"
                                >
                                    <button
                                        @click="open = !open"
                                        class="w-full flex items-center gap-2 px-3 py-2 text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100 hover:bg-gray-100/50 dark:hover:bg-white/5 rounded-lg transition-colors"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543-.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span>Tool: <span class="font-mono">{{ $toolCall['function']['name'] ?? $toolCall['name'] ?? "Call #{$index}" }}</span></span>
                                        <svg class="w-3 h-3 ml-auto transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <div x-show="open" x-collapse>
                                        <div class="rounded-lg bg-gray-900/50 dark:bg-black/40 border border-gray-700/30 dark:border-gray-600/20 mx-1">
                                            <div class="px-3 pt-2">
                                                <span class="text-[10px] text-gray-500 font-mono uppercase tracking-wider">Arguments</span>
                                            </div>
                                            <pre class="p-3 text-xs font-mono text-gray-200 whitespace-pre-wrap overflow-x-auto">{!! $this->highlightJson($toolCall['function']['arguments'] ?? $toolCall['arguments'] ?? []) !!}</pre>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        {{-- Raw Payload --}}
                        @if ($showRawPayload)
                            <div
                                x-data="{ open: false }"
                                class="mt-3"
                            >
                                <button
                                    @click="open = !open"
                                    class="w-full flex items-center justify-between px-3 py-1.5 text-xs font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors rounded-t-lg"
                                >
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                        </svg>
                                        Raw JSON
                                    </span>
                                    <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <div x-show="open" x-collapse>
                                    <div class="rounded-b-lg bg-gray-900/50 dark:bg-black/40 border border-gray-700/30 dark:border-gray-600/20 overflow-hidden">
                                        <pre class="p-3 text-xs font-mono text-gray-200 overflow-x-auto whitespace-pre-wrap">{!! $this->highlightJson($message) !!}</pre>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

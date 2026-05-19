<x-ai-orbit::layout>
    @slot('breadcrumb', 'Execution Trace')

    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('orbit.conversations.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-50 tracking-tight">Execution Trace</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ \Illuminate\Support\Str::limit($conversation->title, 60) }}</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('orbit.conversations.show', $conversation->id) }}"
               class="text-sm text-orbit-500 hover:text-orbit-600 dark:text-orbit-400 dark:hover:text-orbit-300 font-medium">
                &larr; View Message Timeline
            </a>
        </div>

        {{-- Conversation Info --}}
        <x-ai-orbit::card>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Conversation ID</span>
                    <p class="font-mono text-gray-900 dark:text-gray-50">{{ \Illuminate\Support\Str::limit($conversation->id, 12, '') }}</p>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Messages</span>
                    <p class="text-gray-900 dark:text-gray-50">{{ $messages->count() }}</p>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Created</span>
                    <p class="text-gray-900 dark:text-gray-50">{{ \Carbon\Carbon::parse($conversation->created_at)->format('M d, Y H:i') }}</p>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Last Updated</span>
                    <p class="text-gray-900 dark:text-gray-50">{{ \Carbon\Carbon::parse($conversation->updated_at)->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </x-ai-orbit::card>

        {{-- Timeline --}}
        @if ($messages->isEmpty())
            <x-ai-orbit::empty-state title="No messages" description="This conversation has no recorded messages to trace." />
        @else
            <div class="space-y-4">
                @foreach ($messages as $index => $message)
                    @php
                        $previousMessage = $index > 0 ? $messages[$index - 1] : null;
                        $latency = $previousMessage
                            ? \Carbon\Carbon::parse($message->created_at)->diffInMilliseconds(\Carbon\Carbon::parse($previousMessage->created_at))
                            : null;

                        $stepLabel = match ($message->role) {
                            'user' => 'User Input',
                            'system' => 'System',
                            'assistant' => 'Agent Response',
                            'tool' => 'Tool Call',
                            default => ucfirst($message->role),
                        };

                        $stepColor = match ($message->role) {
                            'user' => 'border-l-blue-500',
                            'system' => 'border-l-yellow-500',
                            'assistant' => 'border-l-green-500',
                            'tool' => 'border-l-purple-500',
                            default => 'border-l-gray-500',
                        };

                        $trimmedContent = preg_replace('/^\s+|\s+$/u', '', $message->content ?? '');
                        $decoded = json_decode($trimmedContent, true);
                        $isJsonContent = json_last_error() === JSON_ERROR_NONE && (is_array($decoded) || is_object($decoded));
                        if ($isJsonContent) {
                            $prettyJson = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        }
                    @endphp

                    <div class="glass-card rounded-xl border-l-4 {{ $stepColor }} overflow-hidden">
                        {{-- Header --}}
                        <div class="px-4 py-3 flex items-center gap-3 border-b border-gray-100 dark:border-white/5">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                                #{{ $index + 1 }}
                            </span>
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">{{ $stepLabel }}</span>
                            <span class="text-xs text-gray-400 dark:text-gray-500 ml-auto">
                                {{ \Carbon\Carbon::parse($message->created_at)->format('H:i:s') }}
                                @if ($latency !== null)
                                    &middot; {{ number_format($latency) }}ms
                                @endif
                            </span>
                        </div>

                        {{-- Body --}}
                        <div class="px-4 py-3">
                            @if ($message->role === 'system')
                                <pre class="text-sm text-gray-600 dark:text-gray-400 font-mono whitespace-pre-wrap">{{ $trimmedContent }}</pre>
                            @elseif ($isJsonContent)
                                <div x-data="{ open: true }" class="space-y-2">
                                    <button @click="open = !open"
                                        class="flex items-center gap-2 text-xs font-medium text-green-600 dark:text-green-300 hover:text-green-700 dark:hover:text-green-200 transition-colors">
                                        <svg class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                        Structured Output (JSON)
                                    </button>
                                    <div x-show="open" x-collapse>
                                        <pre class="text-xs font-mono text-gray-200 bg-gray-900/80 dark:bg-black/50 rounded-lg p-3 overflow-x-auto whitespace-pre-wrap">{{ $prettyJson }}</pre>
                                    </div>
                                </div>
                            @else
                                <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                                    @if (mb_strlen($trimmedContent) > 500)
                                        <div x-data="{ open: false }">
                                            <div x-show="!open" x-cloak>{{ \Illuminate\Support\Str::limit($trimmedContent, 500) }}</div>
                                            <div x-show="open" x-cloak>{{ $trimmedContent }}</div>
                                            <button @click="open = !open"
                                                class="mt-1 text-xs text-orbit-500 hover:text-orbit-600 dark:text-orbit-400 font-medium">
                                                <span x-show="!open">Show full content</span>
                                                <span x-show="open" x-cloak>Show less</span>
                                            </button>
                                        </div>
                                    @else
                                        {{ $trimmedContent }}
                                    @endif
                                </div>
                            @endif

                            {{-- Tool calls --}}
                            @if (!empty($message->tool_calls) && $message->tool_calls !== 'null')
                                @php $toolCalls = json_decode($message->tool_calls, true) ?? []; @endphp
                                @foreach ($toolCalls as $callIndex => $toolCall)
                                    <div x-data="{ open: true }" class="mt-3">
                                        <button @click="open = !open"
                                            class="flex items-center gap-2 text-xs font-medium text-purple-600 dark:text-purple-300 hover:text-purple-700 dark:hover:text-purple-200 transition-colors">
                                            <svg class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                            Tool: {{ $toolCall['function']['name'] ?? $toolCall['name'] ?? "Call #{$callIndex}" }}
                                        </button>
                                        <div x-show="open" x-collapse class="mt-2">
                                            <pre class="text-xs font-mono text-gray-200 bg-gray-900/80 dark:bg-black/50 rounded-lg p-3 overflow-x-auto whitespace-pre-wrap">{{ json_encode($toolCall['function']['arguments'] ?? $toolCall['arguments'] ?? [], JSON_PRETTY_PRINT) }}</pre>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            {{-- Usage --}}
                            @if (!empty($message->usage) && $message->usage !== 'null')
                                @php $usage = json_decode($message->usage, true) ?? []; @endphp
                                @if (!empty($usage))
                                    <div class="mt-3 flex flex-wrap gap-3 text-xs text-gray-400 dark:text-gray-500">
                                        @if (!empty($usage['input_tokens']))
                                            <span class="inline-flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                {{ number_format($usage['input_tokens']) }} in
                                            </span>
                                        @endif
                                        @if (!empty($usage['output_tokens']))
                                            <span class="inline-flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 14V3L2 14h7v7l9-11H11z"/></svg>
                                                {{ number_format($usage['output_tokens']) }} out
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-ai-orbit::layout>

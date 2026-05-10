<x-laravel-ai-orbit::layout>
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('orbit.conversations.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Execution Trace</h2>
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
        <x-laravel-ai-orbit::card>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Conversation ID</span>
                    <p class="font-mono text-gray-900 dark:text-gray-100">{{ \Illuminate\Support\Str::limit($conversation->id, 12, '') }}</p>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Messages</span>
                    <p class="text-gray-900 dark:text-gray-100">{{ $messages->count() }}</p>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Created</span>
                    <p class="text-gray-900 dark:text-gray-100">{{ \Carbon\Carbon::parse($conversation->created_at)->format('M d, Y H:i') }}</p>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Last Updated</span>
                    <p class="text-gray-900 dark:text-gray-100">{{ \Carbon\Carbon::parse($conversation->updated_at)->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </x-laravel-ai-orbit::card>

        {{-- Timeline --}}
        @if ($messages->isEmpty())
            <x-laravel-ai-orbit::empty-state title="No messages" description="This conversation has no recorded messages to trace." />
        @else
            <div class="relative">
                {{-- Vertical line --}}
                <div class="absolute left-5 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-800" aria-hidden="true"></div>

                <div class="space-y-6">
                    @foreach ($messages as $index => $message)
                        @php
                            $previousMessage = $index > 0 ? $messages[$index - 1] : null;
                            $latency = $previousMessage
                                ? \Carbon\Carbon::parse($message->created_at)->diffInMilliseconds(\Carbon\Carbon::parse($previousMessage->created_at))
                                : null;

                            $stepLabel = match ($message->role) {
                                'user' => 'Input',
                                'system' => 'System Context',
                                'assistant' => 'Response',
                                'tool' => 'Tool Call',
                                default => ucfirst($message->role),
                            };

                            $stepColor = match ($message->role) {
                                'user' => 'bg-blue-500',
                                'system' => 'bg-yellow-500',
                                'assistant' => 'bg-green-500',
                                'tool' => 'bg-purple-500',
                                default => 'bg-gray-500',
                            };
                        @endphp

                        <div class="flex gap-4">
                            {{-- Timeline dot --}}
                            <div class="relative flex-shrink-0 mt-1">
                                <div class="w-3 h-3 rounded-full {{ $stepColor }} ring-4 ring-white dark:ring-gray-950"></div>
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <x-laravel-ai-orbit::card padding="p-4">
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                                            Step {{ $index + 1 }}
                                        </span>
                                        <x-laravel-ai-orbit::badge :label="$stepLabel" :color="match($message->role) {
                                            'user' => 'blue',
                                            'system' => 'yellow',
                                            'assistant' => 'green',
                                            'tool' => 'purple',
                                            default => 'gray',
                                        }" />
                                        <span class="text-xs text-gray-400 dark:text-gray-500">
                                            {{ \Carbon\Carbon::parse($message->created_at)->format('H:i:s') }}
                                        </span>
                                        @if ($latency !== null)
                                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                                &middot; {{ number_format($latency) }}ms since previous step
                                            </span>
                                        @endif
                                    </div>

                                    @if ($message->role === 'system')
                                        <div class="text-sm text-gray-700 dark:text-gray-300 font-mono whitespace-pre-wrap bg-gray-50 dark:bg-gray-800/50 rounded p-3">
                                            {{ $message->content }}
                                        </div>
                                    @else
                                        <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                                            {{ \Illuminate\Support\Str::limit($message->content, 500) }}
                                            @if (mb_strlen($message->content) > 500)
                                                <details class="mt-2">
                                                    <summary class="text-xs text-orbit-500 cursor-pointer">Show full content</summary>
                                                    <div class="mt-2 whitespace-pre-wrap">{{ $message->content }}</div>
                                                </details>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Tool information --}}
                                    @if (!empty($message->tool_calls) && $message->tool_calls !== 'null')
                                        @php $toolCalls = json_decode($message->tool_calls, true) ?? []; @endphp
                                        @foreach ($toolCalls as $callIndex => $toolCall)
                                            <div class="mt-3 p-3 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded">
                                                <div class="text-xs font-semibold text-purple-700 dark:text-purple-300 mb-1">
                                                    Tool Call: {{ $toolCall['function']['name'] ?? $toolCall['name'] ?? "Call #{$callIndex}" }}
                                                </div>
                                                <pre class="text-xs text-purple-600 dark:text-purple-400 font-mono whitespace-pre-wrap">{{ json_encode($toolCall['function']['arguments'] ?? $toolCall['arguments'] ?? [], JSON_PRETTY_PRINT) }}</pre>
                                            </div>
                                        @endforeach
                                    @endif

                                    {{-- Usage info --}}
                                    @if (!empty($message->usage) && $message->usage !== 'null')
                                        @php $usage = json_decode($message->usage, true) ?? []; @endphp
                                        @if (!empty($usage))
                                            <div class="mt-2 flex flex-wrap gap-3 text-xs text-gray-500 dark:text-gray-400">
                                                @if (!empty($usage['input_tokens']))
                                                    <span>{{ number_format($usage['input_tokens']) }} input tokens</span>
                                                @endif
                                                @if (!empty($usage['output_tokens']))
                                                    <span>{{ number_format($usage['output_tokens']) }} output tokens</span>
                                                @endif
                                            </div>
                                        @endif
                                    @endif
                                </x-laravel-ai-orbit::card>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-laravel-ai-orbit::layout>

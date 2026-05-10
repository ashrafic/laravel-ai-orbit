<div class="space-y-6">
    {{-- Conversation Header --}}
    @if ($conversation)
        <x-orbit::card>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $conversation->title }}</h2>
                    <div class="flex flex-wrap items-center gap-2 mt-1">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ \Carbon\Carbon::parse($conversation->created_at)->format('M d, Y H:i') }}
                        </span>
                        @if (!empty($conversation->agent_class))
                            <span class="text-xs text-gray-500 dark:text-gray-400">&middot;</span>
                            <x-orbit::badge :label="class_basename($conversation->agent_class)" color="blue" />
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        wire:click="toggleRawPayload"
                        class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                    >
                        {{ $showRawPayload ? 'Hide' : 'Show' }} Raw Payload
                    </button>
                </div>
            </div>
        </x-orbit::card>
    @endif

    {{-- Messages Timeline --}}
    @if ($messages->isEmpty())
        <x-orbit::empty-state title="No messages" description="This conversation has no recorded messages." />
    @else
        <div class="space-y-4">
            @foreach ($messages as $message)
                @php
                    $roleStyles = match ($message->role) {
                        'user' => 'ml-auto bg-orbit-500 text-white',
                        'assistant' => 'mr-auto bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100',
                        'system' => 'mx-auto bg-yellow-50 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-200 border border-yellow-200 dark:border-yellow-800',
                        'tool' => 'mr-auto bg-purple-50 dark:bg-purple-900/20 text-purple-800 dark:text-purple-200 border border-purple-200 dark:border-purple-800',
                        default => 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100',
                    };

                    $roleLabel = match ($message->role) {
                        'user' => 'User',
                        'assistant' => 'Assistant',
                        'system' => 'System',
                        'tool' => 'Tool',
                        default => ucfirst($message->role),
                    };
                @endphp

                <div class="max-w-2xl {{ $roleStyles }} rounded-lg p-4 shadow-sm {{ $message->role === 'user' ? 'ml-auto' : ($message->role === 'system' ? 'mx-auto text-center max-w-lg' : '') }}">
                    {{-- Message Header --}}
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold uppercase tracking-wider opacity-70">{{ $roleLabel }}</span>
                        <span class="text-xs opacity-60">{{ \Carbon\Carbon::parse($message->created_at)->format('H:i:s') }}</span>
                    </div>

                    {{-- Message Content --}}
                    <div class="text-sm whitespace-pre-wrap {{ $message->role === 'system' ? 'font-mono' : '' }}">
                        {{ $message->content }}
                    </div>

                    {{-- Tool Calls --}}
                    @if (!empty($message->tool_calls) && $message->tool_calls !== 'null')
                        @php
                            $toolCalls = json_decode($message->tool_calls, true) ?? [];
                        @endphp
                        @foreach ($toolCalls as $index => $toolCall)
                            <details class="mt-2">
                                <summary class="text-xs font-medium cursor-pointer opacity-70 hover:opacity-100">
                                    Tool: {{ $toolCall['function']['name'] ?? $toolCall['name'] ?? "Call #{$index}" }}
                                </summary>
                                <div class="mt-2 p-3 bg-black/10 dark:bg-white/5 rounded text-xs font-mono">
                                    <div class="mb-1 font-semibold">Arguments:</div>
                                    <pre class="whitespace-pre-wrap">{{ json_encode($toolCall['function']['arguments'] ?? $toolCall['arguments'] ?? [], JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </details>
                        @endforeach
                    @endif

                    {{-- Raw Payload --}}
                    @if ($showRawPayload)
                        <details class="mt-2">
                            <summary class="text-xs font-medium cursor-pointer opacity-70 hover:opacity-100">Raw JSON</summary>
                            <pre class="mt-2 p-3 bg-black/10 dark:bg-white/5 rounded text-xs overflow-x-auto">{{ json_encode($message, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        </details>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>

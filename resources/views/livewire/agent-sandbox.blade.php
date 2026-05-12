<div class="flex flex-col h-[calc(100vh-10rem)]">
    {{-- Chat Messages --}}
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
                :disabled="$wire.sending"
                class="orbit-input flex-1 disabled:opacity-50"
            >
            <button
                type="submit"
                :disabled="$wire.sending || $wire.prompt === ''"
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

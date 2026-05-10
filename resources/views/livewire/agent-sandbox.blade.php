<div class="flex flex-col h-[calc(100vh-10rem)]">
    {{-- Chat Messages --}}
    <div class="flex-1 overflow-y-auto space-y-3 mb-4 pr-2" id="sandbox-messages">
        @if (empty($history) && !$sending)
            <div class="flex items-center justify-center h-full">
                <x-orbit::empty-state title="Start a conversation"
                    description="Send a message to begin chatting with this agent." />
            </div>
        @endif

        @foreach ($history as $message)
            @if ($message['role'] === 'user')
                <div class="flex justify-end">
                    <div class="max-w-[80%] bg-orbit-500 text-white rounded-lg px-4 py-3 shadow-sm">
                        <p class="text-sm whitespace-pre-wrap">{{ $message['content'] }}</p>
                    </div>
                </div>
            @elseif ($message['role'] === 'error')
                <div class="flex justify-center">
                    <div class="max-w-[80%] bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 rounded-lg px-4 py-3 shadow-sm">
                        <p class="text-sm font-mono whitespace-pre-wrap">{{ $message['content'] }}</p>
                    </div>
                </div>
            @else
                <div class="flex justify-start">
                    <div class="max-w-[80%] bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-lg px-4 py-3 shadow-sm">
                        <p class="text-sm whitespace-pre-wrap">{{ $message['content'] }}</p>
                    </div>
                </div>
            @endif
        @endforeach

        @if ($sending)
            <div class="flex justify-start">
                <div class="bg-gray-100 dark:bg-gray-800 rounded-lg px-4 py-3 shadow-sm">
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
            <div class="mb-2 p-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded text-xs text-red-600 dark:text-red-400">
                {{ $error }}
            </div>
        @endif

        <form wire:submit="send" class="flex gap-2">
            <input
                wire:model="prompt"
                type="text"
                placeholder="Type a message..."
                :disabled="$sending"
                class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-orbit-500 focus:border-orbit-500 disabled:opacity-50"
            >
            <button
                type="submit"
                :disabled="$sending || $prompt === ''"
                class="px-4 py-2 bg-orbit-500 text-white text-sm font-medium rounded-lg hover:bg-orbit-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-2"
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
                    class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                >
                    Clear
                </button>
            @endif
        </form>
    </div>
</div>

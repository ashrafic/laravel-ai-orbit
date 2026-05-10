<x-ai-orbit::layout>
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('orbit.conversations.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Conversation</h2>
        </div>

        <livewire:ai-orbit.message-timeline :conversationId="$id" />
    </div>
</x-ai-orbit::layout>

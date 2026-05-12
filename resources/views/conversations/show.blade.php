<x-ai-orbit::layout>
    @slot('breadcrumb', 'Conversation')

    <div class="space-y-6">
        <livewire:ai-orbit.message-timeline :conversationId="$id" />
    </div>
</x-ai-orbit::layout>

<x-ai-orbit::layout>
    @slot('breadcrumb', 'Prompts')

    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-50 tracking-tight">Prompt Library</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Save and organize reusable prompts for quick access.</p>
        </div>

        <livewire:ai-orbit.prompt-library />
    </div>
</x-ai-orbit::layout>

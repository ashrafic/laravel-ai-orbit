<x-ai-orbit::layout>
    @slot('breadcrumb', 'Health')

    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-50 tracking-tight">Provider Health</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Monitor the health and reliability of your AI providers.</p>
        </div>

        <livewire:ai-orbit.provider-health />
    </div>
</x-ai-orbit::layout>

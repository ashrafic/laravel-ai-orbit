<x-ai-orbit::layout>
    @slot('breadcrumb', 'Runs')

    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-50 tracking-tight">Runs</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Inspect SDK invocations captured by Orbit observability.</p>
        </div>

        <livewire:ai-orbit.run-explorer />
    </div>
</x-ai-orbit::layout>

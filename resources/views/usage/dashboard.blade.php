<x-ai-orbit::layout>
    @slot('breadcrumb', 'Analytics')

    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-50 tracking-tight">Analytics</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Historical AI cost and token analytics.</p>
        </div>

        <livewire:ai-orbit.cost-dashboard />
    </div>
</x-ai-orbit::layout>

<x-ai-orbit::layout>
    @slot('breadcrumb', 'Pricing')

    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-50 tracking-tight">Pricing Matrix</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configure per-model pricing for accurate cost calculations.</p>
        </div>

        <livewire:ai-orbit.pricing-matrix />
    </div>
</x-ai-orbit::layout>

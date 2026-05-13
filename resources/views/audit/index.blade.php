<x-ai-orbit::layout>
    @slot('breadcrumb', 'Audit')

    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-50 tracking-tight">Security Audit</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Monitor PII exposure, access logs, and data retention compliance.</p>
        </div>

        <livewire:ai-orbit.audit-dashboard />
    </div>
</x-ai-orbit::layout>

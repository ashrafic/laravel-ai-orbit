@extends('ai-orbit::components.layout')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Security Audit</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Monitor PII exposure, access logs, and data retention compliance.</p>
    </div>

    <div class="space-y-6">
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Access Log</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Recent access to Orbit conversations.</p>
            <div class="mt-4 text-center py-8">
                <p class="text-gray-500 dark:text-gray-400">Audit logging will be available when conversations are accessed.</p>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">PII Detection</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Scan conversations for potential personally identifiable information.</p>
            <div class="mt-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">PII detection runs automatically on new conversations. Flagged conversations show a warning badge in the Thread Explorer.</p>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Data Retention</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Configure automatic purging of old conversations for GDPR compliance.</p>
            <div class="mt-4">
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Current retention policy: <strong>{{ config('ai-orbit-pro.audit.retention_days', 90) }} days</strong></span>
                </div>
                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">Conversations older than {{ config('ai-orbit-pro.audit.retention_days', 90) }} days are eligible for automatic purging. Configure via ORBIT_PRO_RETENTION_DAYS.</p>
            </div>
        </div>
    </div>
</div>
@endsection

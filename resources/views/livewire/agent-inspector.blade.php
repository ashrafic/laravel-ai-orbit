<div class="space-y-4">
    @if ($agentMeta === null)
        <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
            <p class="text-sm text-yellow-700 dark:text-yellow-300">Agent metadata could not be loaded.</p>
        </div>
    @else
        <div>
            <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Agent Class</h3>
            <p class="text-sm font-mono text-gray-900 dark:text-gray-100 break-all">{{ $agentMeta['class'] }}</p>
        </div>

        @if (!empty($agentMeta['instructions']))
            <div>
                <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Instructions</h3>
                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $agentMeta['instructions'] }}</p>
            </div>
        @endif

        <div>
            <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Structured Output</h3>
            @if ($agentMeta['has_schema'])
                <x-laravel-ai-orbit::badge label="Enabled" color="green" />
            @else
                <x-laravel-ai-orbit::badge label="Disabled" color="gray" />
            @endif
        </div>

        @if (!empty($agentMeta['tools']))
            <div>
                <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Tools</h3>
                <div class="space-y-1">
                    @foreach ($agentMeta['tools'] as $tool)
                        <div class="text-sm font-mono text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 rounded px-2 py-1">
                            {{ class_basename($tool) }}
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div>
                <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Tools</h3>
                <p class="text-sm text-gray-400 dark:text-gray-500">No tools registered</p>
            </div>
        @endif
    @endif
</div>

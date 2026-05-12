<div class="space-y-6">
    {{-- Access Log --}}
    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Access Log</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Recent conversations across all agents.</p>
        </div>

        @if($recentConversations->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Agent</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Messages</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($recentConversations as $conv)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300">#{{ $conv->id }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ class_basename($conv->agent ?? 'Unknown') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $conv->messages_count ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ isset($conv->created_at) ? \Illuminate\Support\Carbon::parse($conv->created_at)->diffForHumans() : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
            No conversations recorded yet.
        </div>
        @endif
    </div>

    {{-- PII Detection --}}
    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">PII Detection</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Scan text for potential personally identifiable information (email, phone, SSN, credit card, IP).</p>

        <div class="mb-4">
            <textarea wire:model="scanContent" rows="4"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-2 text-sm"
                placeholder="Paste message content to scan for PII..."></textarea>
        </div>

        <button wire:click="scanPii"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg mb-4">
            Scan for PII
        </button>

        @if($piiResults !== null)
        <div class="rounded-lg border {{ $piiResults['has_pii'] ? 'border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20' : 'border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20' }} p-4">
            @if($piiResults['has_pii'])
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <span class="text-sm font-semibold text-red-700 dark:text-red-300">PII Detected</span>
                </div>
                <div class="space-y-2">
                    @foreach($piiResults['detections'] as $type => $matches)
                    <div>
                        <span class="text-xs font-medium text-red-600 dark:text-red-400 uppercase">{{ $type }}</span>
                        <div class="flex flex-wrap gap-1 mt-0.5">
                            @foreach($matches as $match)
                            <code class="px-1.5 py-0.5 bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 text-xs rounded">{{ $match }}</code>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-semibold text-green-700 dark:text-green-300">No PII detected</span>
                </div>
            @endif
        </div>
        @endif
    </div>

    {{-- Data Retention --}}
    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Data Retention</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Manage automatic purging of old conversations for compliance.</p>

        <div class="flex items-center gap-3 mb-4">
            <label class="text-sm text-gray-700 dark:text-gray-300">Retention period (days):</label>
            <input wire:model="retentionDays" type="number" min="1"
                class="w-24 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-1.5 text-sm">
        </div>

        <div class="flex gap-2 mb-4">
            <button wire:click="dryRun"
                class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">
                Dry Run
            </button>
            <button wire:click="purge" wire:confirm="This will permanently delete conversations older than {{ $retentionDays }} days. Are you sure?"
                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg">
                Purge Now
            </button>
        </div>

        @if($dryRunResults !== null)
        <div class="rounded-lg border border-yellow-200 dark:border-yellow-800 bg-yellow-50 dark:bg-yellow-900/20 p-4">
            <p class="text-sm text-yellow-700 dark:text-yellow-300">
                <strong>{{ $dryRunResults['count'] }}</strong> conversation(s) older than {{ $retentionDays }} days would be deleted.
            </p>
        </div>
        @endif

        @if($purgedCount !== null)
        <div class="rounded-lg border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 p-4">
            <p class="text-sm text-green-700 dark:text-green-300">
                Successfully purged <strong>{{ $purgedCount }}</strong> conversation(s).
            </p>
        </div>
        @endif
    </div>
</div>

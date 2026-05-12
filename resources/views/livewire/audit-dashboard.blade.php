<div class="space-y-6">
    {{-- Access Log --}}
    <x-ai-orbit::card padding="p-0">
        <div class="px-6 py-4 border-b border-gray-200/60 dark:border-white/8">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50 tracking-tight">Access Log</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Recent conversations across all agents.</p>
        </div>

        @if($recentConversations->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="orbit-table w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200/60 dark:border-white/8">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Agent</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Messages</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/4">
                    @foreach($recentConversations as $conv)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-50">#{{ $conv->id }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ class_basename($conv->agent_class ?? 'Unknown') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ number_format($conv->messages_count ?? 0) }}</td>
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
    </x-ai-orbit::card>

    {{-- PII Detection --}}
    <x-ai-orbit::card>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50 tracking-tight mb-4">PII Detection</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Scan text for potential personally identifiable information (email, phone, SSN, credit card, IP).</p>

        <div class="mb-4">
            <textarea wire:model="scanContent" rows="4"
                class="orbit-input w-full"
                placeholder="Paste message content to scan for PII..."></textarea>
        </div>

        <button wire:click="scanPii"
            class="orbit-btn-primary mb-4">
            Scan for PII
        </button>

        @if($piiResults !== null)
        <div class="glass-card {{ $piiResults['has_pii'] ? 'border-red-200/50 dark:border-red-800/50' : 'border-green-200/50 dark:border-green-800/50' }} p-4">
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
    </x-ai-orbit::card>

    {{-- Data Retention --}}
    <x-ai-orbit::card>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50 tracking-tight mb-4">Data Retention</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Manage automatic purging of old conversations for compliance.</p>

        <div class="flex items-center gap-3 mb-4">
            <label class="text-sm text-gray-700 dark:text-gray-300">Retention period (days):</label>
            <input wire:model="retentionDays" type="number" min="1"
                class="orbit-input w-24">
        </div>

        <div class="flex gap-2 mb-4">
            <button wire:click="dryRun"
                class="orbit-btn-secondary">
                Dry Run
            </button>
            <button wire:click="purge" wire:confirm="This will permanently delete conversations older than {{ $retentionDays }} days. Are you sure?"
                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg">
                Purge Now
            </button>
        </div>

        @if($dryRunResults !== null)
        <div class="glass-card border-yellow-200/50 dark:border-yellow-800/50 p-4">
            <p class="text-sm text-yellow-700 dark:text-yellow-300">
                <strong>{{ $dryRunResults['count'] }}</strong> conversation(s) older than {{ $retentionDays }} days would be deleted.
            </p>
        </div>
        @endif

        @if($purgedCount !== null)
        <div class="glass-card border-green-200/50 dark:border-green-800/50 p-4">
            <p class="text-sm text-green-700 dark:text-green-300">
                Successfully purged <strong>{{ $purgedCount }}</strong> conversation(s).
            </p>
        </div>
        @endif
    </x-ai-orbit::card>
</div>

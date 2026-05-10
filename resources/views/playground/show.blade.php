<x-ai-orbit::layout>
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('orbit.playground.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Sandbox</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-mono">{{ class_basename($agent) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Sandbox --}}
            <div class="lg:col-span-2">
                <x-ai-orbit::card title="Chat" padding="p-4">
                    <livewire:ai-orbit.agent-sandbox :agentClass="$agent" />
                </x-ai-orbit::card>
            </div>

            {{-- Inspector Sidebar --}}
            <div>
                <x-ai-orbit::card title="Agent Inspector">
                    <livewire:ai-orbit.agent-inspector :agentClass="$agent" />
                </x-ai-orbit::card>
            </div>
        </div>
    </div>
</x-ai-orbit::layout>

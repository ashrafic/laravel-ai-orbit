<div class="space-y-6">
    {{-- Create / Edit Form --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            {{ $editingId ? 'Edit Prompt' : 'Create New Prompt' }}
        </h3>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                <input type="text" wire:model="name"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-orbit-500 focus:border-orbit-500">
                @error('name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Content</label>
                <textarea wire:model="content" rows="4"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-orbit-500 focus:border-orbit-500"></textarea>
                @error('content') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Agent Class (optional)</label>
                <input type="text" wire:model="agentClass"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-orbit-500 focus:border-orbit-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tags</label>
                <div class="flex gap-2">
                    <input type="text" wire:model="tagInput" wire:keydown.enter="addTag"
                        placeholder="Type and press enter"
                        class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-orbit-500 focus:border-orbit-500">
                    <button type="button" wire:click="addTag"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-600">
                        Add
                    </button>
                </div>
                @if (!empty($tags))
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach ($tags as $tag)
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-orbit-50 dark:bg-orbit-900/30 text-orbit-600 dark:text-orbit-400">
                                {{ $tag }}
                                <button type="button" wire:click="removeTag('{{ $tag }}')" class="hover:text-red-500">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex gap-2">
                <button type="button" wire:click="save"
                    class="px-4 py-2 bg-orbit-500 text-white rounded-lg text-sm font-medium hover:bg-orbit-600 transition-colors">
                    {{ $editingId ? 'Update' : 'Save' }}
                </button>
                @if ($editingId)
                    <button type="button" wire:click="cancelEdit"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-600">
                        Cancel
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Search --}}
    <div>
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search prompts..."
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-orbit-500 focus:border-orbit-500">
    </div>

    {{-- Prompt List --}}
    <div class="space-y-4">
        @forelse ($prompts as $prompt)
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $prompt->name }}</h4>
                        @if ($prompt->agent_class)
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 font-mono">{{ $prompt->agent_class }}</p>
                        @endif
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 line-clamp-3">{{ $prompt->content }}</p>
                        @if (!empty($prompt->tags))
                            <div class="flex flex-wrap gap-1 mt-2">
                                @foreach ($prompt->tags as $tag)
                                    <span class="px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0 ml-4">
                        <button type="button" wire:click="edit({{ $prompt->id }})"
                            class="text-gray-400 hover:text-orbit-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <button type="button" wire:click="delete({{ $prompt->id }})"
                            wire:confirm="Are you sure you want to delete this prompt?"
                            class="text-gray-400 hover:text-red-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <x-ai-orbit::empty-state message="No prompts found. Create your first prompt above." />
        @endforelse
    </div>

    <div class="pt-2">
        {{ $prompts->links() }}
    </div>
</div>

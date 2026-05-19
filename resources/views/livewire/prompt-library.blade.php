<div class="space-y-6">
    {{-- Create Button --}}
    @if(! $showForm)
    <div class="mb-4">
        <button wire:click="$set('showForm', true)"
            class="orbit-btn-primary">
            + Create Prompt
        </button>
    </div>
    @endif

    {{-- Create / Edit Form --}}
    @if($showForm)
    <x-ai-orbit::card>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-50 tracking-tight mb-6">
            {{ $editingId ? 'Edit Prompt' : 'Create New Prompt' }}
        </h3>

        <div class="space-y-4">
            {{-- Identical to Prompt Lab --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">System Prompt</label>
                <textarea wire:model="content" rows="3"
                    class="orbit-input w-full"
                    placeholder="You are a helpful assistant..."></textarea>
                @error('content') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Instruction / User Prompt</label>
                <textarea wire:model="instruction" rows="3"
                    class="orbit-input w-full"
                    placeholder="Write a poem about..."></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Temperature: {{ number_format($temperature, 1) }}
                    </label>
                    <input type="range" wire:model.live="temperature" min="0" max="2" step="0.1"
                        class="w-full accent-orbit-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Max Tokens</label>
                    <input type="number" wire:model="maxTokens"
                        class="orbit-input w-full"
                        placeholder="Model default">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Top P: {{ number_format($topP, 1) }}
                    </label>
                    <input type="range" wire:model.live="topP" min="0" max="1" step="0.1"
                        class="w-full accent-orbit-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Context / History <span class="text-gray-400 font-normal">(optional, JSON array of prior messages)</span>
                </label>
                <textarea wire:model="context" rows="3"
                    class="orbit-input w-full font-mono text-xs"
                    placeholder='[{"role": "user", "content": "Hello"}, {"role": "assistant", "content": "Hi!"}]'></textarea>
            </div>

            {{-- Library-specific --}}
            <div class="pt-4 border-t border-gray-100 dark:border-white/5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name <span class="text-gray-400 font-normal">(label)</span></label>
                        <input type="text" wire:model="name"
                            class="orbit-input w-full" placeholder="e.g. Blog Writer, Code Reviewer">
                        @error('name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tags</label>
                        <div class="flex gap-2">
                            <input type="text" wire:model="tagInput" wire:keydown.enter="addTag"
                                placeholder="Type and press enter"
                                class="orbit-input flex-1">
                            <button type="button" wire:click="addTag"
                                class="orbit-btn-secondary text-xs">
                                Add
                            </button>
                        </div>
                        @if (!empty($tags))
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                @foreach ($tags as $tag)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-orbit-50 dark:bg-orbit-900/30 text-orbit-600 dark:text-orbit-400">
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
                </div>
            </div>

            <div class="flex gap-2">
                <button type="button" wire:click="save"
                    class="orbit-btn-primary">
                    {{ $editingId ? 'Update' : 'Save' }}
                </button>
                @if ($editingId)
                    <button type="button" wire:click="cancelEdit"
                        class="orbit-btn-secondary">
                        Cancel
                    </button>
                @endif
            </div>
        </div>
    </x-ai-orbit::card>
    @endif

    {{-- Search --}}
    <div>
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search prompts..."
            class="orbit-input w-full">
    </div>

    {{-- Prompt List --}}
    <div class="space-y-3">
        @forelse ($prompts as $prompt)
            <x-ai-orbit::card>
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-50 truncate">{{ $prompt->name }}</h4>
                            @if (!empty($prompt->tags))
                                <div class="flex gap-1 flex-shrink-0">
                                    @foreach ($prompt->tags as $tag)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">{{ $prompt->content }}</p>
                        @if (!empty($prompt->instruction))
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500 line-clamp-1">Instruction: {{ $prompt->instruction }}</p>
                        @endif
                        @php $meta = $prompt->meta ?? []; @endphp
                        @if (!empty($meta))
                            <div class="flex flex-wrap items-center gap-2 mt-2 text-[11px] text-gray-400 dark:text-gray-500">
                                @if (isset($meta['temperature']))
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        {{ number_format($meta['temperature'], 1) }}
                                    </span>
                                @endif
                                @if (!empty($meta['max_tokens']))
                                    <span>{{ $meta['max_tokens'] }} max tokens</span>
                                @endif
                                @if (isset($meta['top_p']))
                                    <span>topP: {{ number_format($meta['top_p'], 1) }}</span>
                                @endif
                                @if (!empty($meta['context']))
                                    <span class="line-clamp-1">context: {{ \Illuminate\Support\Str::limit($meta['context'], 60) }}</span>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="flex items-center gap-1 flex-shrink-0">
                        <button type="button" wire:click="edit({{ $prompt->id }})"
                            class="p-1 text-gray-400 hover:text-orbit-500 transition-colors rounded">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <button type="button" wire:click="delete({{ $prompt->id }})"
                            wire:confirm="Are you sure you want to delete this prompt?"
                            class="p-1 text-gray-400 hover:text-red-500 transition-colors rounded">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </x-ai-orbit::card>
        @empty
            <x-ai-orbit::empty-state message="No prompts found. Create your first prompt above." />
        @endforelse
    </div>

    <div class="pt-2">
        {{ $prompts->links() }}
    </div>
</div>

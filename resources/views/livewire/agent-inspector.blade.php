<div class="space-y-4">
    @if ($agentMeta === null)
        <div class="p-4 glass-card border-yellow-200/50 dark:border-yellow-800/50 rounded-xl">
            <p class="text-sm text-yellow-700 dark:text-yellow-300">Agent metadata could not be loaded.</p>
        </div>
    @else
        <div>
            <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Agent Class</h3>
            <p class="text-sm font-mono text-gray-900 dark:text-gray-50 break-all">{{ $agentMeta['class'] }}</p>
        </div>

        {{-- Provider & Model Overrides --}}
        <div>
            <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Overrides</h3>
            <div class="space-y-2">
                <select wire:model.live="overrideProvider"
                    class="w-full px-2.5 py-1.5 text-xs border border-gray-300 dark:border-gray-600 rounded-lg
                           bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                           focus:ring-2 focus:ring-orbit-500 focus:border-orbit-500">
                    <option value="">Provider (default)</option>
                    @foreach($this->getConfiguredProviders() as $provider)
                        <option value="{{ $provider }}">{{ ucfirst($provider) }}</option>
                    @endforeach
                </select>
                <input type="text" wire:model.live="overrideModel"
                    placeholder="Model (default)"
                    class="w-full px-2.5 py-1.5 text-xs border border-gray-300 dark:border-gray-600 rounded-lg
                           bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                           focus:ring-2 focus:ring-orbit-500 focus:border-orbit-500
                           placeholder-gray-400 dark:placeholder-gray-500">
                <label class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                    <span>Temperature</span>
                    <span class="font-mono">{{ $overrideTemperature !== null ? number_format($overrideTemperature, 1) : 'default' }}</span>
                </label>
                <input type="range" wire:model.live="overrideTemperature"
                    min="0" max="2" step="0.1"
                    class="w-full accent-orbit-500">
                <div class="flex justify-between text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">
                    <span>0</span><span>1</span><span>2</span>
                </div>
                @if ($overrideProvider || $overrideModel || $overrideTemperature !== ($agentMeta['temperature'] ?? null))
                    <button wire:click="clearOverrides" type="button"
                        class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        Clear overrides
                    </button>
                @endif
            </div>
        </div>

        @if (!empty($agentMeta['instructions']))
            <div>
                <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Instructions</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 whitespace-pre-wrap">{{ $agentMeta['instructions'] }}</p>
            </div>
        @else
            <div>
                <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Instructions</h3>
                <p class="text-sm text-gray-400 dark:text-gray-500 italic">Instructions require runtime context</p>
            </div>
        @endif

        <div>
            <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Structured Output</h3>
            @if ($agentMeta['has_schema'])
                <x-ai-orbit::badge label="Enabled" color="green" />
            @else
                <x-ai-orbit::badge label="Disabled" color="gray" />
            @endif
        </div>

        @if (!empty($agentMeta['tools']))
            <div>
                <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                    Tools ({{ count($agentMeta['tools']) }})
                </h3>
                <div class="space-y-2">
                    @foreach ($agentMeta['tools'] as $tool)
                        @php
                            $toolName = is_array($tool) ? ($tool['name'] ?? '') : class_basename($tool);
                            $toolClass = is_array($tool) ? ($tool['class'] ?? $tool) : $tool;
                            $toolDesc = is_array($tool) ? ($tool['description'] ?? '') : '';
                            $callCount = $toolCallCounts[$toolName] ?? 0;
                        @endphp
                        <div class="p-2 glass-card rounded flex items-start gap-2">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-50">{{ $toolName }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 font-mono mt-0.5">{{ $toolClass }}</p>
                                @if ($toolDesc)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ $toolDesc }}</p>
                                @endif
                            </div>
                            @if ($callCount > 0)
                                <span class="flex-shrink-0 px-1.5 py-0.5 text-[10px] font-semibold bg-orbit-500/10 text-orbit-600 dark:text-orbit-400 rounded-full">
                                    {{ $callCount }}
                                </span>
                            @endif
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

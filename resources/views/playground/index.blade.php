<x-ai-orbit::layout>
    @slot('breadcrumb', 'Playground')

    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-50 tracking-tight">Playground</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Select an agent to start testing.</p>
        </div>

        @if ($agents->isEmpty())
            <x-ai-orbit::empty-state title="No agents discovered"
                description="Place your agent classes in app/AI/Agents/. They must implement the Laravel\Ai\Contracts\Agent interface." />
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach ($agents as $agentClass)
                    @php
                        $shortName = class_basename($agentClass);
                        $meta = app(\Ashrafic\AiOrbit\Contracts\AgentRegistryContract::class)->find($agentClass);
                    @endphp
                    <x-ai-orbit::card padding="p-4" class="hover:border-orbit-300/50 dark:hover:border-orbit-700/50 transition-colors">
                        <div class="space-y-3">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-50">{{ $shortName }}</h3>
                                <p class="text-xs text-gray-400 dark:text-gray-500 font-mono mt-0.5 break-all">{{ $agentClass }}</p>
                            </div>

                            @if ($meta)
                                <div class="flex flex-wrap gap-1.5">
                                    @if (!empty($meta['instructions']))
                                        <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 w-full">{{ $meta['instructions'] }}</p>
                                    @else
                                        <p class="text-xs text-gray-400 dark:text-gray-500 italic w-full">No description available</p>
                                    @endif

                                    @if (!empty($meta['tools']))
                                        <x-ai-orbit::badge :label="count($meta['tools']).' tools'" color="blue" />
                                    @endif

                                    @if ($meta['has_schema'])
                                        <x-ai-orbit::badge label="Structured Output" color="green" />
                                    @endif
                                </div>
                            @endif

                            <a href="{{ route('orbit.playground.show', $agentClass) }}"
                               class="orbit-btn-primary block w-full text-center">
                                Open Sandbox
                            </a>
                        </div>
                    </x-ai-orbit::card>
                @endforeach
            </div>
        @endif
    </div>
</x-ai-orbit::layout>

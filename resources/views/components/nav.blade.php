<div
    data-sidebar
    class="w-56 lg:w-60 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 flex flex-col h-screen sticky top-0 flex-shrink-0
           max-sm:fixed max-sm:inset-y-0 max-sm:left-0 max-sm:z-40 max-sm:-translate-x-full sidebar-transition"
>
    {{-- Brand --}}
    <div class="h-14 flex items-center px-4 border-b border-gray-200 dark:border-gray-800">
        <a href="{{ route('orbit.dashboard') }}" class="flex items-center gap-2">
            <span class="text-lg font-bold text-orbit-500">Orbit</span>
        </a>
    </div>

    {{-- Navigation Links --}}
    <nav class="flex-1 overflow-y-auto py-4 space-y-0.5 px-2">
        @php
            $links = [
                ['route' => 'orbit.dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard'],
                ['route' => 'orbit.conversations.index', 'label' => 'Conversations', 'icon' => 'chat'],
                ['route' => 'orbit.playground.index', 'label' => 'Playground', 'icon' => 'play'],
                ['route' => 'orbit.usage.index', 'label' => 'Usage', 'icon' => 'chart'],
            ];

            $proLinks = [
                ['label' => 'Arena', 'feature' => 'hasArena'],
                ['label' => 'Audit', 'feature' => 'hasAudit'],
                ['label' => 'Prompts', 'feature' => 'hasAdvancedAnalytics'],
            ];
        @endphp

        @foreach ($links as $link)
            @php
                $isActive = request()->routeIs($link['route']);
            @endphp
            <a href="{{ route($link['route']) }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ $isActive
                          ? 'bg-orbit-50 dark:bg-orbit-900/30 text-orbit-600 dark:text-orbit-400'
                          : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200' }}"
            >
                {{-- Icon --}}
                <span class="w-5 h-5 flex items-center justify-center flex-shrink-0">
                    @if ($link['icon'] === 'dashboard')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                    @elseif ($link['icon'] === 'chat')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                        </svg>
                    @elseif ($link['icon'] === 'play')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    @elseif ($link['icon'] === 'chart')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    @endif
                </span>
                <span>{{ $link['label'] }}</span>
            </a>
        @endforeach

        {{-- Pro Features Divider --}}
        <div class="pt-4 pb-1">
            <div class="border-t border-gray-200 dark:border-gray-800 pt-3 px-3">
                <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Pro</span>
            </div>
        </div>

        @php
            $featureGate = app(\Ashraf\LaravelAiOrbit\Contracts\FeatureGate::class);
        @endphp

        @foreach ($proLinks as $proLink)
            @php
                $hasFeature = method_exists($featureGate, $proLink['feature'])
                    ? $featureGate->{$proLink['feature']}()
                    : false;
            @endphp

            @if ($hasFeature)
                <a href="#"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200"
                >
                    <span class="w-5 h-5 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </span>
                    <span>{{ $proLink['label'] }}</span>
                </a>
            @else
                <span class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-400 dark:text-gray-600 cursor-not-allowed select-none">
                    <span class="w-5 h-5 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </span>
                    <span>{{ $proLink['label'] }}</span>
                </span>
            @endif
        @endforeach
    </nav>

    {{-- Pro CTA --}}
    <div class="p-3 border-t border-gray-200 dark:border-gray-800">
        @if (! $featureGate->hasArena())
            <a href="#" class="block text-center text-xs py-2 px-3 rounded-lg bg-orbit-500 text-white font-medium hover:bg-orbit-600 transition-colors">
                Upgrade to Pro
            </a>
        @endif
    </div>
</div>

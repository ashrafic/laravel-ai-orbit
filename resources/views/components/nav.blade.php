<div
    data-sidebar
    class="w-[220px] glass-sidebar flex flex-col h-[calc(100vh-24px)] sticky top-3 flex-shrink-0 sidebar-transition z-30
           max-sm:fixed max-sm:inset-y-0 max-sm:left-0 max-sm:z-40 max-sm:-translate-x-full max-sm:rounded-none max-sm:border-0 max-sm:border-r"
>
    {{-- Brand --}}
    <div class="flex items-center gap-2.5 px-4 pt-4 pb-2">
        <div class="relative w-8 h-8 flex-shrink-0 logo-icon">
            <svg viewBox="0 0 64 64" class="w-8 h-8" fill="none" xmlns="http://www.w3.org/2000/svg">
                <ellipse cx="32" cy="32" rx="24" ry="10" stroke="rgba(99,102,241,0.5)" stroke-width="2" transform="rotate(-20 32 32)"/>
                <ellipse cx="32" cy="32" rx="24" ry="10" stroke="rgba(139,92,246,0.4)" stroke-width="2" transform="rotate(70 32 32)"/>
                <ellipse cx="32" cy="32" rx="20" ry="8" stroke="rgba(99,102,241,0.25)" stroke-width="1.5" transform="rotate(25 32 32)"/>
                <circle cx="32" cy="32" r="6" fill="url(#logoGrad)"/>
                <circle cx="52" cy="22" r="2.5" fill="#818cf8" opacity="0.8"/>
                <defs>
                    <linearGradient id="logoGrad" x1="0" y1="0" x2="64" y2="64">
                        <stop stop-color="#6366f1"/>
                        <stop offset="1" stop-color="#a78bfa"/>
                    </linearGradient>
                </defs>
            </svg>
        </div>
        <span class="text-2xl font-black tracking-wider brand-text orbit-brand-title">
            <span class="text-gray-100 dark:text-gray-100 font-bold">AI</span>
            <span class="orbit-brand-gradient">Orbit</span>
        </span>
    </div>

    {{-- Navigation Links --}}
    <div class="flex-1 overflow-hidden">
        <nav class="h-full overflow-y-auto py-2 px-2 space-y-0.5 nav-links">
            @php
                $links = [
                    ['route' => 'orbit.dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard'],
                    ['route' => 'orbit.conversations.index', 'label' => 'Conversations', 'icon' => 'chat'],
                    ['route' => 'orbit.playground.index', 'label' => 'Playground', 'icon' => 'play'],
                    ['route' => 'orbit.usage.index', 'label' => 'Usage', 'icon' => 'chart'],
                ];

                $advancedLinks = [
                    ['route' => 'orbit.prompt-lab.index', 'label' => 'Prompt Lab', 'icon' => 'prompt-lab'],
                    ['route' => 'orbit.usage.dashboard', 'label' => 'Analytics', 'icon' => 'chart'],
                    ['route' => 'orbit.usage.pricing', 'label' => 'Pricing', 'icon' => 'dollar'],
                    ['route' => 'orbit.usage.alerts', 'label' => 'Alerts', 'icon' => 'bell'],
                    ['route' => 'orbit.usage.health', 'label' => 'Health', 'icon' => 'heart'],
                    ['route' => 'orbit.audit.index', 'label' => 'Audit', 'icon' => 'shield'],
                    ['route' => 'orbit.prompts.index', 'label' => 'Prompts', 'icon' => 'bolt'],
                ];
            @endphp

            @foreach ($links as $link)
                @php $isActive = request()->routeIs($link['route']); @endphp
                <div class="sidebar-nav-item">
                    <a href="{{ route($link['route']) }}"
                       class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 pointer-events-auto
                              {{ $isActive
                                  ? 'nav-link-active'
                                  : 'text-gray-500 dark:text-gray-400 hover:bg-white/5 dark:hover:bg-white/5 hover:text-gray-700 dark:hover:text-gray-200' }}"
                    >
                        <span class="w-5 h-5 flex items-center justify-center flex-shrink-0 opacity-70">
                            @if ($link['icon'] === 'dashboard')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            @elseif ($link['icon'] === 'chat')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                            @elseif ($link['icon'] === 'play')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            @elseif ($link['icon'] === 'chart')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            @endif
                        </span>
                        <span class="whitespace-nowrap brand-text">{{ $link['label'] }}</span>
                    </a>
                </div>
            @endforeach

            <div class="pt-3 pb-1 advanced-divider">
                <div class="border-t border-gray-200/30 dark:border-white/5 pt-2 px-3">
                    <span class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Advanced</span>
                </div>
            </div>

            @foreach ($advancedLinks as $link)
                @php $isActive = request()->routeIs($link['route']); @endphp
                <div class="sidebar-nav-item">
                    <a href="{{ route($link['route']) }}"
                       class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 pointer-events-auto
                              {{ $isActive
                                  ? 'nav-link-active'
                                  : 'text-gray-500 dark:text-gray-400 hover:bg-white/5 dark:hover:bg-white/5 hover:text-gray-700 dark:hover:text-gray-200' }}"
                    >
                        <span class="w-5 h-5 flex items-center justify-center flex-shrink-0 opacity-70">
                            @if ($link['icon'] === 'prompt-lab')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                            @elseif ($link['icon'] === 'chart')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                            @elseif ($link['icon'] === 'dollar')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            @elseif ($link['icon'] === 'bell')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            @elseif ($link['icon'] === 'heart')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                            @elseif ($link['icon'] === 'shield')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            @elseif ($link['icon'] === 'bolt')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            @endif
                        </span>
                        <span class="whitespace-nowrap brand-text">{{ $link['label'] }}</span>
                    </a>
                </div>
            @endforeach
        </nav>
    </div>
</div>

<div class="sidebar-popovers-container">
    @foreach (array_merge($links, $advancedLinks) as $link)
        @php $isActive = request()->routeIs($link['route']); @endphp
        <div class="sidebar-popover hidden z-[100]" data-link-route="{{ $link['route'] }}">
            <a href="{{ route($link['route']) }}"
               class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium whitespace-nowrap glass-panel shadow-xl border border-white/10
                      {{ $isActive
                          ? 'nav-link-active'
                          : 'text-gray-500 dark:text-gray-400 hover:bg-white/5 dark:hover:bg-white/5 hover:text-gray-700 dark:hover:text-gray-200' }}"
            >
                <span class="w-5 h-5 flex items-center justify-center flex-shrink-0 opacity-70">
                    @if ($link['icon'] === 'dashboard')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    @elseif ($link['icon'] === 'chat')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                    @elseif ($link['icon'] === 'play')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    @elseif ($link['icon'] === 'chart')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    @elseif ($link['icon'] === 'prompt-lab')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    @elseif ($link['icon'] === 'dollar')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    @elseif ($link['icon'] === 'bell')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    @elseif ($link['icon'] === 'heart')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                    @elseif ($link['icon'] === 'shield')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    @elseif ($link['icon'] === 'bolt')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    @endif
                </span>
                <span>{{ $link['label'] }}</span>
            </a>
        </div>
    @endforeach
</div>

<style>
    .orbit-brand-title {
        font-family: 'Orbitron', sans-serif;
        white-space: nowrap;
        letter-spacing: 0.08em;
    }
    .orbit-brand-gradient {
        background: linear-gradient(135deg, #60a5fa, #a78bfa, #f472b6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        filter: drop-shadow(0 0 10px rgba(139, 92, 246, 0.5));
    }
    html:not(.dark) .orbit-brand-gradient {
        background: linear-gradient(135deg, #4f46e5, #7c3aed, #ec4899);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        filter: drop-shadow(0 0 8px rgba(99, 102, 241, 0.4));
    }
    html:not(.dark) .orbit-brand-title span:first-child {
        color: #475569;
    }
    .sidebar-collapsed .brand-text,
    .sidebar-collapsed .advanced-divider {
        display: none;
    }
    .brand-text {
        opacity: 0;
        transition: opacity 0.1s ease-in-out;
        transition-delay: 0s;
    }
    [data-sidebar]:not(.sidebar-collapsed) .brand-text {
        opacity: 1;
        transition-delay: 0.15s;
    }
    .sidebar-collapsed nav a {
        justify-content: center;
        padding: 10px;
    }
    .sidebar-collapsed nav a span:first-child {
        margin: 0;
    }
    .sidebar-collapsed .sidebar-nav-item:hover ~ .sidebar-popovers-container .sidebar-popover.show,
    .sidebar-popover.show {
        display: block;
    }
    .sidebar-collapsed .nav-link-active:hover {
        border-color: rgba(99, 102, 241, 0.5);
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.3), rgba(139, 92, 246, 0.25));
    }
    html:not(.dark) .sidebar-collapsed .nav-link-active:hover {
        border-color: rgba(99, 102, 241, 0.35);
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(139, 92, 246, 0.15));
    }
</style>

<script>
    (function() {
        var sidebar = document.querySelector('[data-sidebar]');
        if (!sidebar) return;
        var items = sidebar.querySelectorAll('.sidebar-nav-item');
        var popovers = document.querySelectorAll('.sidebar-popover[data-link-route]');

        items.forEach(function(item, index) {
            var link = item.querySelector('a');
            var route = link ? link.getAttribute('href') : '';
            var popover = document.querySelector('.sidebar-popover[data-link-route]');

            item.addEventListener('mouseenter', function() {
                if (!sidebar.classList.contains('sidebar-collapsed')) return;
                var route = this.querySelector('a').getAttribute('href');
                // find matching popover
                popovers.forEach(function(p) {
                    var pr = p.querySelector('a').getAttribute('href');
                    if (pr === route) {
                        var itemRect = item.getBoundingClientRect();
                        p.style.position = 'fixed';
                        p.style.top = itemRect.top + 'px';
                        p.style.left = itemRect.left + 'px';
                        p.classList.add('show');
                    }
                });
            });

            item.addEventListener('mouseleave', function() {
                popovers.forEach(function(p) {
                    p.classList.remove('show');
                });
            });
        });

        popovers.forEach(function(p) {
            p.addEventListener('mouseenter', function() {
                this.classList.add('show');
            });
            p.addEventListener('mouseleave', function() {
                this.classList.remove('show');
            });
        });
    })();
</script>

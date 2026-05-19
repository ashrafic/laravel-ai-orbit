<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Laravel') }} — AI Orbit</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('vendor/ai-orbit/logo/favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('vendor/ai-orbit/logo/favicon-32.png') }}">
    <link rel="apple-touch-icon" sizes="32x32" href="{{ asset('vendor/ai-orbit/logo/favicon-32.png') }}">

    <link rel="stylesheet" href="{{ asset('vendor/ai-orbit/css/orbit.css') }}">

    @livewireStyles

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Orbitron:wght@500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <script>
        (function() {
            const stored = localStorage.getItem('orbit-theme');
            if (stored === 'light') {
                document.documentElement.classList.remove('dark');
            } else if (stored === 'dark') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>
<body class="bg-gray-100 dark:bg-[#111827] text-gray-900 dark:text-gray-100 min-h-screen font-['Inter',system-ui,sans-serif]">

    <div class="flex min-h-screen p-3 gap-3 dark:bg-gradient-to-br dark:from-[#111827] dark:via-[#1e1b4b] dark:to-[#111827] bg-gradient-to-br from-slate-50 via-indigo-50 to-slate-50">

        {{-- Ambient Glow Blobs --}}
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="ambient-glow-1"></div>
            <div class="ambient-glow-2"></div>
        </div>

        {{-- Sidebar --}}
        <x-ai-orbit::nav />

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col min-w-0 relative z-10">

            {{-- Top Bar --}}
            <header class="glass-topbar h-[52px] flex items-center justify-between px-4 flex-shrink-0 mb-4">
                <div class="flex items-center gap-3">
                    {{-- Collapse Toggle --}}
                    <button
                        id="sidebar-toggle"
                        onclick="toggleSidebar()"
                        class="w-8 h-8 glass-panel rounded-lg text-gray-400 dark:text-gray-400 hover:text-gray-200 dark:hover:text-gray-200 flex items-center justify-center transition-colors"
                        title="Toggle sidebar"
                    >
                        <svg id="collapse-icon" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="18" height="18" rx="2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9 3v18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="m14 9 3 3-3 3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <svg id="expand-icon" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="18" height="18" rx="2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9 3v18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="m14 12-3-3 3-3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <span id="breadcrumb" class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ $breadcrumb ?? 'Dashboard' }}</span>
                </div>

                <div class="flex items-center gap-4">
                    <a href="{{ config('ai-orbit.back_to_app_url', '/') }}"
                       class="glass-panel rounded-lg px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:border-orbit-300 dark:hover:border-orbit-700 transition-all flex items-center gap-1.5"
                       title="Back to App"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        <span class="hidden sm:inline">Back to App</span>
                    </a>
                    <div class="status-dot"></div>
                    <x-ai-orbit::theme-toggle />
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 pb-6 overflow-x-hidden">

                {{-- Health Check Warnings --}}
                @php $healthIssues = \Ashrafic\AiOrbit\Orbit::healthCheck(); @endphp
                @if (!empty($healthIssues))
                    <div x-data="{ dismissed: false }" x-show="!dismissed" class="mb-6 space-y-3">
                        @foreach ($healthIssues as $issue)
                            <div class="flex items-start gap-3 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl shadow-sm">
                                <div class="flex-shrink-0 mt-0.5">
                                    <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Setup Required</p>
                                    <p class="mt-0.5 text-sm text-yellow-700 dark:text-yellow-300">{{ $issue['message'] }}</p>
                                </div>
                                <button @click="dismissed = true" type="button" class="flex-shrink-0 text-yellow-500 hover:text-yellow-700 dark:hover:text-yellow-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif

                @isset($slot)
                    {{ $slot }}
                @else
                    @yield('content')
                @endisset
            </main>
        </div>
    </div>

    {{-- Livewire Scripts --}}
    @livewireScripts

    {{-- Orbit Guard --}}
    <script>
        (function() {
            if (window.top !== window.self) {
                window.top.location.href = window.location.href;
                return;
            }

            if (typeof window.Livewire !== 'undefined') {
                document.addEventListener('livewire:initialized', function () {
                    window.Livewire.hook('commit', function ({ fail }) {
                        fail(function ({ status, preventDefault }) {
                            if (status >= 400 || status === 0) {
                                preventDefault();
                                window.location.reload();
                            }
                        });
                    });
                });
            }
        })();
    </script>

    {{-- Sidebar Collapse State --}}
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('[data-sidebar]');
            const collapseIcon = document.getElementById('collapse-icon');
            const expandIcon = document.getElementById('expand-icon');
            if (!sidebar) return;

            const isCollapsed = sidebar.classList.contains('sidebar-collapsed');

            if (isCollapsed) {
                sidebar.classList.remove('sidebar-collapsed');
                sidebar.style.width = '';
                collapseIcon.classList.remove('hidden');
                expandIcon.classList.add('hidden');
                localStorage.setItem('orbit-sidebar-collapsed', 'false');
            } else {
                sidebar.classList.add('sidebar-collapsed');
                sidebar.style.width = '64px';
                collapseIcon.classList.add('hidden');
                expandIcon.classList.remove('hidden');
                localStorage.setItem('orbit-sidebar-collapsed', 'true');
            }
        }

        (function() {
            const sidebar = document.querySelector('[data-sidebar]');
            const collapseIcon = document.getElementById('collapse-icon');
            const expandIcon = document.getElementById('expand-icon');
            if (!sidebar) return;

            const collapsed = localStorage.getItem('orbit-sidebar-collapsed') === 'true';
            if (collapsed) {
                sidebar.classList.add('sidebar-collapsed');
                sidebar.style.width = '64px';
                collapseIcon.classList.add('hidden');
                expandIcon.classList.remove('hidden');
            } else {
                collapseIcon.classList.remove('hidden');
                expandIcon.classList.add('hidden');
            }
        })();
    </script>

    @stack('scripts')
    @once
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @endonce
</body>
</html>

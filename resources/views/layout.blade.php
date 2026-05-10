<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Laravel') }} — AI Orbit</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        orbit: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>

    @livewireStyles

    <style>
        [x-cloak] { display: none !important; }

        .sidebar-transition {
            transition: transform 0.2s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-950 text-gray-900 dark:text-gray-100 min-h-screen">

    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        <x-laravel-ai-orbit::nav />

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col min-w-0">

            {{-- Top Bar --}}
            <header class="h-14 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 flex items-center justify-between px-4 lg:px-6 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <button
                        onclick="document.querySelector('[data-sidebar]').classList.toggle('-translate-x-full'); document.querySelector('[data-sidebar]').classList.toggle('max-sm:translate-x-0')"
                        class="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h1 class="text-sm font-semibold text-gray-700 dark:text-gray-300 tracking-wide uppercase">
                        AI Orbit
                    </h1>
                </div>

                <div class="flex items-center gap-3">
                    <x-laravel-ai-orbit::theme-toggle />
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 p-4 lg:p-6 overflow-x-hidden">

                {{-- Health Check Warnings --}}
                @php $healthIssues = \Ashraf\LaravelAiOrbit\Orbit::healthCheck(); @endphp
                @if (!empty($healthIssues))
                    <div x-data="{ dismissed: false }" x-show="!dismissed" class="mb-6 space-y-3">
                        @foreach ($healthIssues as $issue)
                            <div class="flex items-start gap-3 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg shadow-sm">
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

                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- Livewire Scripts --}}
    @livewireScripts

    {{-- Theme Persistence --}}
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

    @stack('scripts')
</body>
</html>

@extends('ai-orbit::components.layout')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-8">
        <a href="{{ route('orbit.arena.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">&larr; Back to Arena</a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-2">Session #{{ $session->id }}</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $session->created_at->format('M d, Y H:i') }} &middot; {{ implode(', ', $session->models) }}</p>
    </div>

    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6 mb-6">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Prompt</h3>
        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $session->prompt }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($session->results as $model => $result)
        <div class="rounded-lg border {{ $result['success'] ? 'border-gray-200 dark:border-gray-700' : 'border-red-300 dark:border-red-700' }} bg-white dark:bg-gray-900 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <span class="font-semibold text-gray-900 dark:text-white">{{ $model }}</span>
            </div>
            <div class="p-4">
                @if($result['success'])
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $result['content'] }}</p>
                @else
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $result['error'] ?? 'Unknown error' }}</p>
                @endif
            </div>
            <div class="px-4 py-2 bg-gray-50 dark:bg-gray-800 text-xs text-gray-500 dark:text-gray-400">
                {{ $result['latency_ms'] }}ms &middot; {{ $result['tokens'] }} tokens
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

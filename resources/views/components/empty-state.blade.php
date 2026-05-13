@props(['icon' => null, 'title' => 'No data found', 'description' => null, 'message' => null])

@php
    $displayDescription = $description ?? $message;
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center py-12 text-center']) }}>
    @if ($icon)
        <div class="text-gray-300 dark:text-gray-600 mb-3">
            {!! $icon !!}
        </div>
    @else
        <div class="text-gray-300 dark:text-gray-600 mb-3">
            <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
        </div>
    @endif

    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $title }}</h3>

    @if ($displayDescription)
        <p class="mt-1 text-sm text-gray-400 dark:text-gray-500">{{ $displayDescription }}</p>
    @endif

    {{ $slot }}
</div>

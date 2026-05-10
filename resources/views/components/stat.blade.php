@props(['value' => 0, 'label' => '', 'icon' => null, 'color' => 'orbit'])

@php
    $colorClasses = [
        'orbit' => 'text-orbit-500',
        'blue' => 'text-blue-500',
        'green' => 'text-green-500',
        'red' => 'text-red-500',
        'yellow' => 'text-yellow-500',
        'purple' => 'text-purple-500',
    ][$color] ?? 'text-orbit-500';
@endphp

<div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 p-4 shadow-sm">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $value }}</p>
        </div>
        @if ($icon)
            <span class="{{ $colorClasses }}">{{ $icon }}</span>
        @endif
    </div>
</div>

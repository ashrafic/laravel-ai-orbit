@props(['label' => '', 'color' => 'gray', 'variant' => null])

@php
    $colorMap = $variant ? match($variant) {
        'success' => 'green',
        'danger' => 'red',
        'warning' => 'yellow',
        'info' => 'blue',
        default => 'gray',
    } : $color;

    $colorClasses = match($colorMap) {
        'gray' => 'bg-gray-100 text-gray-600 dark:bg-white/5 dark:text-gray-400',
        'blue' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
        'green' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
        'red' => 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400',
        'yellow' => 'bg-yellow-50 text-yellow-600 dark:bg-yellow-500/10 dark:text-yellow-400',
        'purple' => 'bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400',
        'orange' => 'bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400',
        default => 'bg-gray-100 text-gray-600 dark:bg-white/5 dark:text-gray-400',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium '.$colorClasses]) }}>
    {{ $label ?: $slot }}
</span>

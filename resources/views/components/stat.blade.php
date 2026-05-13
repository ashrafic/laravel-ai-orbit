@props(['value' => 0, 'label' => '', 'icon' => null, 'color' => 'orbit', 'trend' => null])

@php
    $accentClass = match($color) {
        'orbit', 'blue' => 'accent-bar-indigo',
        'green' => 'accent-bar-emerald',
        'purple' => 'accent-bar-purple',
        'yellow', 'orange' => 'accent-bar-amber',
        default => 'accent-bar-indigo',
    };

    $iconClass = match($color) {
        'orbit', 'blue' => 'icon-container-indigo',
        'green' => 'icon-container-emerald',
        'purple' => 'icon-container-purple',
        'yellow', 'orange' => 'icon-container-amber',
        default => 'icon-container-indigo',
    };
@endphp

<div {{ $attributes->merge(['class' => 'glass-card '.$accentClass.' relative overflow-hidden p-[18px]']) }}>
    <div class="flex items-start justify-between">
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $label }}</p>
            <p class="mt-1.5 text-2xl font-bold text-gray-900 dark:text-gray-50 tracking-tight">{{ $value }}</p>
            @if ($trend)
                <p class="mt-1.5 text-xs font-medium {{ str_starts_with($trend, '↑') || str_starts_with($trend, '+') ? 'text-emerald-500' : 'text-red-500' }}">{{ $trend }}</p>
            @endif
        </div>
        @if ($icon)
            <div class="{{ $iconClass }}">
                {{ $icon }}
            </div>
        @endif
    </div>
</div>

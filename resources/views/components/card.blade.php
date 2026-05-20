@props(['title' => null, 'icon' => null, 'padding' => 'p-4', 'class' => '', 'accent' => null])

@php
    $accentClasses = match($accent) {
        'indigo' => 'accent-bar-indigo',
        'emerald' => 'accent-bar-emerald',
        'purple' => 'accent-bar-purple',
        'amber' => 'accent-bar-amber',
        default => '',
    };
@endphp

<div {{ $attributes->merge(['class' => 'glass-card '.$accentClasses.' relative overflow-hidden '.$class]) }}>
    @if ($title || $icon)
        <div class="px-4 py-3 border-b border-gray-200/30 dark:border-white/5 flex items-center gap-2">
            @if ($icon)
                <span class="text-orbit-500">{!! $icon !!}</span>
            @endif
            @if ($title)
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $title }}</h3>
            @endif
        </div>
    @endif

    <div class="{{ $padding }}">
        {{ $slot }}
    </div>
</div>

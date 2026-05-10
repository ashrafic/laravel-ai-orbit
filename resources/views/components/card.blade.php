@props(['title' => null, 'icon' => null, 'padding' => 'p-4', 'class' => ''])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm '.$class]) }}>
    @if ($title || $icon)
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-800 flex items-center gap-2">
            @if ($icon)
                <span class="text-orbit-500">{!! $icon !!}</span>
            @endif
            @if ($title)
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $title }}</h3>
            @endif
        </div>
    @endif

    <div class="{{ $padding }}">
        {{ $slot }}
    </div>
</div>

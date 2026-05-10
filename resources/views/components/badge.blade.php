@props(['label' => '', 'color' => 'gray'])

@php
    $colorClasses = [
        'gray' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
        'green' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
        'red' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
        'yellow' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300',
        'purple' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
        'orange' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
    ][$color] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium '.$colorClasses]) }}>
    {{ $label }}
</span>

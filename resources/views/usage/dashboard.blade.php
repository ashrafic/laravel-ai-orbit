@extends('ai-orbit::components.layout')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Usage Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Historical AI cost and token analytics.</p>
    </div>

    <livewire:ai-orbit.cost-dashboard />
</div>
@endsection

@extends('ai-orbit::components.layout')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Arena</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Compare prompts across multiple AI models side-by-side.</p>
    </div>

    <livewire:ai-orbit.arena-compare />

    @if($sessions->isNotEmpty())
    <div class="mt-12">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Session History</h2>
        <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Models</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                    @foreach($sessions as $session)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300">#{{ $session->id }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ implode(', ', $session->models) }}</td>
                        <td class="px-4 py-3">
                            <x-ai-orbit::badge :variant="$session->status === 'completed' ? 'success' : 'warning'">
                                {{ ucfirst($session->status) }}
                            </x-ai-orbit::badge>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $session->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('orbit.arena.show', $session->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline text-sm">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $sessions->links() }}
    </div>
    @endif
</div>
@endsection

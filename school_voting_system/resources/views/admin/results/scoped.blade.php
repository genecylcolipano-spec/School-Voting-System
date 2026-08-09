<x-app-layout>
    <x-admin-portal :title="$title" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => $title,
            'description' => $description,
            'showAction' => false,
        ])

        <div class="mb-5 flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.results.elections') }}" class="rounded-full px-4 py-1.5 text-sm font-semibold transition {{ $mode === 'election' ? 'bg-gradient-to-r from-violet-600 to-indigo-500 text-white' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">Election Results</a>
            <a href="{{ route('admin.results.competitions') }}" class="rounded-full px-4 py-1.5 text-sm font-semibold transition {{ $mode === 'talent' ? 'bg-gradient-to-r from-violet-600 to-indigo-500 text-white' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">Talent Competition Results</a>
            <a href="{{ route('admin.results.index') }}" class="ml-auto text-sm font-semibold text-violet-300 hover:text-violet-200">All results →</a>
        </div>

        <div class="mb-4 rounded-xl border border-violet-500/15 bg-slate-900/60 px-4 py-3 text-xs text-slate-400">
            Open any result to publish/unpublish, export as PDF, Excel, CSV, or print. Students only see results after they are published.
        </div>

        @if ($events->isEmpty())
            <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-violet-500/20 bg-slate-900/50 px-6 py-16 text-center">
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-violet-500/10 text-3xl">🏆</div>
                <h2 class="text-xl font-bold text-white">No {{ $mode === 'talent' ? 'Talent Competition' : 'Election' }} Results</h2>
                <p class="mt-2 max-w-md text-sm text-slate-400">Results will appear here once {{ $mode === 'talent' ? 'competitions' : 'elections' }} in your scope have voting activity.</p>
            </div>
        @else
            <p class="mb-4 text-sm text-slate-400">{{ $events->count() }} {{ \Illuminate\Support\Str::plural($mode === 'talent' ? 'competition' : 'election', $events->count()) }} in your scope</p>
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($events as $event)
                    @include('admin.results._event-card', ['event' => $event])
                @endforeach
            </div>
        @endif
    </x-admin-portal>

    @vite(['resources/css/admin-live-voting.css', 'resources/css/admin-results.css'])
</x-app-layout>

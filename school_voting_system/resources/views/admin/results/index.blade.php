<x-app-layout>
    <x-admin-portal title="Results" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'Results',
            'description' => 'View official results for all elections and voting-based events.',
            'showAction' => false,
        ])

        @if (! $hasEvents)
            <div class="rs-empty flex flex-col items-center justify-center rounded-2xl border border-dashed border-violet-500/20 bg-slate-900/50 px-6 py-16 text-center">
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-violet-500/10 text-3xl">🏆</div>
                <h2 class="text-xl font-bold text-white">No Results Available</h2>
                <p class="mt-2 max-w-md text-sm text-slate-400">Results will appear here once elections or talent competitions are set up in your scope.</p>
            </div>
        @else
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-slate-400">{{ $events->count() }} voting event{{ $events->count() === 1 ? '' : 's' }} in your scope</p>
                @if ($filterOptions->isNotEmpty())
                    <form method="GET" action="{{ route('admin.results.index') }}" class="flex items-center gap-2">
                        <label for="event-filter" class="sr-only">Filter events</label>
                        <select
                            id="event-filter"
                            name="event"
                            onchange="this.form.submit()"
                            class="rounded-xl border border-slate-700 bg-slate-950/60 px-4 py-2 text-sm text-white focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500"
                        >
                            <option value="">All voting events</option>
                            @foreach ($filterOptions as $option)
                                <option value="{{ $option['key'] }}" @selected($selectedEvent === $option['key'])>{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                        @if ($selectedEvent)
                            <a href="{{ route('admin.results.index') }}" class="text-sm font-semibold text-violet-300 hover:text-violet-200">Clear</a>
                        @endif
                    </form>
                @endif
            </div>

            @php
                $typeFilter = request('type');
                $visibleEvents = collect($events)->filter(function ($event) use ($typeFilter) {
                    if (! in_array($typeFilter, ['election', 'talent'], true)) {
                        return true;
                    }
                    $isTalent = str_contains($event['show_url'] ?? '', '/results/talent/');
                    return $typeFilter === 'talent' ? $isTalent : ! $isTalent;
                });
            @endphp

            @if ($visibleEvents->isEmpty())
                <div id="{{ $typeFilter === 'talent' ? 'talent-results' : 'election-results' }}" class="rounded-2xl border border-violet-500/15 bg-slate-900/70 px-6 py-12 text-center text-slate-400">
                    No {{ $typeFilter === 'talent' ? 'talent competition' : ($typeFilter === 'election' ? 'election' : '') }} results match the selected filter.
                </div>
            @else
                <div id="{{ $typeFilter === 'talent' ? 'talent-results' : 'election-results' }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($visibleEvents as $event)
                        @include('admin.results._event-card', ['event' => $event])
                    @endforeach
                </div>
            @endif
        @endif
    </x-admin-portal>

    @vite(['resources/css/admin-live-voting.css', 'resources/css/admin-results.css'])
</x-app-layout>

<x-app-layout>
    <x-faculty-portal title="{{ $competition->title }}" :user="$user" :notifications-count="$notificationsCount">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('faculty.judging.index') }}" class="text-sm font-semibold text-teal-300 hover:text-teal-200">&larr; Assigned competitions</a>
            <div class="flex flex-wrap items-center justify-end gap-2">
                @if ($competition->hasPublishedResults())
                    <a
                        href="{{ route('faculty.results.talent.show', [$competition, 'from' => 'assigned']) }}"
                        class="rounded-xl border border-teal-500/30 bg-teal-500/10 px-3 py-1.5 text-sm font-semibold text-teal-100 transition hover:bg-teal-500/20"
                    >
                        View official results
                    </a>
                @endif
                <x-faculty.judging-phase-badge :event="$competition" show-opens-at class="text-right" />
            </div>
        </div>

        <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-white">{{ $competition->title }}</h2>
                    <p class="mt-1 text-sm text-slate-400">{{ $competition->votingMethodLabel() }} · {{ $competition->displayStatusLabel() }}</p>
                </div>
                <div class="text-right text-sm text-slate-400">
                    <p>{{ $progress['submitted'] }}/{{ $progress['approved'] }} submitted</p>
                    <p>{{ $progress['remaining'] }} remaining</p>
                </div>
            </div>
        </section>

        <div class="space-y-3">
            @forelse ($entries as $entry)
                @include('faculty.judging._performance-row', [
                    'competition' => $competition,
                    'entry' => $entry,
                    'sheet' => $sheets->get($entry->id),
                ])
            @empty
                <div class="rounded-2xl border border-dashed border-slate-700 px-4 py-10 text-center text-sm text-slate-500">
                    No approved performances are ready for judging yet.
                </div>
            @endforelse
        </div>
    </x-faculty-portal>
</x-app-layout>

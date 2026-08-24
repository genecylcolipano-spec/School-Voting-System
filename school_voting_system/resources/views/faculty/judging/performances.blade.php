<x-app-layout>
    <x-faculty-portal title="Judge Performances" :user="$user" :notifications-count="$notificationsCount">
        <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
            <p class="text-sm text-slate-400">
                Approved performances for competitions that are still scheduled or open. Open a performance to score it. Finished assignments stay under Assigned Competitions → Past and Submitted Scores.
            </p>
        </section>

        <div class="space-y-6">
            @forelse ($rows as $row)
                @php
                    $competition = $row['competition'];
                    $p = $row['progress'];
                @endphp
                <section class="space-y-3">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-lg font-semibold text-white">{{ $competition->title }}</h2>
                                <x-faculty.judging-phase-badge :event="$competition" show-opens-at />
                            </div>
                            <p class="mt-1 text-sm text-slate-400">
                                @if ($p['approved'] === 0)
                                    No approved performances yet
                                @elseif ($row['needs_work'])
                                    {{ $p['remaining'] }} of {{ $p['approved'] }} still need submitted scores
                                @else
                                    All {{ $p['approved'] }} approved performances have submitted scores
                                @endif
                            </p>
                        </div>
                        <a href="{{ route('faculty.judging.show', $competition) }}" class="text-sm font-semibold text-teal-300 hover:text-teal-200">
                            Open judging
                        </a>
                    </div>

                    @forelse ($row['entries'] as $entry)
                        @include('faculty.judging._performance-row', [
                            'competition' => $competition,
                            'entry' => $entry,
                            'sheet' => $row['sheets']->get($entry->id),
                            'from' => 'performances',
                        ])
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-700 px-4 py-8 text-center text-sm text-slate-500">
                            No approved performances are ready for judging yet.
                        </div>
                    @endforelse
                </section>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-700 px-4 py-10 text-center text-sm text-slate-500">
                    No current competitions to score. Review finished work under Assigned Competitions → Past, or Submitted Scores.
                </div>
            @endforelse
        </div>
    </x-faculty-portal>
</x-app-layout>

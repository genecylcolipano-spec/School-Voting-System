<x-app-layout>
    <x-faculty-portal title="Judge Performances" :user="$user" :notifications-count="$notificationsCount">
        <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
            <p class="text-sm text-slate-400">
                Continue scoring approved performances for your assigned competitions.
            </p>
        </section>

        <div class="space-y-4">
            @forelse ($rows as $row)
                @php
                    $competition = $row['competition'];
                    $p = $row['progress'];
                @endphp
                <article class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-white">{{ $competition->title }}</h2>
                            <p class="mt-1 text-sm text-slate-400">
                                @if ($row['needs_work'])
                                    {{ $p['remaining'] }} performance{{ $p['remaining'] === 1 ? '' : 's' }} still need submitted scores
                                @else
                                    All approved performances have submitted scores
                                @endif
                            </p>
                        </div>
                        <a
                            href="{{ route('faculty.judging.show', $competition) }}"
                            class="rounded-xl {{ $row['needs_work'] ? 'bg-gradient-to-r from-teal-500 to-emerald-400 text-slate-950' : 'border border-slate-700 text-slate-300 hover:bg-slate-800' }} px-4 py-2 text-sm font-semibold"
                        >
                            {{ $row['needs_work'] ? 'Continue judging' : 'Review' }}
                        </a>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-700 px-4 py-10 text-center text-sm text-slate-500">
                    No assigned competitions to judge.
                </div>
            @endforelse
        </div>
    </x-faculty-portal>
</x-app-layout>

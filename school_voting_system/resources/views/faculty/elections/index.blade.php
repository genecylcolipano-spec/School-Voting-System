<x-app-layout>
    <x-faculty-portal title="Elections" :user="$user" :notifications-count="$notificationsCount">
        <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
            <p class="text-sm text-slate-400">
                @if ($showOpenOnly)
                    Showing elections currently open for voting. Faculty accounts are view-only and cannot cast votes.
                @else
                    Browse school elections and candidate lineups. Faculty accounts are view-only and cannot cast votes.
                @endif
            </p>

            <nav class="mt-4 flex flex-wrap gap-2" aria-label="Election lists">
                <a
                    href="{{ route('faculty.elections.index') }}"
                    @class([
                        'inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold transition',
                        'bg-gradient-to-r from-teal-500 to-emerald-400 text-slate-950' => ! $showOpenOnly,
                        'border border-slate-700 text-slate-300 hover:bg-slate-800' => $showOpenOnly,
                    ])
                >
                    All
                    <span class="rounded-full bg-black/10 px-1.5 py-0.5 text-[11px] leading-none">{{ $allCount }}</span>
                </a>
                <a
                    href="{{ route('faculty.elections.index', ['filter' => 'open']) }}"
                    @class([
                        'inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold transition',
                        'bg-gradient-to-r from-teal-500 to-emerald-400 text-slate-950' => $showOpenOnly,
                        'border border-slate-700 text-slate-300 hover:bg-slate-800' => ! $showOpenOnly,
                    ])
                >
                    Open
                    <span class="rounded-full bg-black/10 px-1.5 py-0.5 text-[11px] leading-none">{{ $openCount }}</span>
                </a>
            </nav>
        </section>

        <div class="space-y-4">
            @forelse ($elections as $election)
                <article class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <h2 class="text-lg font-semibold text-white">{{ $election->title }}</h2>
                            @if ($election->description)
                                <p class="mt-1 text-sm text-slate-300 line-clamp-2">{{ $election->description }}</p>
                            @endif
                        </div>
                        <div class="sm:shrink-0 sm:text-right">
                            @include('faculty.elections._status-badge', ['election' => $election])
                            @if ($election->voting_starts_at)
                                <p class="mt-1 text-xs text-slate-400">Starts: {{ $election->voting_starts_at->format('M d, Y g:i A') }}</p>
                            @endif
                            @if ($election->voting_ends_at)
                                <p class="text-xs text-slate-400">Ends: {{ $election->voting_ends_at->format('M d, Y g:i A') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                        <a
                            href="{{ route('faculty.elections.show', $election) }}"
                            class="inline-flex min-h-10 w-full items-center justify-center rounded-xl bg-gradient-to-r from-teal-500 to-emerald-400 px-4 py-2 text-sm font-semibold text-slate-950 sm:w-auto"
                        >
                            View details
                        </a>
                        @if ($election->shouldShowOfficialResultsToStudents())
                            <a
                                href="{{ route('faculty.results.election.show', $election) }}"
                                class="inline-flex min-h-10 w-full items-center justify-center rounded-xl border border-teal-400/40 px-4 py-2 text-sm font-semibold text-teal-100 sm:w-auto"
                            >
                                View official results
                            </a>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-700 px-4 py-8 text-center text-sm text-slate-500">
                    {{ $showOpenOnly ? 'No elections are currently open for voting.' : 'No elections found.' }}
                </div>
            @endforelse
        </div>

        <div>{{ $elections->links() }}</div>
    </x-faculty-portal>
</x-app-layout>

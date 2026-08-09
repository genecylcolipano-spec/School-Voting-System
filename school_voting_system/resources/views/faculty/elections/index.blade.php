<x-app-layout>
    <x-faculty-portal title="Elections" :user="$user" :notifications-count="$notificationsCount">
        <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
            <p class="text-sm text-slate-400">
                Browse school elections and candidate lineups. Faculty accounts are view-only and cannot cast votes.
            </p>
        </section>

        <div class="space-y-4">
            @forelse ($elections as $election)
                <article class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h2 class="text-lg font-semibold text-white">{{ $election->title }}</h2>
                            @if ($election->description)
                                <p class="mt-1 text-sm text-slate-300 line-clamp-2">{{ $election->description }}</p>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="text-xs uppercase tracking-wide text-slate-500">{{ $election->status?->value ?? $election->status }}</p>
                            @if ($election->voting_starts_at)
                                <p class="mt-1 text-xs text-slate-400">Starts: {{ $election->voting_starts_at->format('M d, Y g:i A') }}</p>
                            @endif
                            @if ($election->voting_ends_at)
                                <p class="text-xs text-slate-400">Ends: {{ $election->voting_ends_at->format('M d, Y g:i A') }}</p>
                            @endif
                        </div>
                    </div>

                    <a
                        href="{{ route('faculty.elections.show', $election) }}"
                        class="mt-4 inline-block rounded-xl bg-gradient-to-r from-teal-500 to-emerald-400 px-4 py-2 text-sm font-semibold text-slate-950"
                    >
                        View details
                    </a>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-700 px-4 py-8 text-center text-sm text-slate-500">
                    No elections found.
                </div>
            @endforelse
        </div>

        <div>{{ $elections->links() }}</div>
    </x-faculty-portal>
</x-app-layout>

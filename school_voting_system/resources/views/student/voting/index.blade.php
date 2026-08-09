<x-app-layout>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-white">Voting</h1>
                    <p class="mt-1 text-sm text-slate-400">Open elections and voting history.</p>
                </div>
                <a href="{{ route('student.dashboard') }}" class="rounded-xl border border-cyan-500/25 bg-slate-900 px-4 py-2 text-sm font-semibold text-cyan-300 hover:bg-slate-800">
                    Back to dashboard
                </a>
            </div>

            @if (session('error'))
                <div class="mb-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('success'))
                <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                    {{ session('success') }}
                </div>
            @endif

            <div class="space-y-4">
                @forelse ($elections as $election)
                    <article class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-5">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-white">{{ $election->title }}</h2>
                                @if ($election->description)
                                    <p class="mt-1 text-sm text-slate-300 line-clamp-2">{{ $election->description }}</p>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="text-xs uppercase tracking-wide text-slate-500">{{ $election->status?->value ?? $election->status }}</p>
                                <p class="mt-1 text-xs text-slate-400">
                                    @if ($election->voting_starts_at)
                                        Starts: {{ $election->voting_starts_at->format('M d, Y g:i A') }}
                                    @endif
                                </p>
                                <p class="text-xs text-slate-400">
                                    @if ($election->voting_ends_at)
                                        Ends: {{ $election->voting_ends_at->format('M d, Y g:i A') }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        @php
                            $availability = $electionService->votingAvailability($election, $student);
                        @endphp

                        @if ($availability['state'] === 'open')
                            <div class="mt-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3">
                                <p class="text-sm font-semibold text-emerald-200">🟢 {{ $availability['title'] }}</p>
                                <p class="mt-1 text-sm text-emerald-100/80">{{ $availability['message'] }}</p>
                                @if ($availability['submessage'])
                                    <p class="mt-1 text-sm text-emerald-100/70">{{ $availability['submessage'] }}</p>
                                @endif
                            </div>
                            <a href="{{ route('student.voting.show', $election) }}" class="mt-4 inline-block rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-4 py-2 text-sm font-semibold text-slate-950">
                                Vote Now
                            </a>
                        @elseif ($availability['state'] === 'voted')
                            <div class="mt-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3">
                                <p class="text-sm font-semibold text-emerald-200">✅ {{ $availability['title'] }}</p>
                                <p class="mt-1 text-sm text-emerald-100/80">{{ $availability['message'] }}</p>
                                @if ($availability['submessage'])
                                    <p class="mt-1 text-sm text-emerald-100/70">{{ $availability['submessage'] }}</p>
                                @endif
                            </div>
                        @elseif ($availability['state'] === 'results_published')
                            <div class="mt-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3">
                                <p class="text-sm font-semibold text-emerald-200">🏆 {{ $availability['title'] }}</p>
                                @if ($availability['message'])
                                    <p class="mt-1 text-sm text-emerald-100/80">{{ $availability['message'] }}</p>
                                @endif
                                @if ($availability['submessage'])
                                    <p class="mt-1 text-sm text-emerald-100/70">{{ $availability['submessage'] }}</p>
                                @endif
                            </div>
                            <a href="{{ route('student.results.election.show', $election) }}" class="mt-4 inline-block rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-4 py-2 text-sm font-semibold text-slate-950">
                                View Results
                            </a>
                        @elseif ($availability['state'] === 'not_started')
                            <p class="mt-4 rounded-xl border border-amber-500/20 bg-amber-500/10 px-4 py-3 text-sm text-amber-200">
                                {{ $availability['message'] }}
                            </p>
                            @if ($election->voting_starts_at)
                                <p class="mt-2 text-xs text-slate-400">
                                    Opens {{ $election->voting_starts_at->format('M d, Y g:i A') }}
                                </p>
                            @endif
                        @elseif ($availability['state'] === 'under_review')
                            <div class="mt-4 rounded-xl border border-amber-500/20 bg-amber-500/10 px-4 py-3">
                                <p class="text-sm font-semibold text-amber-200">🟡 {{ $availability['title'] }}</p>
                                <p class="mt-1 text-sm text-amber-100/80">{{ $availability['message'] }}</p>
                                @if ($availability['submessage'])
                                    <p class="mt-1 text-sm text-amber-100/70">{{ $availability['submessage'] }}</p>
                                @endif
                            </div>
                        @elseif ($availability['message'])
                            <p class="mt-4 rounded-xl border border-slate-700 bg-slate-950/40 px-4 py-3 text-sm text-slate-300">
                                {{ $availability['message'] }}
                                @if ($availability['submessage'])
                                    <span class="mt-1 block text-slate-400">{{ $availability['submessage'] }}</span>
                                @endif
                            </p>
                        @else
                            <p class="mt-4 text-sm text-slate-400">Voting is not currently available.</p>
                        @endif
                    </article>
                @empty
                    <div class="rounded-2xl border border-slate-800 bg-slate-900/50 p-6 text-slate-300">
                        No elections found.
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $elections->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

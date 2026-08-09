<x-app-layout>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between gap-4">
                <a href="{{ route('student.voting.index') }}" class="text-sm font-semibold text-cyan-300 hover:text-cyan-200">← Back to voting</a>
                <a href="{{ route('student.dashboard') }}" class="text-sm text-slate-300 hover:text-white">Dashboard</a>
            </div>

            @if (session('error'))
                <div class="mb-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                    {{ session('error') }}
                </div>
            @endif

            <div class="rounded-2xl border border-amber-500/20 bg-slate-900/70 p-8 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-amber-500/10 text-amber-300">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-white">{{ $election->title }}</h1>

                @if ($election->description)
                    <p class="mx-auto mt-3 max-w-xl text-sm text-slate-400">{{ $election->description }}</p>
                @endif

                <p class="mx-auto mt-6 max-w-md text-base text-amber-200">{{ $message }}</p>

                @if ($election->isBeforeVotingStart() && $election->voting_starts_at)
                    <p class="mt-3 text-sm text-slate-400">
                        Opens {{ $election->voting_starts_at->format('M d, Y g:i A') }}
                    </p>
                @elseif ($election->isAfterVotingEnd() && $election->voting_ends_at)
                    <p class="mt-3 text-sm text-slate-400">
                        Closed {{ $election->voting_ends_at->format('M d, Y g:i A') }}
                    </p>
                @endif

                @if ($election->shouldShowOfficialResultsToStudents())
                    <a href="{{ route('student.results.election.show', $election) }}" class="mt-6 inline-block rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-4 py-2 text-sm font-semibold text-slate-950">
                        View Results
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

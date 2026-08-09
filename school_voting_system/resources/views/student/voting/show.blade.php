<x-app-layout>
    @php
        $badgePalette = [
            'bg-violet-500/15 text-violet-200',
            'bg-emerald-500/15 text-emerald-200',
            'bg-amber-500/15 text-amber-200',
            'bg-rose-500/15 text-rose-200',
            'bg-sky-500/15 text-sky-200',
        ];

        // Build ballot data: every category with its active candidates.
        $ballotCategories = $election->categories->map(function ($category) use ($election, $existingVotes, $badgePalette) {
            $candidates = $election->activeCandidates
                ->where('election_category_id', $category->id)
                ->map(function ($candidate) use ($badgePalette) {
                    $grade = $candidate->grade_level ?: $candidate->user?->grade_level;
                    $section = $candidate->section ?: $candidate->user?->section;
                    $party = $candidate->party_or_group ?: 'Independent';

                    return [
                        'id' => $candidate->id,
                        'name' => $candidate->display_name,
                        'party' => $party,
                        'badge' => $party === 'Independent'
                            ? 'bg-slate-700/60 text-slate-300'
                            : $badgePalette[crc32($party) % count($badgePalette)],
                        'platform' => $candidate->platform ? \Illuminate\Support\Str::limit($candidate->platform, 120) : null,
                        'grade' => $grade,
                        'section' => $section,
                        'photo_path' => $candidate->photo_path,
                        'photo' => \App\Support\EventImageUrl::hasUploadedImage($candidate->photo_path)
                            ? \App\Support\EventImageUrl::resolve($candidate->photo_path)
                            : null,
                        'profile_url' => route('student.candidates.show', $candidate),
                    ];
                })
                ->values();

            $lockedCandidate = $existingVotes[$category->id] ?? null;

            return [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'votable' => $candidates->isNotEmpty(),
                'locked' => $lockedCandidate !== null,
                'locked_candidate_id' => $lockedCandidate ? (int) $lockedCandidate : null,
                'candidates' => $candidates,
            ];
        })->values();

        $votableCategories = $ballotCategories->where('votable', true);
        $totalPositions = $votableCategories->count();

        // Alpine seed: preset already-voted positions as locked selections + names.
        $seedSelections = [];
        $seedNames = [];
        foreach ($ballotCategories as $cat) {
            if ($cat['locked'] && $cat['locked_candidate_id']) {
                $seedSelections[(string) $cat['id']] = $cat['locked_candidate_id'];
                $locked = collect($cat['candidates'])->firstWhere('id', $cat['locked_candidate_id']);
                $seedNames[(string) $cat['id']] = $locked['name'] ?? 'Recorded vote';
            }
        }

        $justSubmitted = (bool) ($justSubmitted ?? false);
        $showReceipt = $justSubmitted;
    @endphp

    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between gap-4">
                <a href="{{ route('student.voting.index') }}" class="text-sm font-semibold text-cyan-300 hover:text-cyan-200">&larr; Back to Elections</a>
                <a href="{{ route('student.dashboard') }}" class="text-sm text-slate-300 hover:text-white">Dashboard</a>
            </div>

            @if (session('error'))
                <div class="mb-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                    {{ $errors->first() }}
                </div>
            @endif

            @if ($showReceipt)
                {{-- =========================== SUCCESS / RECEIPT =========================== --}}
                <div class="mx-auto max-w-xl">
                    <div class="rounded-3xl border border-emerald-500/25 bg-slate-900/70 p-8 text-center shadow-lg shadow-emerald-500/5">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-300">
                            <svg class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        </div>
                        <h1 class="mt-5 text-2xl font-bold text-white">Vote Successfully Submitted</h1>
                        <p class="mt-2 text-sm text-slate-300">Thank you for casting your ballot. Your vote has been securely recorded.</p>

                        <dl class="mt-6 space-y-3 rounded-2xl border border-slate-800 bg-slate-950/50 p-5 text-left text-sm">
                            <div class="flex items-center justify-between gap-4">
                                <dt class="text-slate-400">Election</dt>
                                <dd class="font-semibold text-white text-right">{{ $election->title }}</dd>
                            </div>
                            @if ($ballotReceipt ?? null)
                                <div class="flex items-center justify-between gap-4 border-t border-slate-800 pt-3">
                                    <dt class="text-slate-400">Receipt Number</dt>
                                    <dd class="font-mono text-sm font-semibold tracking-wide text-cyan-300">{{ $ballotReceipt->receipt_token }}</dd>
                                </div>
                            @endif
                            @if ($submittedAt)
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-slate-400">Submission Date</dt>
                                    <dd class="font-semibold text-white">{{ $submittedAt->format('M d, Y') }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-slate-400">Submission Time</dt>
                                    <dd class="font-semibold text-white">{{ $submittedAt->format('g:i A') }}</dd>
                                </div>
                            @endif
                        </dl>

                        <p class="mt-6 text-xs text-slate-500">Keep your receipt number for your records. It confirms submission without revealing how you voted. Official results will be published after administrator approval. You cannot edit your vote after submission.</p>

                        <a href="{{ route('student.dashboard') }}" class="mt-6 inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-4 py-3 text-sm font-semibold text-slate-950 transition hover:from-cyan-400 hover:to-sky-300">
                            Return to Dashboard
                        </a>
                    </div>
                </div>
            @else
                {{-- =========================== BALLOT =========================== --}}
                <div
                    x-data="ballot({
                        seed: {{ \Illuminate\Support\Js::from($seedSelections) }},
                        seedNames: {{ \Illuminate\Support\Js::from($seedNames) }},
                        totalPositions: {{ $totalPositions }},
                        endsAt: @js($countdown['ends_at_iso'] ?? null),
                    })"
                    x-init="init()"
                    role="region"
                    aria-label="Election ballot for {{ $election->title }}"
                >
                    {{-- ELECTION HEADER --}}
                    <div class="overflow-hidden rounded-2xl border border-cyan-500/15 bg-slate-900/70">
                        <div class="flex flex-wrap items-start justify-between gap-5 p-6">
                            <div class="flex min-w-0 items-start gap-4">
                                <div class="hidden h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500/20 to-violet-500/20 text-cyan-300 sm:flex">
                                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div class="min-w-0">
                                    <h1 class="text-2xl font-bold text-white">{{ $election->title }}</h1>
                                    @if ($election->description)
                                        <p class="mt-1 max-w-2xl text-sm text-slate-300">{{ $election->description }}</p>
                                    @endif
                                    <span class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-200">
                                        <span class="relative flex h-2 w-2">
                                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                                        </span>
                                        {{ $availability['title'] ?? 'Voting Open' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Live countdown boxes --}}
                            <div class="text-right" aria-live="polite" aria-label="Time remaining until voting ends">
                                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500">Time Remaining</p>
                                <div class="mt-2 flex items-center gap-2" x-show="!cd.none && !cd.closed">
                                    <template x-for="unit in [{ v: cd.h, l: 'Hrs' }, { v: cd.m, l: 'Mins' }, { v: cd.s, l: 'Secs' }]" :key="unit.l">
                                        <div class="flex items-center gap-2">
                                            <div class="min-w-[3rem] rounded-xl border border-cyan-500/20 bg-slate-950/60 px-2.5 py-1.5 text-center">
                                                <p class="text-2xl font-bold tabular-nums text-cyan-300" x-text="unit.v"></p>
                                                <p class="text-[9px] uppercase tracking-wide text-slate-500" x-text="unit.l"></p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <p x-show="cd.none" x-cloak class="mt-2 text-sm text-slate-400">No deadline</p>
                                <p x-show="cd.closed" x-cloak class="mt-2 text-sm font-semibold text-rose-300">Voting Closed</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-x-6 gap-y-1 border-t border-slate-800/70 px-6 py-3 text-xs text-slate-400">
                            @if ($election->voting_starts_at)
                                <span><span class="text-slate-500">Started:</span> {{ $election->voting_starts_at->format('M d, Y g:i A') }}</span>
                            @endif
                            @if ($election->voting_ends_at)
                                <span><span class="text-slate-500">Ends:</span> {{ $election->voting_ends_at->format('M d, Y g:i A') }}</span>
                            @endif
                        </div>

                        {{-- Ballot progress bar --}}
                        <div class="border-t border-slate-800/70 px-6 py-4">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold uppercase tracking-wide text-slate-400">Ballot Progress</span>
                                <span class="font-semibold text-cyan-300">
                                    <span x-text="completedCount"></span> of {{ $totalPositions }} positions completed
                                </span>
                            </div>
                            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-slate-800" role="progressbar" :aria-valuenow="progressPercent" aria-valuemin="0" aria-valuemax="100" :aria-label="`${completedCount} of {{ $totalPositions }} positions completed`">
                                <div class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-emerald-400 transition-all duration-300"
                                     :style="`width: ${progressPercent}%`"></div>
                            </div>
                        </div>
                    </div>

                    {{-- TWO-COLUMN LAYOUT --}}
                    <div class="mt-6 grid gap-6 pb-28 lg:grid-cols-3 lg:pb-6">
                        {{-- LEFT: positions + candidates --}}
                        <div class="space-y-6 lg:col-span-2">
                            {{-- Instructions banner --}}
                            <div class="flex items-start gap-3 rounded-2xl border border-cyan-500/15 bg-cyan-500/5 px-5 py-4">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-cyan-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
                                <div>
                                    <p class="text-sm font-semibold text-cyan-100">You may vote for ONE (1) candidate per position.</p>
                                    <p class="mt-0.5 text-sm text-slate-400">Make sure to review your choices before submitting your ballot.</p>
                                </div>
                            </div>

                            @forelse ($ballotCategories as $category)
                                <section class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-6" id="position-{{ $category['id'] }}">
                                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-violet-500/15 text-violet-300">
                                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5z" /></svg>
                                            </span>
                                            <div class="min-w-0">
                                                <h2 class="text-lg font-semibold text-white">{{ $category['name'] }}</h2>
                                                <p class="text-xs text-slate-400">
                                                    @if ($category['votable']) Select ONE (1) candidate @else No candidates available @endif
                                                </p>
                                            </div>
                                        </div>
                                        @if ($category['votable'])
                                            <span
                                                class="rounded-full px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide"
                                                :class="isCompleted({{ $category['id'] }}) ? 'bg-emerald-500/15 text-emerald-300' : 'bg-slate-800 text-slate-400'"
                                                x-text="isCompleted({{ $category['id'] }}) ? 'Completed' : 'Pending'"
                                            ></span>
                                        @endif
                                    </div>

                                    @if (! $category['votable'])
                                        <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-5 text-sm text-slate-400">
                                            No approved candidates are available for this position.
                                        </div>
                                    @else
                                        <div class="grid gap-4 md:grid-cols-2">
                                            @foreach ($category['candidates'] as $candidate)
                                                @php $cid = $candidate['id']; $catId = $category['id']; @endphp
                                                <article
                                                    class="relative flex flex-col rounded-2xl border bg-slate-950/40 p-4 transition"
                                                    role="group"
                                                    aria-label="{{ $candidate['name'] }} candidate card"
                                                    :class="isSelected({{ $catId }}, {{ $cid }})
                                                        ? 'border-emerald-500/70 bg-emerald-500/10 ring-1 ring-emerald-500/40'
                                                        : (isLocked({{ $catId }})
                                                            ? 'border-slate-800 opacity-60'
                                                            : 'border-slate-800 hover:border-cyan-500/40')"
                                                    :aria-pressed="isSelected({{ $catId }}, {{ $cid }})"
                                                >
                                                    {{-- Selected check (top-left) --}}
                                                    <div class="absolute -left-2 -top-2 flex h-7 w-7 items-center justify-center rounded-full bg-emerald-500 text-slate-950 shadow-lg"
                                                         x-show="isSelected({{ $catId }}, {{ $cid }})" x-cloak aria-hidden="true">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                                    </div>

                                                    <div class="flex items-start gap-4">
                                                        <x-candidate-avatar :path="$candidate['photo_path']" :name="$candidate['name']" size="lg" class="!h-20 !w-20 !rounded-xl" />
                                                        <div class="min-w-0 flex-1">
                                                            <div class="flex items-start justify-between gap-2">
                                                                <div class="min-w-0">
                                                                    <h3 class="truncate font-semibold text-white">{{ $candidate['name'] }}</h3>
                                                                    <p class="truncate text-sm text-slate-400">{{ $candidate['party'] }}</p>
                                                                </div>
                                                                <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide {{ $candidate['badge'] }}">
                                                                    {{ \Illuminate\Support\Str::limit($candidate['party'], 14, '') }}
                                                                </span>
                                                            </div>
                                                            @if ($candidate['grade'] || $candidate['section'])
                                                                <p class="mt-1 text-[11px] text-slate-500">Grade {{ $candidate['grade'] ?? '—' }} · {{ $candidate['section'] ?? '—' }}</p>
                                                            @endif
                                                            @if ($candidate['platform'])
                                                                <p class="mt-2 text-sm text-slate-300 line-clamp-2">{{ $candidate['platform'] }}</p>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="mt-4">
                                                        @if ($category['locked'])
                                                            <button type="button" disabled class="w-full cursor-not-allowed rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-slate-400">
                                                                Vote submitted
                                                            </button>
                                                        @else
                                                            <div class="grid grid-cols-2 gap-2" x-show="!isSelected({{ $catId }}, {{ $cid }})">
                                                                <a href="{{ $candidate['profile_url'] }}" class="rounded-xl border border-cyan-500/25 px-3 py-2 text-center text-xs font-semibold text-cyan-300 transition hover:bg-cyan-500/10">
                                                                    View Profile
                                                                </a>
                                                                <button type="button"
                                                                    @click="select({{ $catId }}, {{ $cid }}, @js($candidate['name']))"
                                                                    class="rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:from-cyan-400 hover:to-sky-300"
                                                                    :aria-pressed="isSelected({{ $catId }}, {{ $cid }})">
                                                                    Vote
                                                                </button>
                                                            </div>
                                                            <button type="button"
                                                                x-show="isSelected({{ $catId }}, {{ $cid }})" x-cloak
                                                                @click="select({{ $catId }}, {{ $cid }}, @js($candidate['name']))"
                                                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-950">
                                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                                                Selected
                                                            </button>
                                                        @endif
                                                    </div>
                                                </article>
                                            @endforeach
                                        </div>
                                    @endif
                                </section>
                            @empty
                                <div class="rounded-2xl border border-slate-800 bg-slate-900/50 p-6 text-slate-300">
                                    No positions found for this election.
                                </div>
                            @endforelse
                        </div>

                        {{-- RIGHT: sticky ballot summary + reminders + help --}}
                        <aside class="lg:col-span-1">
                            <div class="space-y-4 lg:sticky lg:top-6">
                                {{-- Ballot Summary --}}
                                <div class="rounded-2xl border border-violet-500/20 bg-slate-900/70 p-5">
                                    <div class="flex items-center gap-2">
                                        <svg class="h-5 w-5 text-violet-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        <h3 class="text-sm font-bold uppercase tracking-wide text-white">Your Ballot Summary</h3>
                                    </div>
                                    <div class="mt-4 space-y-3">
                                        @foreach ($votableCategories as $category)
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="text-sm font-semibold text-white">{{ $category['name'] }}</p>
                                                    <p class="truncate text-xs"
                                                       x-text="selectionName({{ $category['id'] }}) || 'Not selected'"
                                                       :class="selectionName({{ $category['id'] }}) ? 'text-cyan-300' : 'text-slate-500'"></p>
                                                </div>
                                                <span class="shrink-0">
                                                    <span x-show="isCompleted({{ $category['id'] }})" x-cloak class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500 text-slate-950">
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                                    </span>
                                                    <span x-show="!isCompleted({{ $category['id'] }})" x-cloak class="block h-5 w-5 rounded-full border border-slate-600"></span>
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Election Reminders --}}
                                <div class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-5">
                                    <div class="flex items-center gap-2">
                                        <svg class="h-5 w-5 text-cyan-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>
                                        <h3 class="text-sm font-bold uppercase tracking-wide text-white">Election Reminders</h3>
                                    </div>
                                    <ul class="mt-4 space-y-3 text-sm text-slate-300">
                                        <li class="flex items-start gap-2">
                                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                                            You can only submit your vote once.
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            Review your choices before submitting.
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            Keep your login details confidential.
                                        </li>
                                    </ul>
                                </div>

                                {{-- Need Help --}}
                                <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5">
                                    <div class="flex items-center gap-2">
                                        <svg class="h-5 w-5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" /></svg>
                                        <h3 class="text-sm font-bold uppercase tracking-wide text-white">Need Help?</h3>
                                    </div>
                                    <p class="mt-3 text-sm text-slate-400">If you encounter any issues while voting, please contact the {{ \App\Support\PortalSupportSettings::teamLabel() }}.</p>
                                    <a href="mailto:{{ \App\Support\PortalSupportSettings::email() }}" class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-cyan-500/25 px-4 py-2 text-sm font-semibold text-cyan-300 transition hover:bg-cyan-500/10">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
                                        Contact Support
                                    </a>
                                </div>

                                {{-- Review & Submit --}}
                                @if ($totalPositions > 0)
                                    <div class="hidden rounded-2xl border border-violet-500/25 bg-slate-900/70 p-5 lg:block">
                                        <button type="button" @click="openConfirm()" :disabled="!canSubmit()"
                                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-3 text-sm font-semibold text-white transition enabled:hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-40">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            Review &amp; Submit Ballot
                                        </button>
                                        <p class="mt-2 text-center text-xs text-slate-500" x-show="!canSubmit()" x-cloak>
                                            Make sure you've selected a candidate for all positions.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </aside>
                    </div>

                    @if ($totalPositions > 0)
                        {{-- MOBILE: fixed submit bar --}}
                        <div class="fixed inset-x-0 bottom-0 z-40 border-t border-cyan-500/15 bg-slate-950/95 backdrop-blur lg:hidden" x-show="!confirmOpen && !mobileSummaryOpen" x-cloak>
                            <div class="flex items-center gap-3 px-4 py-3">
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs text-slate-400">Ballot progress</p>
                                    <p class="text-sm font-semibold text-white" aria-live="polite">
                                        <span x-text="completedCount"></span> / {{ $totalPositions }} positions
                                    </p>
                                </div>
                                <button type="button" @click="mobileSummaryOpen = true"
                                    class="rounded-xl border border-cyan-500/25 px-3 py-2 text-xs font-semibold text-cyan-300">
                                    Summary
                                </button>
                                <button type="button" @click="openConfirm()" :disabled="!canSubmit()"
                                    class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-40">
                                    Submit
                                </button>
                            </div>
                        </div>

                        {{-- MOBILE: ballot summary sheet --}}
                        <div x-show="mobileSummaryOpen" x-cloak class="fixed inset-0 z-50 lg:hidden" @keydown.escape.window="mobileSummaryOpen = false">
                            <div class="absolute inset-0 bg-slate-950/80" @click="mobileSummaryOpen = false"></div>
                            <div class="absolute inset-x-0 bottom-0 max-h-[80vh] overflow-y-auto rounded-t-2xl border border-violet-500/20 bg-slate-900 p-5 shadow-2xl">
                                <div class="mb-4 flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-white">Your Ballot Summary</h3>
                                    <button type="button" @click="mobileSummaryOpen = false" class="text-slate-400 hover:text-white" aria-label="Close summary">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                                <div class="space-y-3">
                                    @foreach ($votableCategories as $category)
                                        <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                                            <div class="min-w-0">
                                                <p class="text-sm font-semibold text-white">{{ $category['name'] }}</p>
                                                <p class="truncate text-xs"
                                                   x-text="selectionName({{ $category['id'] }}) || 'Not selected'"
                                                   :class="selectionName({{ $category['id'] }}) ? 'text-cyan-300' : 'text-slate-500'"></p>
                                            </div>
                                            <span x-show="isCompleted({{ $category['id'] }})" x-cloak class="text-emerald-400" aria-hidden="true">✓</span>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" @click="mobileSummaryOpen = false; openConfirm()" :disabled="!canSubmit()"
                                    class="mt-5 w-full rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-3 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-40">
                                    Review &amp; Submit Ballot
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- HIDDEN SUBMISSION FORM --}}
                    <form method="POST" action="{{ route('student.voting.submit', $election) }}" x-ref="ballotForm" class="hidden">
                        @csrf
                        <template x-for="entry in newSelectionEntries()" :key="entry[0]">
                            <input type="hidden" :name="`selections[${entry[0]}]`" :value="entry[1]">
                        </template>
                    </form>

                    {{-- CONFIRMATION MODAL --}}
                    <div x-show="confirmOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="confirmOpen = false"
                         role="dialog" aria-modal="true" aria-labelledby="ballot-confirm-title">
                        <div class="absolute inset-0 bg-slate-950/80" @click="confirmOpen = false"></div>
                        <div class="relative w-full max-w-md rounded-2xl border border-violet-500/20 bg-slate-900 p-6 shadow-2xl"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-violet-500/15 text-violet-300" aria-hidden="true">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <h3 id="ballot-confirm-title" class="mt-4 text-center text-lg font-bold text-white">Confirm Ballot Submission</h3>
                            <p class="mt-2 text-center text-sm text-slate-300">
                                You are about to submit your official vote. You cannot edit your vote after submission.
                            </p>
                            <div class="mt-6 flex gap-3">
                                <button type="button" @click="confirmOpen = false"
                                    class="flex-1 rounded-xl border border-slate-700 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:bg-slate-800">
                                    Cancel
                                </button>
                                <button type="button" @click="submitBallot()"
                                    class="flex-1 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:opacity-90">
                                    Submit Vote
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if (! $showReceipt)
        @push('scripts')
            <style>[x-cloak]{display:none !important;}</style>
        @endpush
    @endif
</x-app-layout>

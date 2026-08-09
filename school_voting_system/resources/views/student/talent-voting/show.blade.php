<x-app-layout>
    @php
        $studentEntry = $studentEntry ?? null;
        $heroActions = $heroActions ?? ['primary' => null, 'secondary' => null, 'phase' => 'unknown'];
        $heroPrimary = $heroActions['primary'] ?? null;
        $heroSecondary = $heroActions['secondary'] ?? null;
        $entryStatus = $studentEntry?->status;
        $statusKey = $talentEvent->currentStatusKey();
        $statusLabel = $talentEvent->displayStatusLabel();
        $competitionCategory = $talentEvent->talent_category?->label() ?? $talentEvent->type?->label() ?? 'Talent';
        $approvedCount = $talentEvent->approvedEntries->count();
        $winnersCount = (int) ($talentEvent->number_of_winners ?? 3);
        $totalVotesCast = (int) $talentEvent->approvedEntries->sum('votes_count');
        $registrationOpen = $talentEvent->isRegistrationOpen();
        $votingOpen = $talentEvent->isAcceptingVotes();
        $resultsPublished = $talentEvent->hasPublishedResults();
        $votingClosed = $talentEvent->votingHasClosed() && ! $resultsPublished;
        $registrationStatusLabel = $registrationOpen ? 'Registration Open' : 'Registration Closed';
        $officialResultsUrl = route('student.results.talent.show', $talentEvent);

        $studentVote = $hasVoted
            ? \App\Models\TalentEventVote::query()
                ->where('talent_event_id', $talentEvent->id)
                ->where('user_id', $user->id)
                ->first()
            : null;
        $votedEntry = ($hasVoted && $votedEntryId)
            ? $talentEvent->approvedEntries->firstWhere('id', $votedEntryId)
            : null;

        // Competition Progress timeline (server-time driven)
        $registrationDone = ! $registrationOpen && (
            $talentEvent->registration_ends_at !== null
            || in_array($statusKey, ['registration_closed', 'voting_open', 'voting_closed', 'voting_paused', 'results_published', 'archived'], true)
            || $votingOpen
            || $resultsPublished
            || $talentEvent->isAfterVotingEnd()
        );
        $entriesApprovedDone = $registrationDone && (
            $approvedCount > 0
            || $votingOpen
            || $talentEvent->isAfterVotingEnd()
            || $resultsPublished
            || in_array($statusKey, ['voting_open', 'voting_closed', 'voting_paused', 'results_published', 'archived'], true)
        );
        $votingDone = $resultsPublished || $talentEvent->isAfterVotingEnd() || in_array($statusKey, ['voting_closed', 'results_published', 'archived'], true);
        $votingActive = $votingOpen;
        $resultsDone = $resultsPublished;
        $resultsPending = $votingDone && ! $resultsDone;

        $timeline = [
            [
                'label' => $registrationOpen ? 'Registration Open' : 'Registration Closed',
                'state' => $registrationOpen ? 'active' : ($registrationDone ? 'done' : 'pending'),
            ],
            [
                'label' => 'Entries Approved',
                'state' => ($registrationDone && ! $votingActive && ! $votingDone && $entriesApprovedDone)
                    ? 'active'
                    : ($entriesApprovedDone ? 'done' : 'pending'),
            ],
            [
                'label' => $votingActive ? 'Voting Live' : ($votingDone ? 'Voting Closed' : 'Voting'),
                'state' => $votingActive ? 'active' : ($votingDone ? 'done' : 'pending'),
            ],
            [
                'label' => $resultsDone ? 'Results Published' : 'Results Pending',
                'state' => $resultsDone ? 'done' : ($resultsPending ? 'active' : 'pending'),
            ],
        ];

        $votingEndsIso = $talentEvent->voting_ends_at?->toIso8601String();
    @endphp

    <div class="min-h-screen bg-slate-950 text-slate-100" x-data="talentVoteConfirm()">
        <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 sm:py-6 lg:px-8">
            <div class="mb-3 flex items-center justify-between gap-4">
                <a href="{{ route('student.talent-voting.index') }}" class="text-sm font-semibold text-cyan-300 hover:text-cyan-200">&larr; Back to Talent Competitions</a>
                <a href="{{ route('student.dashboard') }}" class="text-sm text-slate-300 hover:text-white">Dashboard</a>
            </div>

            @if (session('success'))
                <div class="mb-3 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-2.5 text-sm text-emerald-200">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-3 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-2.5 text-sm text-rose-200">{{ session('error') }}</div>
            @endif

            <div class="space-y-3">
                {{-- Hero --}}
                <section class="w-full overflow-hidden rounded-xl border border-cyan-500/15 bg-slate-900/80" aria-label="Competition hero">
                    <div class="relative w-full overflow-hidden rounded-xl aspect-[21/9] h-[150px] max-h-[160px] sm:h-[180px] sm:max-h-[180px] lg:aspect-video lg:h-[220px] lg:max-h-[220px]">
                        <x-competition-detail-banner
                            :event="$talentEvent"
                            bare
                            :show-warning="false"
                            class="absolute inset-0"
                        />
                        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/60 to-slate-950/25"></div>
                        <div class="absolute inset-0 z-[1] flex items-end p-4 sm:p-5">
                            <div class="flex w-full flex-wrap items-end justify-between gap-3">
                                <div class="min-w-0 max-w-3xl">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full border border-cyan-400/30 bg-cyan-500/15 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-cyan-100">{{ $competitionCategory }}</span>
                                        <span class="rounded-full border border-slate-500/40 bg-slate-950/55 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-100">{{ $statusLabel }}</span>
                                        <span @class([
                                            'rounded-full px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide',
                                            'bg-emerald-500/15 text-emerald-200' => $registrationOpen,
                                            'bg-slate-800/80 text-slate-300' => ! $registrationOpen,
                                        ])>{{ $registrationStatusLabel }}</span>
                                    </div>
                                    <h1 class="mt-2 text-xl font-bold leading-tight text-white sm:text-2xl lg:text-[1.75rem]">{{ $talentEvent->title }}</h1>
                                    <p class="mt-1 text-xs text-slate-300 sm:text-sm">
                                        {{ $talentEvent->event_date?->format('M d, Y · g:i A') ?: 'Schedule TBA' }}
                                        <span class="text-slate-500"> · </span>Online Competition
                                    </p>
                                </div>
                                @if ($heroPrimary || $heroSecondary)
                                    <div class="flex shrink-0 flex-col items-end gap-2">
                                        @if ($heroPrimary)
                                            @if (($heroPrimary['disabled'] ?? false) || ($heroPrimary['style'] ?? '') === 'disabled')
                                                <span class="inline-flex items-center rounded-xl border border-slate-600 bg-slate-800/80 px-4 py-2.5 text-sm font-semibold text-slate-300">
                                                    {{ $heroPrimary['label'] }}
                                                </span>
                                            @else
                                                <a href="{{ $heroPrimary['href'] }}"
                                                    class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-4 py-2.5 text-sm font-semibold text-slate-950 shadow-lg shadow-cyan-500/20 transition hover:from-cyan-400 hover:to-sky-300">
                                                    {{ $heroPrimary['label'] }}
                                                </a>
                                            @endif
                                        @endif

                                        @if ($heroSecondary)
                                            @if (($heroSecondary['style'] ?? '') === 'link')
                                                <a href="{{ $heroSecondary['href'] }}" class="text-xs font-semibold text-cyan-300 underline decoration-cyan-500/40 hover:text-cyan-200">
                                                    {{ $heroSecondary['label'] }}
                                                </a>
                                            @elseif (($heroSecondary['style'] ?? '') === 'secondary')
                                                <a href="{{ $heroSecondary['href'] }}"
                                                    class="inline-flex items-center justify-center rounded-xl border border-slate-600 bg-slate-950/50 px-4 py-2 text-xs font-semibold text-slate-200 transition hover:bg-slate-800">
                                                    {{ $heroSecondary['label'] }}
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Quick Statistics --}}
                <section class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-6" aria-label="Competition quick statistics">
                    <div class="rounded-xl border border-cyan-500/15 bg-slate-900/70 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Approved Participants</p>
                        <p class="mt-1 text-sm font-bold text-white sm:text-base">{{ number_format($approvedCount) }}</p>
                    </div>
                    <div class="rounded-xl border border-cyan-500/15 bg-slate-900/70 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Total Votes Cast</p>
                        <p class="mt-1 text-sm font-bold text-white sm:text-base">{{ number_format($totalVotesCast) }}</p>
                    </div>
                    <div class="rounded-xl border border-cyan-500/15 bg-slate-900/70 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Category</p>
                        <p class="mt-1 truncate text-sm font-bold text-white sm:text-base">{{ $competitionCategory }}</p>
                    </div>
                    <div class="rounded-xl border border-cyan-500/15 bg-slate-900/70 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Winners</p>
                        <p class="mt-1 text-sm font-bold text-white sm:text-base">{{ number_format($winnersCount) }}</p>
                    </div>
                    <div class="rounded-xl border border-cyan-500/15 bg-slate-900/70 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Status</p>
                        <p class="mt-1 truncate text-sm font-bold text-white sm:text-base">{{ $statusLabel }}</p>
                    </div>
                    <div class="rounded-xl border border-cyan-500/15 bg-slate-900/70 px-3 py-2.5"
                        @if ($votingOpen && $votingEndsIso)
                            x-data="{
                                endsAt: new Date(@js($votingEndsIso)).getTime(),
                                label: '—',
                                tick() {
                                    const diff = this.endsAt - Date.now();
                                    if (diff <= 0) { this.label = 'Ended'; return; }
                                    const h = Math.floor(diff / 3600000);
                                    const m = Math.floor((diff % 3600000) / 60000);
                                    const s = Math.floor((diff % 60000) / 1000);
                                    this.label = h > 0 ? `${h}h ${m}m` : `${m}m ${s}s`;
                                }
                            }"
                            x-init="tick(); setInterval(() => tick(), 1000)"
                        @endif
                    >
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Voting Ends In</p>
                        @if ($votingOpen && $votingEndsIso)
                            <p class="mt-1 text-sm font-bold text-emerald-300 sm:text-base" x-text="label">—</p>
                        @elseif ($talentEvent->voting_ends_at)
                            <p class="mt-1 text-sm font-bold text-white sm:text-base">{{ $talentEvent->isAfterVotingEnd() ? 'Ended' : $talentEvent->voting_ends_at->format('M d, g:i A') }}</p>
                        @else
                            <p class="mt-1 text-sm font-bold text-slate-400 sm:text-base">—</p>
                        @endif
                    </div>
                </section>

                {{-- Your Vote --}}
                <section class="rounded-xl border border-violet-500/20 bg-slate-900/70 px-4 py-3 sm:px-5" aria-label="Your vote status">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wide text-violet-200">Your Vote</h2>
                            @if ($hasVoted && $votedEntry)
                                <p class="mt-1 text-sm text-white">You voted for: <span class="font-semibold text-cyan-300">{{ $votedEntry->display_name }}</span></p>
                                <p class="mt-0.5 text-xs text-emerald-300">Vote recorded successfully</p>
                                @if ($studentVote?->created_at)
                                    <p class="mt-0.5 text-xs text-slate-500">Voting date: {{ $studentVote->created_at->format('M d, Y · g:i A') }}</p>
                                @endif
                            @else
                                <p class="mt-1 text-sm text-slate-400">You haven't voted yet.</p>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @if ($votingOpen && ! $hasVoted)
                                <a href="#candidates" class="rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-4 py-2 text-xs font-semibold text-slate-950">Vote Now</a>
                            @endif
                            @if ($resultsPublished)
                                <a href="{{ $officialResultsUrl }}" class="rounded-xl border border-sky-500/30 px-4 py-2 text-xs font-semibold text-sky-200 hover:bg-sky-500/10">View Official Results</a>
                            @endif
                        </div>
                    </div>
                </section>

                {{-- Competition Progress --}}
                <section class="rounded-xl border border-cyan-500/15 bg-slate-900/70 p-4 sm:p-5" aria-label="Competition progress">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-white">Competition Progress</h2>
                    <ol class="mt-4 space-y-0">
                        @foreach ($timeline as $step)
                            <li class="relative flex gap-3 pb-4 last:pb-0">
                                @if (! $loop->last)
                                    <span class="absolute left-[0.6875rem] top-6 h-[calc(100%-0.5rem)] w-px bg-slate-700" aria-hidden="true"></span>
                                @endif
                                <span @class([
                                    'relative z-[1] mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full border text-[11px] font-bold',
                                    'border-emerald-400/50 bg-emerald-500/20 text-emerald-300' => $step['state'] === 'done',
                                    'border-cyan-400/60 bg-cyan-500/20 text-cyan-200 ring-2 ring-cyan-400/20' => $step['state'] === 'active',
                                    'border-slate-600 bg-slate-900 text-slate-500' => $step['state'] === 'pending',
                                ])>
                                    @if ($step['state'] === 'done') ✓ @elseif ($step['state'] === 'active') ● @else ○ @endif
                                </span>
                                <div class="min-w-0 pt-0.5">
                                    <p @class([
                                        'text-sm font-semibold',
                                        'text-emerald-200' => $step['state'] === 'done',
                                        'text-cyan-100' => $step['state'] === 'active',
                                        'text-slate-500' => $step['state'] === 'pending',
                                    ])>{{ $step['label'] }}</p>
                                    @if ($step['state'] === 'active' && $votingActive)
                                        <p class="mt-0.5 text-xs text-emerald-300/90">Live now — cast your vote below</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </section>

                {{-- About Competition --}}
                <section class="rounded-xl border border-cyan-500/15 bg-slate-900/70 p-4 sm:p-5" aria-label="About this competition">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-white">About Competition</h2>
                    @if ($talentEvent->description)
                        <p class="mt-2 text-sm leading-relaxed text-slate-300">{{ $talentEvent->description }}</p>
                    @else
                        <p class="mt-2 text-sm text-slate-500">No description provided for this competition.</p>
                    @endif
                    <dl class="mt-3 grid gap-3 border-t border-slate-800 pt-3 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Performance Duration</dt>
                            <dd class="mt-0.5 text-sm font-medium text-white">{{ $talentEvent->performanceDurationLabel() }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Voting Method</dt>
                            <dd class="mt-0.5 text-sm font-medium text-white">{{ $talentEvent->votingMethodLabel() }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Registration Period</dt>
                            <dd class="mt-0.5 text-sm font-medium text-white">{{ $talentEvent->registrationWindowLabel() }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Submission Deadline</dt>
                            <dd class="mt-0.5 text-sm font-medium text-white">{{ $talentEvent->submission_deadline?->format('M d, Y g:i A') ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Voting Period</dt>
                            <dd class="mt-0.5 text-sm font-medium text-white">{{ $talentEvent->votingWindowLabel() }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Current Status</dt>
                            <dd class="mt-0.5 text-sm font-medium text-white">{{ $statusLabel }}</dd>
                        </div>
                    </dl>
                </section>

                {{-- Competition Rules --}}
                <section class="rounded-xl border border-cyan-500/15 bg-slate-900/70 p-4 sm:p-5" aria-label="Competition rules">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-white">Competition Rules</h2>
                    <ul class="mt-3 space-y-2 text-sm text-slate-300">
                        <li class="flex gap-2"><span class="text-emerald-400" aria-hidden="true">✓</span><span>One entry per student</span></li>
                        <li class="flex gap-2"><span class="text-emerald-400" aria-hidden="true">✓</span><span>One vote per student</span></li>
                        <li class="flex gap-2"><span class="text-emerald-400" aria-hidden="true">✓</span><span>Votes cannot be changed</span></li>
                        <li class="flex gap-2"><span class="text-emerald-400" aria-hidden="true">✓</span><span>Offensive performances are prohibited</span></li>
                        <li class="flex gap-2"><span class="text-emerald-400" aria-hidden="true">✓</span><span>Official results are available on the Results page</span></li>
                    </ul>
                </section>

                {{-- Participants --}}
                @if ($votingOpen && ! $hasVoted)
                    <div class="flex items-start gap-3 rounded-xl border border-cyan-500/15 bg-cyan-500/5 px-4 py-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-cyan-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
                        <div>
                            <p class="text-sm font-semibold text-cyan-100">You may vote for ONE (1) talent entry.</p>
                            <p class="mt-0.5 text-sm text-slate-400">Review each performance before casting your vote. You cannot change your vote after submission.</p>
                        </div>
                    </div>
                @endif

                <section id="candidates" aria-label="Talent competition participants">
                    <h2 class="mb-3 text-lg font-bold text-white">Participants</h2>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @forelse ($talentEvent->approvedEntries as $entry)
                            @php
                                $isSelected = $votedEntryId === $entry->id;
                                $entryCategory = $entry->talentCategoryLabel() ?? $competitionCategory;
                                $photo = $entry->photoUrl() ?: $entry->thumbnailUrl();
                                $watchPayload = [
                                    'name' => $entry->display_name,
                                    'title' => $entry->performance_title ?? $entry->display_name,
                                    'category' => $entryCategory,
                                    'grade' => $entry->grade_level ? ('Grade '.$entry->grade_level.($entry->section ? ' · '.$entry->section : '')) : '',
                                    'embed' => $entry->videoEmbedUrl(),
                                    'file' => $entry->videoFileUrl(),
                                    'viewUrl' => route('student.talent-voting.view', $entry),
                                    'csrf' => csrf_token(),
                                ];
                                $participantPayload = [
                                    'name' => $entry->display_name,
                                    'title' => $entry->performance_title,
                                    'category' => $entryCategory,
                                    'grade' => $entry->grade_level,
                                    'section' => $entry->section,
                                    'course' => $entry->course_strand,
                                    'summary' => $entry->profile_summary,
                                    'description' => $entry->performance_description,
                                    'social' => $entry->social_media,
                                    'photo' => $entry->photoUrl(),
                                ];
                            @endphp
                            <article
                                class="relative flex overflow-hidden rounded-xl border bg-slate-900/70 transition {{ $isSelected ? 'border-emerald-500/70 bg-emerald-500/10 ring-1 ring-emerald-500/40' : 'border-cyan-500/15 hover:border-cyan-500/40' }}"
                                role="group"
                                aria-label="{{ $entry->display_name }}"
                            >
                                <div class="relative w-24 shrink-0 self-stretch sm:w-28">
                                    @if ($photo)
                                        <img src="{{ $photo }}" loading="lazy" alt="{{ $entry->display_name }}" class="absolute inset-0 h-full w-full object-cover object-center">
                                    @else
                                        <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-cyan-900/40 to-sky-900/20">
                                            <span class="text-2xl font-bold text-cyan-300/60">{{ strtoupper(substr($entry->display_name, 0, 1)) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex min-w-0 flex-1 flex-col p-3">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <h3 class="truncate text-sm font-semibold text-white">{{ $entry->display_name }}</h3>
                                            @if ($entry->grade_level)
                                                <p class="mt-0.5 text-[11px] text-slate-400">Grade {{ $entry->grade_level }}@if($entry->section) · {{ $entry->section }}@endif</p>
                                            @endif
                                        </div>
                                        @if ($entryCategory)
                                            <span class="shrink-0 rounded-full border border-cyan-400/30 bg-cyan-500/10 px-2 py-0.5 text-[10px] font-semibold text-cyan-200">{{ $entryCategory }}</span>
                                        @endif
                                    </div>
                                    @if ($entry->performance_title)
                                        <p class="mt-1.5 truncate text-xs font-semibold text-cyan-300">{{ $entry->performance_title }}</p>
                                    @endif
                                    @if ($entry->profile_summary || $entry->performance_description)
                                        <p class="mt-1 line-clamp-2 text-xs text-slate-400">{{ $entry->profile_summary ?: $entry->performance_description }}</p>
                                    @endif

                                    <div class="mt-auto flex flex-wrap gap-1.5 pt-2.5">
                                        @if ($entry->hasVideo())
                                            <button type="button" @click='openWatch(@json($watchPayload))'
                                                class="inline-flex items-center gap-1 rounded-lg border border-cyan-500/30 px-2.5 py-1.5 text-[11px] font-semibold text-cyan-200 hover:bg-cyan-500/10">
                                                Watch Performance
                                            </button>
                                        @endif
                                        <button type="button" @click='openParticipant(@json($participantPayload))'
                                            class="inline-flex items-center gap-1 rounded-lg border border-slate-700 px-2.5 py-1.5 text-[11px] font-semibold text-slate-300 hover:bg-slate-800">
                                            View Profile
                                        </button>
                                        @if ($isSelected)
                                            <span class="inline-flex items-center gap-1 rounded-lg bg-emerald-500 px-2.5 py-1.5 text-[11px] font-semibold text-slate-950">Selected</span>
                                        @elseif ($votingOpen && ! $hasVoted)
                                            <form method="POST" action="{{ route('student.talent-voting.vote', $entry) }}" class="inline">
                                                @csrf
                                                <button type="button"
                                                    @click="openConfirm($event.target.closest('form'), @js($entry->display_name))"
                                                    class="rounded-lg bg-gradient-to-r from-cyan-500 to-sky-400 px-2.5 py-1.5 text-[11px] font-semibold text-slate-950">
                                                    Vote
                                                </button>
                                            </form>
                                        @elseif ($resultsPublished)
                                            <span class="inline-flex items-center rounded-lg border border-sky-500/30 bg-sky-500/10 px-2.5 py-1.5 text-[11px] font-semibold text-sky-200">Results Published</span>
                                        @elseif (! $votingOpen)
                                            <span class="inline-flex items-center rounded-lg border border-slate-600 bg-slate-800/80 px-2.5 py-1.5 text-[11px] font-semibold text-slate-400">Voting Closed</span>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @empty
                            <p class="text-sm text-slate-400 sm:col-span-2 lg:col-span-3">No approved participants have been published yet.</p>
                        @endforelse
                    </div>
                </section>

                {{-- Reminders + Help --}}
                <section class="rounded-xl border border-cyan-500/15 bg-slate-900/70 p-4 sm:p-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wide text-white">Reminders</h3>
                            <ul class="mt-2 space-y-1.5 text-sm text-slate-300">
                                <li>• One entry per student.</li>
                                <li>• One vote per student.</li>
                                <li>• Votes cannot be changed.</li>
                                <li>• Official results are available on the Results page.</li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wide text-white">Need Help?</h3>
                            <p class="mt-2 text-sm text-slate-400">Contact {{ \App\Support\PortalSupportSettings::teamLabel() }}</p>
                            <a href="mailto:{{ \App\Support\PortalSupportSettings::email() }}"
                                class="mt-2 inline-flex items-center justify-center rounded-xl border border-cyan-500/25 px-4 py-2 text-xs font-semibold text-cyan-300 transition hover:bg-cyan-500/10">
                                Contact ICT Support
                            </a>
                        </div>
                    </div>
                </section>

                {{-- Results Published card (links to dedicated Results page) --}}
                @if ($resultsPublished)
                    <section class="rounded-xl border border-amber-500/25 bg-gradient-to-br from-amber-500/10 via-slate-900/80 to-slate-950/80 p-4 sm:p-5" aria-label="Results published notice">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="text-lg font-bold text-white">🏆 Results Published</p>
                                <p class="mt-1 text-sm text-slate-300">The official competition results have been published.</p>
                                <p class="mt-1 text-sm text-slate-400">Click below to view the complete rankings, winner, and vote statistics.</p>
                            </div>
                            <a href="{{ $officialResultsUrl }}"
                                class="inline-flex shrink-0 items-center justify-center rounded-xl bg-gradient-to-r from-amber-500 to-orange-400 px-5 py-2.5 text-sm font-semibold text-slate-950 shadow-lg shadow-amber-500/20 transition hover:from-amber-400 hover:to-orange-300">
                                View Official Results
                            </a>
                        </div>
                    </section>
                @endif
            </div>
        </div>

        {{-- WATCH PERFORMANCE MODAL --}}
        <div x-show="watchOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="closeWatch()"
             role="dialog" aria-modal="true" aria-labelledby="talent-watch-title">
            <div class="absolute inset-0 bg-slate-950/90" @click="closeWatch()"></div>
            <div class="relative flex w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-cyan-500/25 bg-slate-900 shadow-2xl" x-ref="watchPanel">
                <div class="flex items-start justify-between gap-4 border-b border-slate-800 px-5 py-4">
                    <div class="min-w-0">
                        <h3 id="talent-watch-title" class="truncate text-lg font-bold text-white" x-text="watch.title"></h3>
                        <p class="mt-0.5 truncate text-sm text-cyan-300">
                            <span x-text="watch.name"></span>
                            <template x-if="watch.grade"><span class="text-slate-500"> · </span></template>
                            <span class="text-slate-400" x-text="watch.grade"></span>
                        </p>
                        <template x-if="watch.category">
                            <span class="mt-2 inline-block rounded-full border border-cyan-400/40 bg-cyan-500/10 px-3 py-0.5 text-[11px] font-semibold text-cyan-200" x-text="watch.category"></span>
                        </template>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="toggleFullscreen($refs.watchPanel)" class="rounded-lg border border-slate-700 p-2 text-slate-300 transition hover:bg-slate-800" aria-label="Toggle fullscreen">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 8.25V4.5h3.75M20.25 8.25V4.5h-3.75M3.75 15.75v3.75h3.75M20.25 15.75v3.75h-3.75" /></svg>
                        </button>
                        <button type="button" @click="closeWatch()" class="rounded-lg border border-slate-700 p-2 text-slate-300 transition hover:bg-slate-800" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>
                <div class="aspect-video w-full bg-black">
                    <template x-if="watchOpen && watch.embed">
                        <iframe class="h-full w-full" :src="watch.embed" title="Performance video" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen" allowfullscreen></iframe>
                    </template>
                    <template x-if="watchOpen && !watch.embed && watch.file">
                        <video class="h-full w-full" :src="watch.file" controls playsinline></video>
                    </template>
                    <template x-if="watchOpen && !watch.embed && !watch.file">
                        <div class="flex h-full items-center justify-center text-sm text-slate-500">No playable video available.</div>
                    </template>
                </div>
            </div>
        </div>

        {{-- PARTICIPANT DETAIL MODAL --}}
        <div x-show="viewOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="viewOpen = false"
             role="dialog" aria-modal="true" aria-labelledby="talent-participant-title">
            <div class="absolute inset-0 bg-slate-950/85" @click="viewOpen = false"></div>
            <div class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-cyan-500/20 bg-slate-900 shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-800 px-5 py-4">
                    <h3 id="talent-participant-title" class="text-lg font-bold text-white" x-text="participant.name"></h3>
                    <button type="button" @click="viewOpen = false" class="rounded-lg border border-slate-700 p-2 text-slate-300 transition hover:bg-slate-800" aria-label="Close">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="max-h-[70vh] overflow-y-auto p-5">
                    <div class="flex items-start gap-4">
                        <template x-if="participant.photo">
                            <img :src="participant.photo" alt="" class="h-24 w-24 shrink-0 rounded-xl object-cover">
                        </template>
                        <div class="min-w-0">
                            <template x-if="participant.title">
                                <p class="text-sm font-semibold text-cyan-300" x-text="participant.title"></p>
                            </template>
                            <template x-if="participant.category">
                                <span class="mt-1 inline-block rounded-full border border-cyan-400/40 bg-cyan-500/10 px-3 py-0.5 text-[11px] font-semibold text-cyan-200" x-text="participant.category"></span>
                            </template>
                            <p class="mt-2 text-xs text-slate-400">
                                <span x-show="participant.grade">Grade <span x-text="participant.grade"></span></span>
                                <span x-show="participant.section"> · Section <span x-text="participant.section"></span></span>
                                <span x-show="participant.course"> · <span x-text="participant.course"></span></span>
                            </p>
                        </div>
                    </div>
                    <template x-if="participant.summary">
                        <div class="mt-4">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">About</p>
                            <p class="mt-1 text-sm text-slate-300" x-text="participant.summary"></p>
                        </div>
                    </template>
                    <template x-if="participant.description">
                        <div class="mt-4">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Performance</p>
                            <p class="mt-1 text-sm text-slate-300" x-text="participant.description"></p>
                        </div>
                    </template>
                    <template x-if="participant.social">
                        <div class="mt-4">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Social Media</p>
                            <p class="mt-1 text-sm text-cyan-300" x-text="participant.social"></p>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- CONFIRMATION MODAL --}}
        <div x-show="confirmOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="confirmOpen = false"
             role="dialog" aria-modal="true" aria-labelledby="talent-vote-confirm-title">
            <div class="absolute inset-0 bg-slate-950/80" @click="confirmOpen = false"></div>
            <div class="relative w-full max-w-md rounded-2xl border border-violet-500/20 bg-slate-900 p-6 shadow-2xl">
                <h3 id="talent-vote-confirm-title" class="text-lg font-bold text-white">Confirm Your Vote</h3>
                <p class="mt-2 text-sm text-slate-300">
                    You are about to vote for <span class="font-semibold text-white" x-text="entryName"></span>. You cannot change your vote after submission.
                </p>
                <div class="mt-6 flex gap-3">
                    <button type="button" @click="confirmOpen = false"
                        class="flex-1 rounded-xl border border-slate-700 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:bg-slate-800">
                        Cancel
                    </button>
                    <button type="button" @click="submitVote()"
                        class="flex-1 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:opacity-90">
                        Submit Vote
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <style>[x-cloak]{display:none !important;}</style>
    @endpush
</x-app-layout>

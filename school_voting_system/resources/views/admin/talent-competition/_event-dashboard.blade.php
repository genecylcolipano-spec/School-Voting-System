@php
    $resolvedStatus = $event->currentStatus();
    $statusValue = $resolvedStatus['key'];
    $statusLabel = $resolvedStatus['label'];
    $statusTone = match ($statusLabel) {
        'Voting Open' => 'live',
        'Results Published', 'Archived' => 'closed',
        'Voting Closed', 'Voting Paused' => 'paused',
        default => 'idle',
    };

    $showVotes = $canViewRealtimeTalentCounts
        || $event->votingHasClosed()
        || $statusValue === 'results_published'
        || $statusValue === 'archived';

    $rankedEntries = $event->entries
        ->sortByDesc(fn ($entry) => (int) ($entry->votes_count ?? 0))
        ->values()
        ->map(function ($entry, $index) {
            $entry->setAttribute('computed_rank', $index + 1);
            return $entry;
        });

    $winner = $rankedEntries->first();
    $totalVotes = (int) ($event->votes_count ?? 0);
    $entriesWithVotes = $rankedEntries->where('votes_count', '>', 0)->count();
    $participationPercent = $event->entries_count > 0
        ? round(($entriesWithVotes / $event->entries_count) * 100, 1)
        : 0.0;

    $startYear = $event->event_date?->year ?? $event->voting_starts_at?->year ?? now()->year;
    $academicYear = $startYear.'–'.($startYear + 1);
    $organizer = $event->creator?->name ?? ($assignedRole ?? 'Event Administrator');
    $categoryLabel = $event->talent_category?->label() ?? ($event->type?->label() ?? 'Talent Competition');

    $countdownRemaining = '—';
    $countdownLabel = 'Voting closed';
    if ($statusValue === 'voting_open' && $event->voting_ends_at && now()->lt($event->voting_ends_at)) {
        $diff = now()->diff($event->voting_ends_at);
        $hours = ($diff->days * 24) + $diff->h;
        $minutes = $diff->i;
        $countdownRemaining = sprintf('%02d Hours %02d Minutes', $hours, $minutes);
        $countdownLabel = 'Voting ends in';
    } elseif ($event->isBeforeVotingStart() && $event->voting_starts_at) {
        $diff = now()->diff($event->voting_starts_at);
        $hours = ($diff->days * 24) + $diff->h;
        $minutes = $diff->i;
        $countdownRemaining = sprintf('%02d Hours %02d Minutes', $hours, $minutes);
        $countdownLabel = 'Voting starts in';
    }

    $grades = $event->entries->pluck('grade_level')->filter()->unique()->sort()->values();
    $isPublished = in_array($statusValue, ['results_published', 'archived'], true);
    $isVotingOpen = $statusValue === 'voting_open';

    $canManageTalentVoting = $user->hasPermission('manage_talent_voting');
    $canPublishTalentResults = $user->hasPermission('publish_talent_results');

    $activity = collect([
        ['label' => 'Event Created', 'at' => $event->created_at, 'icon' => '📅'],
        ['label' => 'Voting Window Opens', 'at' => $event->voting_starts_at, 'icon' => '🟢'],
        ['label' => 'Voting Window Closes', 'at' => $event->voting_ends_at, 'icon' => '🔵'],
        ['label' => 'Published to Students', 'at' => $event->published_at, 'icon' => '📢'],
        ['label' => 'Results Published', 'at' => $event->results_published_at, 'icon' => '🏆'],
    ])->filter(fn ($item) => $item['at'])->sortByDesc('at')->values();
@endphp

<section
    class="tc-dashboard tc-card overflow-hidden"
    data-tc-dashboard
    data-event-id="{{ $event->id }}"
    aria-label="{{ $event->title }} management dashboard"
>
    {{-- Per-event sub-header when multiple events --}}
    @if ($showEventTitle ?? false)
        <div class="border-b border-violet-500/10 bg-slate-950/40 px-4 py-3 sm:px-5">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-violet-400">Current Event</p>
            <h3 class="text-lg font-bold text-white">{{ $event->title }}</h3>
        </div>
    @endif

    <div class="space-y-5 p-4 sm:p-5 lg:p-6">
        {{-- Hero banner (landscape 16:9 only on cards; detail rules for accidental portraits) --}}
        <div class="tc-hero aspect-video bg-slate-950/90">
            <x-competition-detail-banner :event="$event" bare :show-warning="false" class="absolute inset-0 tc-hero__image" />
            <div class="tc-hero__overlay"></div>
            <div class="tc-hero__content">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-violet-500/25 px-2.5 py-0.5 text-[10px] font-semibold uppercase text-violet-200">{{ $categoryLabel }}</span>
                    <span @class([
                        'vm-badge',
                        'vm-badge--live' => $statusTone === 'live',
                        'vm-badge--closed' => $statusTone === 'closed',
                        'vm-badge--idle' => $statusTone === 'idle',
                    ])>{{ $statusLabel }}</span>
                </div>
                <h4 class="mt-2 text-2xl font-bold text-white sm:text-3xl">{{ $event->title }}</h4>
                <p class="mt-2 text-sm text-slate-300">
                    {{ $event->venue ?? 'Venue TBA' }}
                    @if ($event->event_date) · {{ $event->event_date->format('F j, Y') }} @endif
                </p>
            </div>
        </div>
        @if ($event->shouldWarnNonLandscapeBanner())
            <p class="rounded-xl border border-amber-500/25 bg-amber-500/10 px-3 py-2 text-center text-xs font-medium text-amber-100">
                This image is portrait. For best appearance, upload a landscape banner (1600 × 900).
            </p>
        @endif
        <x-competition-poster :event="$event" />

        {{-- Event information --}}
        <div class="tc-card p-4 sm:p-5">
            <h4 class="text-sm font-semibold uppercase tracking-wide text-violet-300">Event Information</h4>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Event Name</dt>
                    <dd class="mt-1 text-sm font-medium text-white">{{ $event->title }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Category</dt>
                    <dd class="mt-1 text-sm font-medium text-white">{{ $categoryLabel }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Venue</dt>
                    <dd class="mt-1 text-sm font-medium text-white">{{ $event->venue ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Date</dt>
                    <dd class="mt-1 text-sm font-medium text-white">{{ $event->event_date?->format('F j, Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Voting Schedule</dt>
                    <dd class="mt-1 text-sm font-medium text-white">
                        @if ($event->voting_starts_at && $event->voting_ends_at)
                            {{ $event->voting_starts_at->format('g:i A') }} – {{ $event->voting_ends_at->format('g:i A') }}
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Registration Window</dt>
                    <dd class="mt-1 text-sm font-medium text-white">{{ $event->registrationWindowLabel() }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Submission Deadline</dt>
                    <dd class="mt-1 text-sm font-medium text-white">{{ $event->submission_deadline?->format('M d, Y g:i A') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Organizer</dt>
                    <dd class="mt-1 text-sm font-medium text-white">{{ $organizer }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Academic Year</dt>
                    <dd class="mt-1 text-sm font-medium text-white">{{ $academicYear }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Current Status</dt>
                    <dd class="mt-1 text-sm font-medium text-white">{{ $statusLabel }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Total Contestants</dt>
                    <dd class="mt-1 text-sm font-medium text-white">{{ $event->entries_count }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Talent Category</dt>
                    <dd class="mt-1 text-sm font-medium text-white">{{ $event->talent_category?->label() ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Performance Duration</dt>
                    <dd class="mt-1 text-sm font-medium text-white">{{ $event->performanceDurationLabel() }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Number of Winners</dt>
                    <dd class="mt-1 text-sm font-medium text-white">{{ $event->number_of_winners ?? 3 }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Contestant Limit</dt>
                    <dd class="mt-1 text-sm font-medium text-white">{{ $event->contestantLimitLabel() }}</dd>
                </div>
                <div class="sm:col-span-2 xl:col-span-3">
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Voting Method</dt>
                    <dd class="mt-1 text-sm font-medium text-white">{{ $event->votingMethodLabel() }}</dd>
                </div>
            </dl>
        </div>

        @php
            $statusCounts = $event->entries->groupBy('status')->map->count();
            $pendingCount = (int) ($statusCounts['pending'] ?? 0);
            $approvedCount = (int) ($statusCounts['approved'] ?? 0);
            $rejectedCount = (int) ($statusCounts['rejected'] ?? 0);
        @endphp

        {{-- Quick statistics --}}
        <div class="tc-stat-grid">
            <article class="tc-stat-card">
                <div class="flex items-center gap-2">
                    <span class="tc-stat-card__icon" aria-hidden="true">👥</span>
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Contestants</p>
                </div>
                <p class="tc-stat-card__value">{{ number_format($event->entries_count) }}</p>
                <p class="mt-1 text-[10px] text-slate-500">Pending {{ $pendingCount }} · Approved {{ $approvedCount }} · Rejected {{ $rejectedCount }}</p>
            </article>
            <article class="tc-stat-card">
                <div class="flex items-center gap-2">
                    <span class="tc-stat-card__icon" aria-hidden="true">🗳</span>
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Total Votes</p>
                </div>
                <p class="tc-stat-card__value">{{ $showVotes ? number_format($totalVotes) : '—' }}</p>
                <p class="mt-1 text-[10px] text-slate-500">{{ $showVotes ? 'Scoped vote total' : 'Hidden until close' }}</p>
            </article>
            <article class="tc-stat-card">
                <div class="flex items-center gap-2">
                    <span class="tc-stat-card__icon" aria-hidden="true">🏆</span>
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Winner</p>
                </div>
                <p class="tc-stat-card__value text-lg sm:text-2xl">{{ ($isPublished && $winner && $winner->votes_count > 0) ? $winner->display_name : '—' }}</p>
                <p class="mt-1 text-[10px] text-slate-500">{{ $isPublished ? 'Official champion' : 'Pending results' }}</p>
            </article>
            <article class="tc-stat-card">
                <div class="flex items-center gap-2">
                    <span class="tc-stat-card__icon" aria-hidden="true">📈</span>
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Participation</p>
                </div>
                <p class="tc-stat-card__value">{{ $showVotes ? number_format($participationPercent, 1).'%' : '—' }}</p>
                <p class="mt-1 text-[10px] text-slate-500">Contestants receiving votes</p>
            </article>
        </div>

        {{-- Quick actions --}}
        <div class="flex flex-wrap items-center gap-2 rounded-2xl border border-violet-500/10 bg-slate-950/40 p-3 sm:p-4" role="toolbar" aria-label="Talent competition actions">
            @if ($canManageTalentEvents)
                <a href="{{ route('admin.talent-competition.edit', $event) }}" class="tc-btn tc-btn--ghost">✏ Edit Event</a>
                <a href="{{ route('admin.talent-participants.index', ['event' => $event->id]) }}" class="tc-btn tc-btn--ghost">👥 Manage Contestants</a>
            @endif

            @if ($canManageTalentVoting && ! in_array($statusValue, ['voting_open', 'results_published', 'voting_closed', 'archived'], true))
                <form method="POST" action="{{ route('admin.talent.open-voting', $event) }}" data-confirm-sensitive data-confirm-title="Open student voting?" class="inline">
                    @csrf
                    <button type="submit" class="tc-btn tc-btn--success">▶ Open Voting</button>
                </form>
            @endif

            @if ($canPublishTalentResults && in_array($statusValue, ['voting_open', 'voting_closed', 'voting_paused'], true))
                <form method="POST" action="{{ route('admin.talent.publish-results', $event) }}" data-confirm-sensitive data-confirm-title="Publish official results?" class="inline">
                    @csrf
                    <button type="submit" class="tc-btn tc-btn--primary">🏆 Publish Results</button>
                </form>
            @endif

            <a href="{{ route('admin.results.talent.show', $event) }}" class="tc-btn tc-btn--primary">📊 View Results</a>

            @if ($canViewRealtimeTalentCounts || $isPublished)
                <a href="{{ route('admin.results.talent.export', ['talentEvent' => $event, 'format' => 'csv']) }}" class="tc-btn tc-btn--ghost">⬇ Export CSV</a>
                <a href="{{ route('admin.results.talent.export', ['talentEvent' => $event, 'format' => 'excel']) }}" class="tc-btn tc-btn--ghost">⬇ Export Excel</a>
                <a href="{{ route('admin.results.talent.export', ['talentEvent' => $event, 'format' => 'pdf']) }}" class="tc-btn tc-btn--ghost">⬇ Export PDF</a>
                <a href="{{ route('admin.results.talent.export', ['talentEvent' => $event, 'format' => 'print']) }}" target="_blank" rel="noopener" class="tc-btn tc-btn--ghost">🖨 Print Results</a>
            @endif

            @if ($canManageTalentEvents)
                <x-admin.delete-action
                    :action="route('admin.talent-competition.destroy', $event)"
                    button-class="tc-btn tc-btn--danger"
                    label="Delete Event"
                />
            @endif
        </div>

        {{-- Live voting status --}}
        @if ($isVotingOpen)
            <div class="tc-live-status tc-live-status--live">
                <span class="text-lg" aria-hidden="true">🟢</span>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-emerald-200">LIVE VOTING</p>
                    <p class="text-sm text-emerald-100/80">Voting updates automatically. Current participation: {{ $showVotes ? number_format($participationPercent, 1).'%' : 'counts hidden until close' }}.</p>
                    <div class="vm-progress mt-3 h-2">
                        <span style="width:{{ min(100, $participationPercent) }}%"></span>
                    </div>
                </div>
                @if ($countdownRemaining !== '—')
                    <div class="shrink-0 text-right text-xs text-emerald-200/80">
                        <p>{{ $countdownLabel }}</p>
                        <p class="mt-0.5 font-semibold tabular-nums text-emerald-100">{{ $countdownRemaining }}</p>
                    </div>
                @endif
            </div>
        @elseif ($isPublished)
            <div class="tc-live-status tc-live-status--published">
                <span class="text-lg" aria-hidden="true">🔵</span>
                <div>
                    <p class="font-semibold text-sky-200">RESULTS PUBLISHED</p>
                    <p class="text-sm text-sky-100/80">Official results are now available.</p>
                </div>
            </div>
        @elseif (in_array($statusValue, ['registration_open', 'registration_closed', 'scheduled', 'draft'], true))
            <div class="tc-live-status tc-live-status--idle">
                <span class="text-lg" aria-hidden="true">⏳</span>
                <div>
                    <p class="font-semibold text-slate-200">Voting Not Started</p>
                    <p class="text-sm text-slate-400">Open voting when contestants are ready and the schedule is confirmed.</p>
                </div>
            </div>
        @endif

        {{-- Live vote monitoring (authorized administrators) --}}
        @if ($canViewRealtimeTalentCounts && ($isVotingOpen || $isPublished))
            <div
                id="tc-live-monitor"
                class="tc-card p-4 sm:p-5 scroll-mt-24"
                data-tc-live-monitor
                data-live-url="{{ route('admin.results.talent.live', $event) }}"
                data-is-live="{{ $isVotingOpen ? '1' : '0' }}"
            >
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        @if ($isVotingOpen)
                            <p class="text-[10px] font-semibold uppercase tracking-widest text-emerald-400">Live Vote Monitoring</p>
                            <h4 class="mt-1 text-lg font-semibold text-white">🟢 Current Standings</h4>
                        @else
                            <p class="text-[10px] font-semibold uppercase tracking-widest text-sky-400">Official Results</p>
                            <h4 class="mt-1 text-lg font-semibold text-white">🏆 Final Rankings</h4>
                        @endif
                        @if ($isVotingOpen)
                            <p class="mt-2 text-sm text-slate-400">Live vote totals are visible only to authorized administrators for monitoring purposes.</p>
                        @else
                            <p class="mt-2 text-sm text-slate-400">Official results are now available for this competition.</p>
                        @endif
                    </div>
                    <p class="text-[11px] text-slate-500" data-tc-live-updated>
                        @if ($isVotingOpen)
                            Auto-refreshing every 5 seconds
                        @else
                            Last updated {{ now()->format('g:i A') }}
                        @endif
                    </p>
                </div>

                @if ($rankedEntries->isEmpty())
                    <div class="tc-empty mt-4 min-h-[8rem]">
                        <div class="mb-2 text-2xl" aria-hidden="true">🎤</div>
                        <p class="font-medium text-slate-300">No contestants to monitor</p>
                        <p class="mt-1 text-sm text-slate-500">Add approved contestants before voting begins.</p>
                    </div>
                @else
                    <div class="mt-4 space-y-3" data-tc-live-list>
                        @foreach ($rankedEntries as $entry)
                            @php
                                $votePercent = $totalVotes > 0
                                    ? round(((int) $entry->votes_count / $totalVotes) * 100, 1)
                                    : 0.0;
                            @endphp
                            <article
                                class="rounded-xl border border-slate-800 bg-slate-950/50 p-3 sm:p-4"
                                data-tc-live-row
                                data-entry-id="{{ $entry->id }}"
                            >
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-white">
                                            <span class="mr-2 text-violet-300" data-tc-live-rank>#{{ $entry->computed_rank }}</span>
                                            <span data-tc-live-name>{{ $entry->display_name }}</span>
                                        </p>
                                        <p class="mt-1 text-xs text-slate-400">
                                            <span data-tc-live-votes>{{ number_format($entry->votes_count) }}</span> votes
                                            · <span data-tc-live-percent>{{ number_format($votePercent, 1) }}%</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="vm-progress mt-3 h-2">
                                    <span data-tc-live-bar style="width:{{ min(100, $votePercent) }}%"></span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        {{-- Winner highlight --}}
        @if ($isPublished && $rankedEntries->isNotEmpty())
            <div>
                <h4 class="mb-3 text-sm font-semibold uppercase tracking-wide text-emerald-300">Winner Highlight</h4>
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach ($rankedEntries->take(3) as $index => $entry)
                        @php
                            $awardLabel = match ($index) {
                                0 => 'Champion',
                                1 => '1st Runner-up',
                                2 => '2nd Runner-up',
                                default => 'Finalist',
                            };
                            $votePercent = $totalVotes > 0 && $showVotes
                                ? round(((int) $entry->votes_count / $totalVotes) * 100, 1)
                                : 0;
                        @endphp
                        <article @class([
                            'tc-winner-card',
                            'tc-winner-card--runner' => $index > 0,
                        ])>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-violet-300">🏆 {{ $awardLabel }}</p>
                            <div class="mt-3 flex items-center gap-3">
                                @if ($entry->posterUrl())
                                    <img src="{{ $entry->posterUrl() }}" alt="" class="h-12 w-12 rounded-full object-cover ring-2 ring-violet-500/30">
                                @else
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-violet-500/15 text-sm font-bold text-violet-200">{{ strtoupper(substr($entry->display_name, 0, 1)) }}</div>
                                @endif
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-white">{{ $entry->display_name }}</p>
                                    <p class="text-xs text-slate-400">{{ $categoryLabel }}</p>
                                </div>
                            </div>
                            @if ($showVotes)
                                <p class="mt-3 text-sm text-slate-300">{{ number_format($entry->votes_count) }} votes · {{ number_format($votePercent, 1) }}%</p>
                            @endif
                            @if ($index === 0)
                                <p class="mt-2 text-xs text-emerald-300">Congratulations to our champion!</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Contestants table --}}
        <div class="tc-card p-4 sm:p-5">
            <div class="mb-4 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h4 class="text-lg font-semibold text-white">Contestants</h4>
                    <p class="mt-1 text-sm text-slate-400">Search, filter, and review performer standings.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <input type="search" data-tc-search placeholder="Search contestant…" class="rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm text-white placeholder:text-slate-500 focus:border-violet-500 focus:outline-none">
                    <select data-tc-grade-filter class="rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm text-white focus:border-violet-500 focus:outline-none">
                        <option value="">All grades</option>
                        @foreach ($grades as $grade)
                            <option value="{{ $grade }}">Grade {{ $grade }}</option>
                        @endforeach
                    </select>
                    <select data-tc-category-filter class="rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm text-white focus:border-violet-500 focus:outline-none">
                        <option value="">All categories</option>
                        <option value="{{ $categoryLabel }}">{{ $categoryLabel }}</option>
                    </select>
                    <select data-tc-status-filter class="rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm text-white focus:border-violet-500 focus:outline-none">
                        <option value="">All statuses</option>
                        <option value="approved">Approved</option>
                        <option value="pending">Pending</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <select data-tc-sort class="rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm text-white focus:border-violet-500 focus:outline-none">
                        <option value="votes-desc">Sort by votes (high)</option>
                        <option value="votes-asc">Sort by votes (low)</option>
                        <option value="rank-asc">Sort by rank</option>
                    </select>
                </div>
            </div>

            @if ($event->entries->isEmpty())
                <div class="tc-empty">
                    <div class="mb-3 text-3xl" aria-hidden="true">🎤</div>
                    <p class="font-semibold text-white">No Contestants</p>
                    <p class="mt-2 max-w-sm text-sm text-slate-400">Add performers when creating or editing this talent competition.</p>
                    @if ($canManageTalentEvents)
                        <a href="{{ route('admin.talent-competition.edit', $event) }}" class="tc-btn tc-btn--ghost mt-4">Manage Contestants</a>
                    @endif
                </div>
            @else
                <p class="mb-3 text-xs text-slate-500" data-tc-table-meta>Showing {{ $event->entries->count() }} contestants</p>
                <div class="tc-table-wrap">
                    <table class="w-full text-left text-xs sm:text-sm" data-tc-table>
                        <thead class="border-b border-slate-800 text-slate-400">
                            <tr>
                                <th class="px-3 py-2">Photo</th>
                                <th class="px-3 py-2">Contestant</th>
                                <th class="px-3 py-2">Grade / Section</th>
                                <th class="px-3 py-2">Talent Category</th>
                                <th class="px-3 py-2">Performance Description</th>
                                <th class="px-3 py-2">Voting Status</th>
                                <th class="px-3 py-2">Votes</th>
                                <th class="px-3 py-2">Rank</th>
                                <th class="px-3 py-2">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @foreach ($rankedEntries as $entry)
                                @php
                                    $detailId = 'tc-entry-'.$event->id.'-'.$entry->id;
                                    $gradeLabel = trim(($entry->grade_level ? 'Grade '.$entry->grade_level : '').($entry->section ? ' · '.$entry->section : ''));
                                @endphp
                                <tr
                                    data-tc-row
                                    data-entry-id="{{ $entry->id }}"
                                    data-name="{{ strtolower($entry->display_name) }}"
                                    data-grade="{{ $entry->grade_level }}"
                                    data-category="{{ $categoryLabel }}"
                                    data-status="{{ $entry->status }}"
                                    data-votes="{{ (int) ($entry->votes_count ?? 0) }}"
                                    data-rank="{{ (int) ($entry->computed_rank ?? 0) }}"
                                    @class(['text-slate-200', 'tc-row-winner' => $entry->computed_rank === 1 && $showVotes && $entry->votes_count > 0])
                                >
                                    <td class="px-3 py-3">
                                        @if ($entry->posterUrl())
                                            <img src="{{ $entry->posterUrl() }}" alt="" class="h-10 w-10 rounded-full object-cover ring-1 ring-slate-700">
                                        @else
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-800 text-xs font-bold text-slate-300">{{ strtoupper(substr($entry->display_name, 0, 1)) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 font-medium text-white">{{ $entry->display_name }}</td>
                                    <td class="px-3 py-3">{{ $gradeLabel ?: '—' }}</td>
                                    <td class="px-3 py-3">{{ $categoryLabel }}</td>
                                    <td class="px-3 py-3 max-w-xs text-slate-400">
                                        <p class="line-clamp-2">{{ $entry->performance_description ?: '—' }}</p>
                                    </td>
                                    <td class="px-3 py-3"><x-admin-status-badge :status="$entry->status" /></td>
                                    <td class="px-3 py-3 font-semibold text-violet-200" data-tc-row-votes>{{ $showVotes ? number_format($entry->votes_count) : '—' }}</td>
                                    <td class="px-3 py-3" data-tc-row-rank>#{{ $entry->computed_rank }}</td>
                                    <td class="px-3 py-3">
                                        <button type="button" data-tc-expand="{{ $detailId }}" class="text-xs font-semibold text-violet-300 hover:text-violet-200">View Details</button>
                                        <div id="{{ $detailId }}" class="mt-2 hidden rounded-lg border border-slate-800 bg-slate-950/70 p-3 text-xs text-slate-300">
                                            @if ($entry->profile_summary)
                                                <p class="font-semibold text-slate-200">Profile</p>
                                                <p class="mt-1">{{ $entry->profile_summary }}</p>
                                            @endif
                                            @if ($entry->performance_title)
                                                <p class="mt-2 font-semibold text-slate-200">Performance Title</p>
                                                <p class="mt-1">{{ $entry->performance_title }}</p>
                                            @endif
                                            @if ($entry->performance_description)
                                                <p class="mt-2 font-semibold text-slate-200">Performance</p>
                                                <p class="mt-1 whitespace-pre-line">{{ $entry->performance_description }}</p>
                                            @endif
                                            @if ($entry->student_id_number || $entry->course_strand)
                                                <p class="mt-2 font-semibold text-slate-200">Student</p>
                                                <p class="mt-1">{{ $entry->student_id_number ?: '—' }}@if($entry->course_strand) · {{ $entry->course_strand }}@endif</p>
                                            @endif
                                            @if ($entry->hasVideo())
                                                <p class="mt-2 font-semibold text-slate-200">Performance Video</p>
                                                <div class="mt-1 flex flex-wrap gap-3">
                                                    @if ($entry->videoEmbedUrl())
                                                        <a href="{{ $entry->video_url }}" target="_blank" rel="noopener" class="font-semibold text-violet-300 hover:text-violet-200">Preview (opens link)</a>
                                                    @elseif ($entry->videoFileUrl())
                                                        <a href="{{ $entry->videoFileUrl() }}" target="_blank" rel="noopener" class="font-semibold text-violet-300 hover:text-violet-200">Preview video</a>
                                                        <a href="{{ $entry->videoDownloadUrl() }}" class="font-semibold text-cyan-300 hover:text-cyan-200">Download video</a>
                                                    @endif
                                                </div>
                                            @endif
                                            @if ($entry->source === \App\Models\TalentEventEntry::SOURCE_SELF && $entry->isPending())
                                                <div class="mt-3 flex flex-wrap gap-2">
                                                    <form method="POST" action="{{ route('admin.talent.entries.approve', $entry) }}">
                                                        @csrf
                                                        <button type="submit" class="rounded-lg border border-emerald-500/40 px-3 py-1.5 text-xs font-semibold text-emerald-200 transition hover:bg-emerald-500/10">Approve</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.talent.entries.reject', $entry) }}" onsubmit="this.querySelector('[name=reason]').value = prompt('Reason for rejection:') || ''; return this.querySelector('[name=reason]').value !== '';">
                                                        @csrf
                                                        <input type="hidden" name="reason" value="">
                                                        <button type="submit" class="rounded-lg border border-rose-500/40 px-3 py-1.5 text-xs font-semibold text-rose-200 transition hover:bg-rose-500/10">Reject</button>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($showVotes && $totalVotes === 0)
                    <div class="tc-empty mt-4">
                        <div class="mb-2 text-2xl" aria-hidden="true">🗳</div>
                        <p class="font-medium text-slate-300">No Votes Yet</p>
                        <p class="mt-1 text-sm text-slate-500">Votes will appear here once students start voting.</p>
                    </div>
                @endif
            @endif
        </div>

        {{-- Recent activity --}}
        <div class="tc-card p-4 sm:p-5">
            <h4 class="text-sm font-semibold uppercase tracking-wide text-violet-300">Recent Activity</h4>
            @if ($activity->isEmpty())
                <div class="tc-empty mt-4 min-h-[10rem]">
                    <p class="text-sm text-slate-400">No activity timestamps recorded yet.</p>
                </div>
            @else
                <div class="tc-timeline relative mt-4 space-y-1">
                    @foreach ($activity as $item)
                        <div class="tc-timeline-item">
                            <p class="text-sm font-semibold text-white">{{ $item['icon'] }} {{ $item['label'] }}</p>
                            <p class="text-xs text-slate-400">{{ $item['at']?->format('M d, Y g:i A') }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>

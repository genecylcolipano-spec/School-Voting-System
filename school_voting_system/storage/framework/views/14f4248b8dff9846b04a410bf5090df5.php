<?php
    $countdownRemaining = $countdown['remaining'] ?? '—';
    $countdownLabel = $countdown['label'] ?? 'Voting Starts In';
    $countdownPhase = $countdown['phase'] ?? 'before_start';
    $countdownTargetIso = $countdown['target_at_iso'] ?? ($countdown['ends_at_iso'] ?? ($election?->voting_ends_at?->toIso8601String() ?? ''));
    $countdownStartsIso = $countdown['starts_at_iso'] ?? ($election?->voting_starts_at?->toIso8601String() ?? '');
    $countdownEndsIso = $countdown['ends_at_iso'] ?? ($election?->voting_ends_at?->toIso8601String() ?? '');
    $countdownHint = match ($countdownPhase) {
        'before_start' => 'Until voting window opens',
        'active' => 'Until voting window closes',
        default => 'Voting window closed',
    };
    $showLivePanel = $election
        && ! $election->annulled_at
        && $election->status?->isOpenForVoting();

    $electionStatus = $statistics['election_status'] ?? 'Unassigned';
    $statusTone = match (true) {
        $election?->is_paused, str_contains(strtolower($electionStatus), 'paused') => 'paused',
        $election?->status?->value === 'closed', str_contains(strtolower($electionStatus), 'closed'), str_contains(strtolower($electionStatus), 'ended'), str_contains(strtolower($electionStatus), 'completed') => 'closed',
        $election?->status?->value === 'active', str_contains(strtolower($electionStatus), 'open'), str_contains(strtolower($electionStatus), 'active') => 'live',
        default => 'idle',
    };

    $electionCategory = $election
        ? ($election->relationLoaded('categories')
            ? $election->categories->pluck('name')->join(', ')
            : $election->categories()->limit(3)->pluck('name')->join(', '))
        : '';
    $electionCategory = $electionCategory !== '' ? $electionCategory : 'Student Government Election';

    $academicYear = \App\Support\SchoolBranding::academicYear();
    $academicSemester = \App\Support\SchoolBranding::semester();

    $scheduleDate = $election?->voting_starts_at?->format('F j, Y') ?? '—';
    $scheduleTime = $election?->voting_starts_at && $election?->voting_ends_at
        ? $election->voting_starts_at->format('g:i A').' – '.$election->voting_ends_at->format('g:i A')
        : ($election?->voting_starts_at?->format('g:i A') ?? '—');

    $createdBy = $election?->creator?->name
        ?? ($election?->created_by ? 'Operations Admin' : '—');

    $isPaused = (bool) ($election?->is_paused ?? false);
    $isCompleted = in_array($election?->status?->value, ['closed', 'archived'], true)
        || ($countdownPhase === 'ended');
    $isResultsPublished = (bool) ($election?->public_results_published ?? false);
    $isOpen = $showLivePanel && ! $isPaused && ! $isCompleted;

    $liveVotesCast = (int) ($statistics['votes_cast'] ?? 0);
    $liveTurnout = (float) ($statistics['turnout_percent'] ?? 0);
    $liveEligible = (int) ($statistics['eligible_voters'] ?? ($voterBreakdown['eligible'] ?? 0));
    $liveUniqueVoters = (int) ($voterBreakdown['voted'] ?? 0);
?>

<div
    id="live-voting-panel"
    class="vm-dashboard flex h-full w-full flex-col border-y border-violet-500/15 bg-slate-900/80 px-4 py-5 shadow-sm shadow-black/20 sm:px-6 lg:px-8"
    data-live-voting-url="<?php echo e(route('admin.dashboard.live-voting')); ?>"
    data-voting-starts="<?php echo e($countdownStartsIso); ?>"
    data-voting-ends="<?php echo e($countdownEndsIso); ?>"
    data-countdown-phase="<?php echo e($countdownPhase); ?>"
    data-is-paused="<?php echo e($isPaused ? '1' : '0'); ?>"
>
    
    <nav class="vm-fade-in mb-4 flex flex-wrap items-center gap-1.5 text-xs text-slate-500" aria-label="Breadcrumb">
        <a href="<?php echo e(route('admin.dashboard')); ?>" class="font-medium text-slate-400 transition hover:text-violet-300">Dashboard</a>
        <span aria-hidden="true" class="text-slate-600">›</span>
        <span class="font-medium text-slate-400">Voting Management</span>
        <span aria-hidden="true" class="text-slate-600">›</span>
        <span class="font-semibold text-violet-300">Live Monitoring</span>
    </nav>

    
    <header class="vm-fade-in rounded-2xl border border-violet-500/15 bg-slate-950/50 p-4 sm:p-5 lg:p-6">
        <div class="grid gap-5 lg:grid-cols-12 lg:items-start">
            <div class="min-w-0 lg:col-span-7">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-violet-400">Voting Management</p>
                <h2 class="mt-1 text-xl font-bold text-white sm:text-2xl">Live Monitoring</h2>
                <p id="live-voting-election-title" class="mt-1 truncate text-sm font-medium text-slate-200">
                    <?php echo e($election?->title ?? 'No assigned election'); ?>

                </p>
                <p id="live-voting-monitor-note" class="mt-2 text-sm text-slate-400">
                    Real-time election monitoring and administration.
                </p>
            </div>

            <div class="flex flex-col gap-3 lg:col-span-5 lg:items-end">
                <span
                    id="live-voting-status"
                    data-fallback="<?php echo e($electionStatus); ?>"
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'vm-badge',
                        'vm-badge--live' => $statusTone === 'live',
                        'vm-badge--paused' => $statusTone === 'paused',
                        'vm-badge--closed' => $statusTone === 'closed',
                        'vm-badge--idle' => $statusTone === 'idle',
                    ]); ?>"
                    role="status"
                    aria-live="polite"
                ><?php echo e($electionStatus); ?></span>

                <div class="w-full rounded-xl border border-violet-500/20 bg-slate-900/80 px-4 py-3 text-right lg:w-auto lg:min-w-[14rem]">
                    <p id="live-voting-countdown-label" class="text-[10px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e($countdownLabel); ?></p>
                    <p id="live-voting-countdown" class="mt-0.5 text-xl font-bold tabular-nums text-violet-200" data-countdown-display>
                        <?php echo e($countdownRemaining); ?>

                    </p>
                    <p id="live-voting-updated-at" class="mt-1 text-[11px] text-slate-500">
                        <?php if($election): ?>
                            Awaiting live sync…
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </header>

    <?php if(! $election): ?>
        <div class="vm-empty-state vm-slide-up mt-6 rounded-2xl border border-dashed border-slate-700 bg-slate-950/40">
            <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-500/10 text-2xl">🗳</div>
            <p class="text-base font-semibold text-white">No Election Selected</p>
            <p class="mt-2 max-w-md text-sm text-slate-400">Assign an election to your administrator account to begin live monitoring.</p>
            <a href="<?php echo e(route('admin.elections.index')); ?>" class="vm-btn vm-btn--ghost mt-5">Manage Elections</a>
        </div>
    <?php else: ?>
        
        <section class="vm-card vm-fade-in mt-5 p-4 sm:p-5" aria-label="Election information">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-violet-300">Election Information</h3>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Election Name</dt>
                    <dd class="mt-1 text-sm font-medium text-white"><?php echo e($election->title); ?></dd>
                </div>
                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Category</dt>
                    <dd class="mt-1 text-sm font-medium text-white"><?php echo e($electionCategory); ?></dd>
                </div>
                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Voting Scope</dt>
                    <dd class="mt-1 text-sm font-medium text-white"><?php echo e($assignedRole ?? 'Operations Admin'); ?></dd>
                </div>
                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Academic Year</dt>
                    <dd class="mt-1 text-sm font-medium text-white"><?php echo e($academicYear); ?></dd>
                    <p class="mt-0.5 text-xs text-slate-400"><?php echo e($academicSemester); ?></p>
                </div>
                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Voting Schedule</dt>
                    <dd class="mt-1 text-sm font-medium text-white">
                        <?php echo e($scheduleDate); ?><br>
                        <span class="text-slate-400"><?php echo e($scheduleTime); ?></span>
                    </dd>
                </div>
                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Election Status</dt>
                    <dd class="mt-1 text-sm font-medium text-white"><?php echo e($electionStatus); ?></dd>
                </div>
                <div class="sm:col-span-2 xl:col-span-3">
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Created By</dt>
                    <dd class="mt-1 text-sm font-medium text-white"><?php echo e($createdBy); ?></dd>
                </div>
            </dl>
        </section>

        
        <div class="vm-fade-in mt-4 flex flex-wrap items-center gap-2 rounded-2xl border border-violet-500/10 bg-slate-950/40 p-3 sm:p-4" role="toolbar" aria-label="Election quick actions">
            <?php if($canPauseElection): ?>
                <?php if($election->is_paused): ?>
                    <form method="POST" action="<?php echo e(route('admin.election.resume', $election)); ?>" data-confirm-sensitive data-confirm-title="Resume election?">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="vm-btn vm-btn--success" aria-label="Resume election">
                            ▶ Resume Election
                        </button>
                    </form>
                <?php elseif($showLivePanel || $election->status?->value === 'active'): ?>
                    <form method="POST" action="<?php echo e(route('admin.election.pause', $election)); ?>" data-confirm-sensitive data-confirm-title="Pause election?">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="vm-btn vm-btn--warning" aria-label="Pause election">
                            ⏸ Pause Election
                        </button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>

            <a href="<?php echo e(route('admin.results.election.show', $election)); ?>" class="vm-btn vm-btn--primary" aria-label="View results for <?php echo e($election->title); ?>">
                🏆 View Results
            </a>

            <?php if($canEditElection ?? false): ?>
                <a href="<?php echo e(route('admin.elections.edit', $election)); ?>" class="vm-btn vm-btn--ghost" aria-label="Edit election settings">
                    ✏ Edit Election
                </a>
            <?php endif; ?>

            <?php if($canExportPreliminary ?? false): ?>
                <a href="<?php echo e(route('admin.results.election.export', ['election' => $election, 'format' => 'csv'])); ?>" class="vm-btn vm-btn--ghost" aria-label="Export results">
                    ⬇ Export Results
                </a>
            <?php endif; ?>
        </div>

        
        <div
            id="live-voting-live-status"
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'vm-live-status vm-fade-in mt-4',
                'vm-live-status--live' => $isOpen,
                'vm-live-status--paused' => $isPaused,
                'vm-live-status--completed' => $isCompleted && ! $isPaused,
                'hidden' => ! $isOpen && ! $isPaused && ! $isCompleted,
            ]); ?>"
            data-state="<?php echo e($isPaused ? 'paused' : ($isOpen ? 'live' : ($isCompleted ? 'completed' : 'idle'))); ?>"
        >
            <?php if($isOpen): ?>
                <span class="vm-live-status__icon" aria-hidden="true">🟢</span>
                <div>
                    <p class="font-semibold text-emerald-200">LIVE</p>
                    <p class="text-sm text-emerald-100/80">Votes are updating automatically every 5 seconds.</p>
                </div>
            <?php elseif($isPaused): ?>
                <span class="vm-live-status__icon" aria-hidden="true">🟡</span>
                <div>
                    <p class="font-semibold text-amber-200">PAUSED</p>
                    <p class="text-sm text-amber-100/80">Voting is temporarily suspended.</p>
                </div>
            <?php elseif($isCompleted): ?>
                <span class="vm-live-status__icon" aria-hidden="true"><?php echo e($isResultsPublished ? '🏆' : '🔵'); ?></span>
                <div>
                    <p class="font-semibold text-sky-200"><?php echo e($isResultsPublished ? 'RESULTS PUBLISHED' : 'COMPLETED'); ?></p>
                    <p class="text-sm text-sky-100/80">
                        <?php echo e($isResultsPublished
                            ? 'Official results are available to students.'
                            : 'Voting has ended. Review and publish official results when ready.'); ?>

                    </p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    
    <div id="live-voting-idle" class="vm-slide-up mt-6 flex flex-1 flex-col items-center justify-center rounded-2xl border border-dashed border-slate-700 bg-slate-950/40 px-4 py-12 text-center<?php echo e($showLivePanel ? ' hidden' : ''); ?>">
        <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-500/10 text-2xl">📊</div>
        <p id="live-voting-idle-title" class="text-base font-semibold text-white">No Votes Yet</p>
        <p id="live-voting-idle-message" class="mt-2 max-w-md text-sm text-slate-400">
            Voting has not started. Set the election to Active within the voting window to unlock live statistics.
        </p>
        <?php if($election): ?>
            <a href="<?php echo e(route('admin.elections.edit', $election)); ?>" class="vm-btn vm-btn--ghost mt-5">Open election settings</a>
        <?php endif; ?>
    </div>

    
    <div id="live-voting-active" class="vm-layout-stack mt-6<?php echo e($showLivePanel ? '' : ' hidden'); ?>">
        
        <div class="vm-layout-grid vm-layout-grid--stats">
            <article class="vm-stat-card vm-stat-card--enhanced vm-slide-up" style="animation-delay: 0.05s">
                <div class="vm-stat-card__head">
                    <span class="vm-stat-card__icon" aria-hidden="true">🗳</span>
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Total Votes</p>
                </div>
                <p id="live-voting-total-votes" class="vm-stat-card__value" data-animate-counter="<?php echo e($liveVotesCast); ?>"><?php echo e(number_format($liveVotesCast)); ?></p>
                <p class="vm-stat-card__meta text-emerald-300">Updated live</p>
            </article>

            <article class="vm-stat-card vm-stat-card--enhanced vm-slide-up" style="animation-delay: 0.1s">
                <div class="vm-stat-card__head">
                    <span class="vm-stat-card__icon" aria-hidden="true">👥</span>
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Turnout</p>
                </div>
                <p id="live-voting-turnout" class="vm-stat-card__value" data-animate-counter="<?php echo e($liveTurnout); ?>"><?php echo e(number_format($liveTurnout, 1)); ?>%</p>
                <p id="live-voting-turnout-detail" class="vm-stat-card__meta"><?php echo e(number_format($liveUniqueVoters)); ?> / <?php echo e(number_format($liveEligible)); ?> Students</p>
            </article>

            <article class="vm-stat-card vm-stat-card--enhanced vm-slide-up" style="animation-delay: 0.15s">
                <div class="vm-stat-card__head">
                    <span class="vm-stat-card__icon" aria-hidden="true">⏱</span>
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Remaining Time</p>
                </div>
                <p id="live-voting-stat-countdown" class="vm-stat-card__value tabular-nums"><?php echo e($countdownRemaining); ?></p>
                <p id="live-voting-stat-countdown-hint" class="vm-stat-card__meta"><?php echo e($countdownHint); ?></p>
            </article>

            <article class="vm-stat-card vm-stat-card--enhanced vm-slide-up" style="animation-delay: 0.2s">
                <div class="vm-stat-card__head">
                    <span class="vm-stat-card__icon" aria-hidden="true">🎓</span>
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Registered Students</p>
                </div>
                <p id="live-voting-registered" class="vm-stat-card__value" data-animate-counter="<?php echo e($liveEligible); ?>"><?php echo e(number_format($liveEligible)); ?></p>
                <p class="vm-stat-card__meta">Eligible enrolled students</p>
            </article>
        </div>

        
        <div class="vm-layout-grid vm-layout-grid--split">
            <section class="vm-card vm-fade-in vm-panel-block vm-layout-grid--split-chart p-5" aria-label="Vote distribution chart">
                <div class="vm-panel-block__head flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-white">Vote Distribution</p>
                        <p class="mt-0.5 text-xs text-slate-400">Position leaders by ballot share</p>
                    </div>
                    <p id="live-voting-chart-updated" class="text-[10px] text-slate-500"></p>
                </div>
                <div id="live-voting-chart" class="vm-panel-block__body vm-panel-block__body--chart"></div>
                <div id="live-voting-chart-legend" class="mt-3 flex flex-wrap gap-2 text-[10px] text-slate-400"></div>
                <div id="live-voting-bars" class="hidden" aria-hidden="true"></div>
            </section>

            <section class="vm-card vm-fade-in vm-panel-block vm-layout-grid--split-activity p-5" aria-label="Recent voting activity">
                <div class="vm-panel-block__head">
                    <p class="text-sm font-semibold text-white">Recent Activity</p>
                    <p class="mt-0.5 text-xs text-slate-400">Latest ballots and administrator events</p>
                </div>
                <div id="live-voting-activity" class="vm-panel-block__body vm-timeline overflow-y-auto pr-1"></div>
            </section>
        </div>

        
        <section class="vm-fade-in" aria-label="Candidate rankings">
            <div class="vm-panel-block__head mb-4 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-white">Candidate Rankings</h3>
                    <p class="mt-1 text-sm text-slate-400">Search, filter, and review standings by position and party</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <input
                        type="search"
                        id="live-voting-rankings-search"
                        placeholder="Search candidate…"
                        class="rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm text-white placeholder:text-slate-500 focus:border-violet-500 focus:outline-none"
                    >
                    <select id="live-voting-rankings-position-filter" class="rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm text-white focus:border-violet-500 focus:outline-none">
                        <option value="">All positions</option>
                    </select>
                    <select id="live-voting-rankings-party-filter" class="rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm text-white focus:border-violet-500 focus:outline-none">
                        <option value="">All parties</option>
                    </select>
                    <select id="live-voting-rankings-sort" class="rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm text-white focus:border-violet-500 focus:outline-none">
                        <option value="position">Sort by position</option>
                        <option value="votes-desc">Sort by votes (high)</option>
                        <option value="votes-asc">Sort by votes (low)</option>
                    </select>
                </div>
            </div>
            <div id="live-voting-leaders" class="vm-candidate-positions-grid"></div>
        </section>

        
        <section class="vm-fade-in" aria-label="Party performance">
            <div class="vm-panel-block__head">
                <h3 class="text-lg font-semibold text-white">Party Performance</h3>
                <p class="mt-1 text-sm text-slate-400">Partylist live comparison — votes, share, seats won, and leading candidate</p>
            </div>
            <div id="live-voting-partylists" class="vm-layout-grid vm-layout-grid--cards"></div>
        </section>

        
        <footer class="vm-card vm-fade-in p-4 sm:p-5" aria-label="System status">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Auto Refresh</p>
                    <p class="mt-1 text-sm font-medium text-white">Every 5 Seconds</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Connection Status</p>
                    <p id="live-voting-connection-status" class="mt-1 flex items-center gap-2 text-sm font-medium text-emerald-300">
                        <span id="live-voting-pulse" class="relative hidden h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                        </span>
                        <span id="live-voting-dot" class="hidden h-2 w-2 rounded-full bg-slate-600" aria-hidden="true"></span>
                        <span id="live-voting-connection-label">Connected</span>
                    </p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Last Updated</p>
                    <p id="live-voting-system-updated" class="mt-1 text-sm font-medium text-white">—</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Current Time</p>
                    <p id="live-voting-system-clock" class="mt-1 text-sm font-medium text-white tabular-nums"><?php echo e(now()->format('g:i A')); ?></p>
                </div>
            </div>
        </footer>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/admin-live-voting.css'); ?>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/dashboard/_live-voting.blade.php ENDPATH**/ ?>
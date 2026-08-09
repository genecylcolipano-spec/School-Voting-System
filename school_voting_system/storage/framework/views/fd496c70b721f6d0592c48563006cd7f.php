<?php
    $statusTone = match ($card['status_key'] ?? '') {
        'voting_open' => 'bg-emerald-500/15 text-emerald-200',
        'voting_paused' => 'bg-amber-500/15 text-amber-200',
        'results_pending', 'voting_closed' => 'bg-orange-500/15 text-orange-200',
        'published' => 'bg-sky-500/15 text-sky-200',
        'registration_open', 'judging_open' => 'bg-violet-500/15 text-violet-200',
        default => 'bg-slate-700/40 text-slate-300',
    };
    $judgePct = ($card['judges_total'] ?? 0) > 0
        ? min(100, round((($card['judges_completed'] ?? 0) / max(1, $card['judges_total'])) * 100, 1))
        : 0;
?>

<article
    class="overflow-hidden rounded-2xl border <?php echo e(! empty($card['is_live']) ? 'border-emerald-500/30' : 'border-violet-500/15'); ?> bg-slate-900/80"
    data-live-card
    data-card-id="<?php echo e($card['id']); ?>"
    data-card-type="talent"
    data-votes-cast="<?php echo e((int) ($card['votes_cast'] ?? 0)); ?>"
>
    <div class="relative aspect-[21/7] overflow-hidden bg-slate-950">
        <img src="<?php echo e($card['banner_url']); ?>" alt="" class="h-full w-full object-cover opacity-85" data-field="banner_url">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/25 to-transparent"></div>
        <div class="absolute left-3 top-3">
            <?php echo $__env->make('admin.live-monitoring._live-badge', ['isLive' => ! empty($card['is_live'])], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
        <div class="absolute bottom-3 left-3 right-3 flex flex-wrap items-end justify-between gap-2">
            <div class="min-w-0 pr-2">
                <h3 class="truncate text-base font-bold text-white sm:text-lg" data-field="name"><?php echo e($card['name']); ?></h3>
                <p class="mt-0.5 truncate text-xs text-violet-300" data-field="category"><?php echo e($card['category'] ?? 'Talent Competition'); ?></p>
                <p class="truncate text-xs text-slate-300">
                    <span class="text-slate-500">Owner</span>
                    <span data-field="owner_name"><?php echo e($card['owner_name']); ?></span>
                    <?php if(! empty($card['owner_account'])): ?>
                        <span class="text-slate-500">(<?php echo e($card['owner_account']); ?>)</span>
                    <?php endif; ?>
                </p>
            </div>
            <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide <?php echo e($statusTone); ?>" data-field="status_label"><?php echo e($card['status_label']); ?></span>
        </div>
    </div>

    <div class="space-y-3.5 p-4">
        <?php echo $__env->make('admin.live-monitoring._phase-timeline', ['steps' => $card['phase_steps'] ?? []], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Registrations</p>
                <p class="mt-1 text-xl font-bold text-white" data-field="registration_count" data-flashable><?php echo e(number_format($card['registration_count'] ?? 0)); ?></p>
            </div>
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Approved</p>
                <p class="mt-1 text-xl font-bold text-white" data-field="approved_participants" data-flashable><?php echo e(number_format($card['approved_participants'] ?? 0)); ?></p>
            </div>
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Votes</p>
                <p class="mt-1 text-xl font-bold text-white transition-colors duration-300" data-field="votes_cast" data-flashable><?php echo e(number_format($card['votes_cast'] ?? 0)); ?></p>
            </div>
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Last Vote</p>
                <p class="mt-1 text-sm font-semibold leading-snug text-slate-200" data-field="last_vote_at" data-flashable><?php echo e($card['last_vote_at'] ?? '—'); ?></p>
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                <span>Judges Completed</span>
                <span class="normal-case tracking-normal text-slate-400">
                    <span data-field="judges_completed"><?php echo e($card['judges_completed'] ?? 0); ?></span>/<span data-field="judges_total"><?php echo e($card['judges_total'] ?? 0); ?></span>
                    · <span data-field="judges_remaining"><?php echo e($card['judges_remaining'] ?? 0); ?></span> remaining
                </span>
            </div>
            <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-800">
                <div class="h-full rounded-full bg-cyan-500 transition-all duration-500" data-judges-bar style="width: <?php echo e($judgePct); ?>%"></div>
            </div>
        </div>

        <?php if(! empty($card['is_live'])): ?>
            <div class="overflow-hidden rounded-xl border border-violet-500/15 bg-slate-950/40" data-leaderboard>
                <div class="border-b border-slate-800 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Live Leaderboard</div>
                <ol class="<?php echo e(empty($card['rankings']) ? 'hidden' : ''); ?> divide-y divide-slate-800" data-leaderboard-list>
                    <?php $__currentLoopData = collect($card['rankings'] ?? [])->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="flex items-center gap-3 px-3 py-2 text-sm">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-violet-500/15 text-xs font-bold text-violet-200"><?php echo e($row['rank']); ?></span>
                            <span class="min-w-0 flex-1 truncate text-slate-200"><?php echo e($row['name']); ?></span>
                            <span class="font-bold text-white"><?php echo e(number_format($row['votes'] ?? 0)); ?></span>
                            <span class="w-12 text-right text-xs text-slate-400"><?php echo e($row['percent'] ?? 0); ?>%</span>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ol>
                <p class="<?php echo e(empty($card['rankings']) ? '' : 'hidden'); ?> px-3 py-4 text-sm text-slate-400" data-leaderboard-empty>Waiting for votes…</p>
            </div>
        <?php endif; ?>

        <div class="flex flex-wrap gap-2 pt-0.5">
            <a href="<?php echo e($card['details_url']); ?>" class="rounded-xl border border-slate-700 px-3 py-1.5 text-sm font-semibold text-slate-300 hover:bg-slate-800">Open Details</a>
            <?php if(! empty($card['show_results_shortcut'])): ?>
                <a href="<?php echo e($card['results_url']); ?>" class="rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 px-3 py-1.5 text-sm font-semibold text-white hover:opacity-90">View Results</a>
            <?php endif; ?>

            <?php if(($canManage ?? false) && ! empty($card['can_manage_live'])): ?>
                <?php if(! empty($card['is_paused'])): ?>
                    <form method="POST" action="<?php echo e($card['actions']['resume']); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="rounded-xl border border-emerald-500/40 px-3 py-1.5 text-sm font-semibold text-emerald-200 hover:bg-emerald-500/10">Resume</button>
                    </form>
                <?php else: ?>
                    <form method="POST" action="<?php echo e($card['actions']['pause']); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="rounded-xl border border-amber-500/40 px-3 py-1.5 text-sm font-semibold text-amber-200 hover:bg-amber-500/10">Pause</button>
                    </form>
                <?php endif; ?>
                <form method="POST" action="<?php echo e($card['actions']['close']); ?>" data-confirm-sensitive data-confirm-title="Close Voting?" data-confirm-message="Close voting for this competition now?">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="rounded-xl border border-rose-500/40 px-3 py-1.5 text-sm font-semibold text-rose-200 hover:bg-rose-500/10">Close</button>
                </form>
            <?php endif; ?>

            <?php if(($canExport ?? false) && ! empty($card['actions']['export'])): ?>
                <a href="<?php echo e($card['actions']['export']); ?>" class="rounded-xl border border-cyan-500/40 px-3 py-1.5 text-sm font-semibold text-cyan-200 hover:bg-cyan-500/10">Export</a>
            <?php endif; ?>
        </div>
    </div>
</article>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/live-monitoring/_talent-card.blade.php ENDPATH**/ ?>
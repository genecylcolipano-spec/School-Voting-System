<?php
    $detail = $detail ?? [];
    $isLive = $detail['is_live'] ?? false;
    $isFinal = $detail['is_final'] ?? false;
    $isPublished = $detail['is_published'] ?? false;
    $isReadyForReview = ($detail['is_ready_for_review'] ?? false) && ! $isPublished;
    $isOfficial = $isPublished;
    $winnersPrimary = collect($detail['winners'] ?? [])->reject(fn ($w) => ($w['group'] ?? null) === 'top_ten');
    $winnersTopTen = collect($detail['winners'] ?? [])->filter(fn ($w) => ($w['group'] ?? null) === 'top_ten');
    $partyPerformance = collect($detail['party_performance'] ?? []);
    $turnoutSections = collect($detail['turnout_sections'] ?? []);
?>

<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginal57da683fe32826f08aa9f05c3342a7e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal57da683fe32826f08aa9f05c3342a7e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => $detail['name'] ?? 'Results','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($detail['name'] ?? 'Results'),'user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <div
            id="admin-results-dashboard"
            data-live-url="<?php echo e($liveUrl); ?>"
            data-is-live="<?php echo e($isLive ? '1' : '0'); ?>"
            data-is-final="<?php echo e($isFinal ? '1' : '0'); ?>"
        >
            <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <a href="<?php echo e($backUrl); ?>" class="text-sm font-semibold text-violet-300 hover:text-violet-200">← Results Dashboard</a>
                    <h2 class="mt-2 text-2xl font-bold text-white"><?php echo e($detail['name']); ?></h2>
                    <p class="mt-1 text-sm text-slate-400"><?php echo e($detail['category'] ?? 'Event'); ?></p>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2">
                    <?php if($detail['can_publish'] ?? false): ?>
                        <form method="POST" action="<?php echo e($detail['publish_url']); ?>" data-confirm-sensitive data-confirm-title="Publish official results to students?" class="inline">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="rs-export-btn rs-export-btn--primary">Publish Official Results</button>
                        </form>
                    <?php endif; ?>

                    <?php if($detail['can_unpublish'] ?? false): ?>
                        <form method="POST" action="<?php echo e($detail['unpublish_url']); ?>" data-confirm-sensitive data-confirm-title="Unpublish official results from students?" class="inline">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="rs-export-btn">Unpublish Results</button>
                        </form>
                    <?php endif; ?>

                    <?php if($detail['can_export'] ?? false): ?>
                        <a href="<?php echo e($exportUrls['pdf']); ?>" class="rs-export-btn">Export PDF</a>
                        <a href="<?php echo e($exportUrls['excel']); ?>" class="rs-export-btn">Export Excel</a>
                        <a href="<?php echo e($exportUrls['csv']); ?>" class="rs-export-btn">Export CSV</a>
                        <button type="button" data-results-print class="rs-export-btn">Print Results</button>
                    <?php endif; ?>
                </div>
            </div>

            <?php if($isLive): ?>
                <div class="rs-banner rs-banner--live mb-6">
                    <span class="rs-banner__dot"></span>
                    <div>
                        <p class="font-semibold text-amber-200">🟡 LIVE RESULTS</p>
                        <p class="text-sm text-amber-100/80">Current standings are unofficial. Results automatically update.</p>
                    </div>
                    <p id="results-live-updated" class="ml-auto text-xs text-amber-200/70">Updating…</p>
                </div>
            <?php elseif($isReadyForReview): ?>
                <div class="rs-banner rs-banner--review mb-6">
                    <span class="rs-banner__dot rs-banner__dot--review"></span>
                    <div>
                        <p class="font-semibold text-amber-200">📋 Review Results</p>
                        <p class="text-sm text-amber-100/80">Voting has ended. Review winners, rankings, and turnout before publishing official results to students.</p>
                    </div>
                </div>
            <?php elseif($isOfficial): ?>
                <div class="rs-banner rs-banner--final mb-6">
                    <span class="rs-banner__dot rs-banner__dot--final"></span>
                    <div>
                        <p class="font-semibold text-emerald-200">🟢 OFFICIAL RESULTS</p>
                        <p class="text-sm text-emerald-100/80">Congratulations to all winners.</p>
                    </div>
                </div>
            <?php endif; ?>

            <section class="mb-6 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h3 class="text-lg font-semibold text-white">Event Information</h3>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Election / Event Name</dt>
                        <dd class="mt-1 font-medium text-white"><?php echo e($detail['name']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Category</dt>
                        <dd class="mt-1 font-medium text-white"><?php echo e($detail['category'] ?? '—'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Status</dt>
                        <dd class="mt-1 font-medium text-white"><?php echo e($detail['voting_status'] ?? '—'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Voting Period</dt>
                        <dd class="mt-1 font-medium text-white"><?php echo e($detail['starts_at'] ?? '—'); ?> — <?php echo e($detail['ends_at'] ?? '—'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Generated Date</dt>
                        <dd class="mt-1 font-medium text-white"><?php echo e($detail['generated_at'] ?? now()->format('M d, Y g:i A')); ?></dd>
                    </div>
                    <?php if(($detail['type'] ?? null) === 'talent' && ! empty($detail['event_settings'])): ?>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Talent Category</dt>
                            <dd class="mt-1 font-medium text-white"><?php echo e($detail['event_settings']['talent_category'] ?? '—'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Performance Duration</dt>
                            <dd class="mt-1 font-medium text-white"><?php echo e($detail['event_settings']['performance_duration'] ?? '—'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Voting Method</dt>
                            <dd class="mt-1 font-medium text-white"><?php echo e($detail['event_settings']['voting_method'] ?? '—'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Contestants</dt>
                            <dd class="mt-1 font-medium text-white"><?php echo e($detail['event_settings']['contestants'] ?? '—'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Number of Winners</dt>
                            <dd class="mt-1 font-medium text-white"><?php echo e($detail['event_settings']['number_of_winners'] ?? '—'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Participants</dt>
                            <dd class="mt-1 font-medium text-white">
                                <?php echo e($detail['event_settings']['total_participants'] ?? 0); ?>

                                <span class="text-xs text-slate-500">(Approved <?php echo e($detail['event_settings']['approved_participants'] ?? 0); ?> · Pending <?php echo e($detail['event_settings']['pending_participants'] ?? 0); ?> · Rejected <?php echo e($detail['event_settings']['rejected_participants'] ?? 0); ?>)</span>
                            </dd>
                        </div>
                        <?php if(! empty($detail['event_settings']['most_viewed'])): ?>
                            <div>
                                <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Most Viewed Performance</dt>
                                <dd class="mt-1 font-medium text-white">
                                    <?php echo e($detail['event_settings']['most_viewed']['title'] ?: $detail['event_settings']['most_viewed']['name']); ?>

                                    <span class="text-xs text-slate-500">(<?php echo e(number_format($detail['event_settings']['most_viewed']['views'])); ?> views)</span>
                                </dd>
                            </div>
                        <?php endif; ?>
                        <?php if(! empty($detail['event_settings']['category_stats'])): ?>
                            <div class="sm:col-span-2 lg:col-span-3">
                                <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Talent Category Statistics</dt>
                                <dd class="mt-1 flex flex-wrap gap-2">
                                    <?php $__currentLoopData = $detail['event_settings']['category_stats']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="rounded-full border border-violet-500/30 bg-violet-500/10 px-3 py-1 text-xs font-medium text-violet-200"><?php echo e($stat['category']); ?>: <?php echo e($stat['count']); ?></span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </dd>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if(! empty($detail['results_published_at'])): ?>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Published</dt>
                            <dd class="mt-1 font-medium text-white">
                                <?php echo e($detail['results_published_at']); ?>

                                <?php if(! empty($detail['results_published_by'])): ?>
                                    <span class="block text-xs text-slate-400">by <?php echo e($detail['results_published_by']); ?></span>
                                <?php endif; ?>
                            </dd>
                        </div>
                    <?php endif; ?>
                </dl>
            </section>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="vm-stat-card">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">🗳 Total Votes</p>
                    <p class="mt-2 text-2xl font-bold text-white" data-results-stat="total_votes"><?php echo e(number_format($detail['summary']['total_votes'] ?? 0)); ?></p>
                </div>
                <div class="vm-stat-card">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">👥 Turnout</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-300" data-results-stat="turnout_percent"><?php echo e(number_format($detail['summary']['turnout_percent'] ?? 0, 1)); ?>%</p>
                </div>
                <div class="vm-stat-card">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">🏆 Winning Positions</p>
                    <p class="mt-2 text-2xl font-bold text-violet-200" data-results-stat="winners_count"><?php echo e(number_format($detail['summary']['winners_count'] ?? 0)); ?></p>
                </div>
                <div class="vm-stat-card">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">🎓 Registered Participants</p>
                    <p class="mt-2 text-2xl font-bold text-white" data-results-stat="participants"><?php echo e(number_format($detail['summary']['participants'] ?? 0)); ?></p>
                </div>
            </div>

            <?php if (isset($component)) { $__componentOriginalbdbf2aa07bfa4a6167c398f43d9565d5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbdbf2aa07bfa4a6167c398f43d9565d5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.event-lifecycle-stepper','data' => ['steps' => $detail['lifecycle_steps'] ?? []]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('event-lifecycle-stepper'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['steps' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($detail['lifecycle_steps'] ?? [])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbdbf2aa07bfa4a6167c398f43d9565d5)): ?>
<?php $attributes = $__attributesOriginalbdbf2aa07bfa4a6167c398f43d9565d5; ?>
<?php unset($__attributesOriginalbdbf2aa07bfa4a6167c398f43d9565d5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbdbf2aa07bfa4a6167c398f43d9565d5)): ?>
<?php $component = $__componentOriginalbdbf2aa07bfa4a6167c398f43d9565d5; ?>
<?php unset($__componentOriginalbdbf2aa07bfa4a6167c398f43d9565d5); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginal578dbf52e12dc6d3ec213f47252a1a45 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal578dbf52e12dc6d3ec213f47252a1a45 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.winner-spotlight','data' => ['spotlight' => $detail['winner_spotlight'] ?? [],'primary' => $detail['primary_winner'] ?? null,'publishedAt' => $detail['results_published_at'] ?? null,'publishedBy' => $detail['results_published_by'] ?? null,'theme' => 'admin']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('winner-spotlight'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['spotlight' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($detail['winner_spotlight'] ?? []),'primary' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($detail['primary_winner'] ?? null),'published-at' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($detail['results_published_at'] ?? null),'published-by' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($detail['results_published_by'] ?? null),'theme' => 'admin']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal578dbf52e12dc6d3ec213f47252a1a45)): ?>
<?php $attributes = $__attributesOriginal578dbf52e12dc6d3ec213f47252a1a45; ?>
<?php unset($__attributesOriginal578dbf52e12dc6d3ec213f47252a1a45); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal578dbf52e12dc6d3ec213f47252a1a45)): ?>
<?php $component = $__componentOriginal578dbf52e12dc6d3ec213f47252a1a45; ?>
<?php unset($__componentOriginal578dbf52e12dc6d3ec213f47252a1a45); ?>
<?php endif; ?>

            <section class="mt-6 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h3 class="text-lg font-semibold text-white">All Winning Candidates</h3>
                <p class="mt-1 text-sm text-slate-400">Automatically grouped by event type.</p>

                <div id="results-winners-grid" class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <?php $__empty_1 = true; $__currentLoopData = $winnersPrimary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $winner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <article class="rs-winner-card rounded-xl border border-violet-500/15 bg-slate-950/50 p-4">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-violet-300"><?php echo e($winner['label']); ?></p>
                            <p class="mt-2 text-lg font-bold text-white"><?php echo e($winner['name']); ?></p>
                            <?php if(! empty($winner['party']) && $winner['party'] !== '—'): ?>
                                <p class="mt-1 text-xs text-slate-400"><?php echo e($winner['party']); ?></p>
                            <?php endif; ?>
                            <p class="mt-3 text-sm text-slate-300"><?php echo e(number_format($winner['votes'] ?? 0)); ?> votes · <?php echo e(number_format($winner['percent'] ?? 0, 1)); ?>%</p>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-slate-400 sm:col-span-2 lg:col-span-3">No winners recorded yet.</p>
                    <?php endif; ?>
                </div>

                <?php if($winnersTopTen->isNotEmpty()): ?>
                    <div class="mt-6">
                        <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Top 10 Contestants</h4>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                            <?php $__currentLoopData = $winnersTopTen; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $winner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="rounded-lg border border-slate-800 bg-slate-950/40 px-3 py-2 text-sm">
                                    <p class="text-[10px] text-violet-300"><?php echo e($winner['label']); ?></p>
                                    <p class="font-semibold text-white"><?php echo e($winner['name']); ?></p>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </section>

            <section class="mt-6 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-white">Candidate Rankings</h3>
                        <p class="mt-1 text-sm text-slate-400">Search, sort, and filter contestant standings.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <input type="search" id="results-table-search" placeholder="Search…" class="rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm text-white placeholder:text-slate-500 focus:border-violet-500 focus:outline-none">
                        <select id="results-table-filter" class="rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm text-white focus:border-violet-500 focus:outline-none">
                            <option value="">All statuses</option>
                            <option value="Winner">Winners</option>
                            <option value="Trailing">Trailing</option>
                            <option value="Finalist">Finalists</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-800">
                    <table class="min-w-full text-sm" id="results-rankings-table">
                        <thead>
                            <tr class="border-b border-slate-800 text-left text-slate-400">
                                <th class="cursor-pointer px-4 py-3 font-medium" data-sort="rank">Rank</th>
                                <th class="cursor-pointer px-4 py-3 font-medium" data-sort="name">Contestant / Candidate</th>
                                <th class="cursor-pointer px-4 py-3 font-medium" data-sort="position">Position</th>
                                <th class="cursor-pointer px-4 py-3 font-medium" data-sort="party">Party</th>
                                <th class="cursor-pointer px-4 py-3 font-medium" data-sort="votes">Votes</th>
                                <th class="cursor-pointer px-4 py-3 font-medium" data-sort="percent">Percentage</th>
                                <th class="cursor-pointer px-4 py-3 font-medium" data-sort="status">Status</th>
                            </tr>
                        </thead>
                        <tbody id="results-rankings-body">
                            <?php $__currentLoopData = $detail['rankings'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="border-b border-slate-800/80 text-slate-200" data-row='<?php echo json_encode($row, 15, 512) ?>'>
                                    <td class="px-4 py-3"><?php echo e($row['rank']); ?></td>
                                    <td class="px-4 py-3 font-medium text-white"><?php echo e($row['name']); ?></td>
                                    <td class="px-4 py-3"><?php echo e($row['position']); ?></td>
                                    <td class="px-4 py-3"><?php echo e($row['party']); ?></td>
                                    <td class="px-4 py-3"><?php echo e(number_format($row['votes'])); ?></td>
                                    <td class="px-4 py-3"><?php echo e(number_format($row['percent'], 1)); ?>%</td>
                                    <td class="px-4 py-3">
                                        <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                            'rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase',
                                            'bg-emerald-500/15 text-emerald-300' => ($row['status'] ?? '') === 'Winner',
                                            'bg-violet-500/15 text-violet-300' => in_array($row['status'] ?? '', ['Trailing', 'Finalist'], true),
                                            'bg-slate-700/50 text-slate-400' => ($row['status'] ?? '') === 'No votes',
                                        ]); ?>"><?php echo e($row['status']); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 flex items-center justify-between text-xs text-slate-500">
                    <p id="results-table-meta">Showing all rows</p>
                    <div class="flex gap-2">
                        <button type="button" id="results-table-prev" class="rounded-lg border border-slate-700 px-3 py-1.5 disabled:opacity-40">Prev</button>
                        <button type="button" id="results-table-next" class="rounded-lg border border-slate-700 px-3 py-1.5 disabled:opacity-40">Next</button>
                    </div>
                </div>
            </section>

            <?php if($partyPerformance->isNotEmpty()): ?>
                <section class="mt-6 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                    <h3 class="text-lg font-semibold text-white">Party Performance</h3>
                    <p class="mt-1 text-sm text-slate-400">Partylist comparison — votes, share, and seats won.</p>
                    <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <?php $__currentLoopData = $partyPerformance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $party): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <article class="rounded-xl border border-violet-500/15 bg-slate-950/50 p-4">
                                <p class="text-sm font-semibold text-white"><?php echo e($party['party']); ?></p>
                                <dl class="mt-3 grid grid-cols-3 gap-2 text-xs">
                                    <div>
                                        <dt class="text-slate-500">Votes</dt>
                                        <dd class="mt-0.5 font-bold text-white"><?php echo e(number_format($party['total_votes'])); ?></dd>
                                    </div>
                                    <div>
                                        <dt class="text-slate-500">Share</dt>
                                        <dd class="mt-0.5 font-bold text-emerald-300"><?php echo e(number_format($party['percent'], 1)); ?>%</dd>
                                    </div>
                                    <div>
                                        <dt class="text-slate-500">Seats</dt>
                                        <dd class="mt-0.5 font-bold text-violet-300"><?php echo e(number_format($party['seats_won'])); ?></dd>
                                    </div>
                                </dl>
                            </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </section>
            <?php endif; ?>

            <section class="mt-6">
                <h3 class="mb-4 text-lg font-semibold text-white">Vote Distribution Charts</h3>
                <div class="grid gap-4 xl:grid-cols-3">
                <?php if (isset($component)) { $__componentOriginal3f4023e8ae0200a7792ee5dfef809633 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3f4023e8ae0200a7792ee5dfef809633 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-chart-panel','data' => ['title' => 'Votes per Candidate','subtitle' => 'Leading contestants by vote count','type' => 'bar','labels' => $detail['charts']['bar']['labels'] ?? [],'values' => $detail['charts']['bar']['values'] ?? [],'yMax' => $detail['charts']['bar']['yMax'] ?? 10,'yTicks' => $detail['charts']['bar']['yTicks'] ?? [0, 5, 10],'accent' => '#818cf8','dataResultsChart' => 'bar']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-chart-panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Votes per Candidate','subtitle' => 'Leading contestants by vote count','type' => 'bar','labels' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($detail['charts']['bar']['labels'] ?? []),'values' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($detail['charts']['bar']['values'] ?? []),'y-max' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($detail['charts']['bar']['yMax'] ?? 10),'y-ticks' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($detail['charts']['bar']['yTicks'] ?? [0, 5, 10]),'accent' => '#818cf8','data-results-chart' => 'bar']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3f4023e8ae0200a7792ee5dfef809633)): ?>
<?php $attributes = $__attributesOriginal3f4023e8ae0200a7792ee5dfef809633; ?>
<?php unset($__attributesOriginal3f4023e8ae0200a7792ee5dfef809633); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3f4023e8ae0200a7792ee5dfef809633)): ?>
<?php $component = $__componentOriginal3f4023e8ae0200a7792ee5dfef809633; ?>
<?php unset($__componentOriginal3f4023e8ae0200a7792ee5dfef809633); ?>
<?php endif; ?>
                <div class="flex h-full flex-col rounded-2xl border border-violet-500/15 bg-slate-900/80 p-5" data-results-chart="pie">
                    <h3 class="text-base font-semibold text-white">Vote Share</h3>
                    <p class="mt-0.5 text-xs text-slate-400">Percentage distribution</p>
                    <div class="mt-4 flex flex-1 items-center justify-center">
                        <svg viewBox="0 0 160 160" class="h-44 w-44" data-results-pie-canvas aria-hidden="true"></svg>
                    </div>
                </div>
                <div class="flex h-full flex-col rounded-2xl border border-violet-500/15 bg-slate-900/80 p-5" data-results-chart="doughnut">
                    <h3 class="text-base font-semibold text-white">Winner Distribution</h3>
                    <p class="mt-0.5 text-xs text-slate-400">Share of total votes</p>
                    <div class="mt-4 flex flex-1 items-center justify-center">
                        <svg viewBox="0 0 160 160" class="h-44 w-44" data-results-doughnut-canvas aria-hidden="true"></svg>
                    </div>
                </div>
                </div>
            </section>

            <?php if(($detail['type'] ?? '') === 'election'): ?>
                <?php
                    $integrity = $detail['integrity'] ?? [];
                    $integrityValid = (bool) ($integrity['valid'] ?? false);
                    $integrityHasHash = (bool) ($integrity['has_hash'] ?? false);
                ?>
                <section class="mt-6 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-white">Vote Integrity</h3>
                            <p class="mt-1 text-sm text-slate-400">Cryptographic fingerprint of all vote records for this election.</p>
                        </div>
                        <?php if($detail['verify_integrity_url'] ?? null): ?>
                            <form method="POST" action="<?php echo e($detail['verify_integrity_url']); ?>" class="inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="rs-export-btn">Verify Integrity</button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <?php if(session('success') || session('error') || session('warning')): ?>
                        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'mt-4 rounded-xl border px-4 py-3 text-sm',
                            'border-emerald-500/20 bg-emerald-500/10 text-emerald-200' => session('success'),
                            'border-rose-500/20 bg-rose-500/10 text-rose-200' => session('error'),
                            'border-amber-500/20 bg-amber-500/10 text-amber-200' => session('warning'),
                        ]); ?>">
                            <?php echo e(session('success') ?? session('error') ?? session('warning')); ?>

                        </div>
                    <?php endif; ?>

                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        <?php if($integrityHasHash && $integrityValid): ?>
                            <span class="inline-flex items-center rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-300">Verified</span>
                        <?php elseif($integrityHasHash): ?>
                            <span class="inline-flex items-center rounded-full border border-rose-500/30 bg-rose-500/10 px-3 py-1 text-xs font-semibold text-rose-300">Mismatch</span>
                        <?php else: ?>
                            <span class="inline-flex items-center rounded-full border border-slate-600 bg-slate-800 px-3 py-1 text-xs font-semibold text-slate-300">No hash recorded</span>
                        <?php endif; ?>
                        <p class="text-sm text-slate-400"><?php echo e($integrity['message'] ?? ''); ?></p>
                    </div>

                    <dl class="mt-5 space-y-3 rounded-xl border border-slate-800 bg-slate-950/40 p-4 text-sm">
                        <div>
                            <dt class="text-slate-500">Stored hash</dt>
                            <dd class="mt-1 break-all font-mono text-xs text-slate-300"><?php echo e($integrity['stored_hash'] ?? '—'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Computed hash</dt>
                            <dd class="mt-1 break-all font-mono text-xs text-slate-300"><?php echo e($integrity['computed_hash'] ?? '—'); ?></dd>
                        </div>
                    </dl>
                </section>
            <?php endif; ?>

            <?php if($turnoutSections->isNotEmpty()): ?>
                <section class="mt-6 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-white">Turnout Statistics</h3>
                            <p class="mt-1 text-sm text-slate-400">Participation by grade level and section.</p>
                        </div>
                        <?php if($detail['turnout_export_url'] ?? null): ?>
                            <a href="<?php echo e($detail['turnout_export_url']); ?>" class="rs-export-btn">Export Turnout CSV</a>
                        <?php endif; ?>
                    </div>
                    <div class="mt-5 overflow-x-auto rounded-xl border border-slate-800">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-800 text-left text-slate-400">
                                    <th class="px-4 py-3 font-medium">Grade</th>
                                    <th class="px-4 py-3 font-medium">Section</th>
                                    <th class="px-4 py-3 font-medium">Registered</th>
                                    <th class="px-4 py-3 font-medium">Voted</th>
                                    <th class="px-4 py-3 font-medium">Turnout</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/80">
                                <?php $__currentLoopData = $turnoutSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="text-slate-200">
                                        <td class="px-4 py-3"><?php echo e($row['grade']); ?></td>
                                        <td class="px-4 py-3"><?php echo e($row['section']); ?></td>
                                        <td class="px-4 py-3"><?php echo e(number_format($row['registered'])); ?></td>
                                        <td class="px-4 py-3"><?php echo e(number_format($row['voted'])); ?></td>
                                        <td class="px-4 py-3 font-medium text-emerald-300"><?php echo e(number_format($row['turnout_percent'], 1)); ?>%</td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            <?php endif; ?>

            <section class="mt-6 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h3 class="text-lg font-semibold text-white">Recent Activity</h3>
                <p class="mt-1 text-sm text-slate-400">Event timeline from voting lifecycle.</p>
                <div id="results-activity" class="mt-5 space-y-4 border-l border-violet-500/20 pl-4">
                    <?php $__empty_1 = true; $__currentLoopData = $detail['activity'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="relative pl-4">
                            <span class="absolute -left-[1.34rem] top-1.5 h-2.5 w-2.5 rounded-full bg-violet-400"></span>
                            <p class="text-sm font-semibold text-white"><?php echo e($item['label']); ?></p>
                            <p class="text-xs text-slate-400"><?php echo e($item['display'] ?? '—'); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-slate-400">No timeline events recorded yet.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal57da683fe32826f08aa9f05c3342a7e2)): ?>
<?php $attributes = $__attributesOriginal57da683fe32826f08aa9f05c3342a7e2; ?>
<?php unset($__attributesOriginal57da683fe32826f08aa9f05c3342a7e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal57da683fe32826f08aa9f05c3342a7e2)): ?>
<?php $component = $__componentOriginal57da683fe32826f08aa9f05c3342a7e2; ?>
<?php unset($__componentOriginal57da683fe32826f08aa9f05c3342a7e2); ?>
<?php endif; ?>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/admin-live-voting.css', 'resources/css/admin-results.css', 'resources/js/admin-results.js']); ?>
    <script type="application/json" id="results-initial-payload"><?php echo json_encode($detail, 15, 512) ?></script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/results/show.blade.php ENDPATH**/ ?>
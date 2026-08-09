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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => 'Reports & Analytics','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Reports & Analytics','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <div
            id="admin-analytics-live"
            data-live-url="<?php echo e(route('admin.analytics.live')); ?>"
            class="hidden"
            aria-hidden="true"
        ></div>

        <?php echo $__env->make('admin.partials.page-header', [
            'title' => 'Reports & Analytics',
            'description' => 'Voting turnout, campaign engagement, event attendance, and fundraising performance.',
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="mb-4 flex items-center justify-end gap-2">
            <span class="relative flex h-2 w-2">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
            </span>
            <p id="analytics-live-updated" class="text-[11px] font-medium text-slate-500">Live · syncing…</p>
        </div>

        <div class="grid gap-4 xl:grid-cols-2">
            <?php if (isset($component)) { $__componentOriginal3f4023e8ae0200a7792ee5dfef809633 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3f4023e8ae0200a7792ee5dfef809633 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-chart-panel','data' => ['title' => 'Participation Growth (Events/Voting)','subtitle' => 'Monthly turnout and event participation — Jan to Jun','type' => 'line','liveKey' => 'participation','labels' => $report['participation']['labels'],'values' => $report['participation']['values'],'yMax' => $report['participation']['yMax'],'yTicks' => $report['participation']['yTicks'],'valueSuffix' => $report['participation']['valueSuffix'],'accent' => '#34d399']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-chart-panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Participation Growth (Events/Voting)','subtitle' => 'Monthly turnout and event participation — Jan to Jun','type' => 'line','live-key' => 'participation','labels' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($report['participation']['labels']),'values' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($report['participation']['values']),'y-max' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($report['participation']['yMax']),'y-ticks' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($report['participation']['yTicks']),'value-suffix' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($report['participation']['valueSuffix']),'accent' => '#34d399']); ?>
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

            <?php if (isset($component)) { $__componentOriginal3f4023e8ae0200a7792ee5dfef809633 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3f4023e8ae0200a7792ee5dfef809633 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-chart-panel','data' => ['title' => 'Donation/Fundraising History','subtitle' => 'Monthly donation totals — Jan to Dec','type' => 'bar','liveKey' => 'fundraising','labels' => $report['fundraising']['labels'],'values' => $report['fundraising']['values'],'yMax' => $report['fundraising']['yMax'],'yTicks' => $report['fundraising']['yTicks'],'valuePrefix' => $report['fundraising']['valuePrefix'],'accent' => '#818cf8']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-chart-panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Donation/Fundraising History','subtitle' => 'Monthly donation totals — Jan to Dec','type' => 'bar','live-key' => 'fundraising','labels' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($report['fundraising']['labels']),'values' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($report['fundraising']['values']),'y-max' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($report['fundraising']['yMax']),'y-ticks' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($report['fundraising']['yTicks']),'value-prefix' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($report['fundraising']['valuePrefix']),'accent' => '#818cf8']); ?>
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

            <?php if (isset($component)) { $__componentOriginal3f4023e8ae0200a7792ee5dfef809633 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3f4023e8ae0200a7792ee5dfef809633 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-chart-panel','data' => ['title' => 'Voting Turnout Trends','subtitle' => 'Turnout by grade and section','type' => 'horizontal-bar','liveKey' => 'turnout','labels' => $report['turnout']['labels'],'values' => $report['turnout']['values'],'yMax' => $report['turnout']['yMax'],'yTicks' => $report['turnout']['yTicks'],'valueSuffix' => $report['turnout']['valueSuffix'],'accent' => '#a78bfa']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-chart-panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Voting Turnout Trends','subtitle' => 'Turnout by grade and section','type' => 'horizontal-bar','live-key' => 'turnout','labels' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($report['turnout']['labels']),'values' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($report['turnout']['values']),'y-max' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($report['turnout']['yMax']),'y-ticks' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($report['turnout']['yTicks']),'value-suffix' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($report['turnout']['valueSuffix']),'accent' => '#a78bfa']); ?>
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

            <?php if (isset($component)) { $__componentOriginal3f4023e8ae0200a7792ee5dfef809633 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3f4023e8ae0200a7792ee5dfef809633 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-chart-panel','data' => ['title' => 'Campaign Engagement Stats','subtitle' => 'Engagement score by partylist campaign','type' => 'bar','liveKey' => 'campaigns','labels' => $report['campaigns']['labels'],'values' => $report['campaigns']['values'],'yMax' => $report['campaigns']['yMax'],'yTicks' => $report['campaigns']['yTicks'],'valueSuffix' => $report['campaigns']['valueSuffix'],'accent' => '#f472b6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-chart-panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Campaign Engagement Stats','subtitle' => 'Engagement score by partylist campaign','type' => 'bar','live-key' => 'campaigns','labels' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($report['campaigns']['labels']),'values' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($report['campaigns']['values']),'y-max' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($report['campaigns']['yMax']),'y-ticks' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($report['campaigns']['yTicks']),'value-suffix' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($report['campaigns']['valueSuffix']),'accent' => '#f472b6']); ?>
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
        </div>

        <div class="mt-4 grid gap-4 xl:grid-cols-12">
            <div class="xl:col-span-8">
                <?php if (isset($component)) { $__componentOriginal3f4023e8ae0200a7792ee5dfef809633 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3f4023e8ae0200a7792ee5dfef809633 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-chart-panel','data' => ['title' => 'Event Attendance History','subtitle' => 'School events scheduled and talent event participation by month','type' => 'bar','liveKey' => 'events','labels' => $report['events']['labels'],'values' => $report['events']['values'],'yMax' => $report['events']['yMax'],'yTicks' => $report['events']['yTicks'],'accent' => '#38bdf8']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-chart-panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Event Attendance History','subtitle' => 'School events scheduled and talent event participation by month','type' => 'bar','live-key' => 'events','labels' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($report['events']['labels']),'values' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($report['events']['values']),'y-max' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($report['events']['yMax']),'y-ticks' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($report['events']['yTicks']),'accent' => '#38bdf8']); ?>
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
            </div>

            <section class="xl:col-span-4 rounded-2xl border border-violet-500/15 bg-slate-900/80 p-5 shadow-sm shadow-black/20">
                <h3 class="text-base font-semibold text-white">Turnout Breakdown</h3>
                <p class="mt-0.5 text-xs text-slate-400">Eligible vs voted students by section</p>

                <div id="analytics-turnout-breakdown" class="mt-4 max-h-72 space-y-3 overflow-y-auto">
                    <?php $__empty_1 = true; $__currentLoopData = $report['turnoutSections']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3">
                            <div class="flex items-center justify-between gap-2">
                                <p class="truncate text-sm font-medium text-white"><?php echo e($section['label']); ?></p>
                                <span class="shrink-0 text-sm font-semibold text-violet-300"><?php echo e($section['turnout']); ?>%</span>
                            </div>
                            <p class="mt-1 text-xs text-slate-500"><?php echo e($section['voted']); ?> voted · <?php echo e($section['eligible']); ?> eligible</p>
                            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-800">
                                <div class="h-full rounded-full bg-violet-500" style="width: <?php echo e(min(100, $section['turnout'])); ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-slate-400">No turnout data for your assigned election yet.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <section class="mt-4 rounded-2xl border border-violet-500/15 bg-slate-900/80 p-5">
            <h3 class="text-base font-semibold text-white">Campaign Performance</h3>
            <p class="mt-0.5 text-xs text-slate-400">Vote share and seats won, derived from candidate votes</p>

            <div class="mt-4 space-y-3">
                <?php $__empty_1 = true; $__currentLoopData = $report['campaignPerformance'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campaign): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <?php if(! empty($campaign['color'])): ?>
                                    <span class="inline-block h-3 w-3 rounded-full" style="background: <?php echo e($campaign['color']); ?>"></span>
                                <?php endif; ?>
                                <p class="text-sm font-medium text-white"><?php echo e($campaign['name']); ?></p>
                                <?php if(! empty($campaign['acronym'])): ?>
                                    <span class="text-xs text-violet-300"><?php echo e($campaign['acronym']); ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="shrink-0 text-sm font-semibold text-violet-300"><?php echo e($campaign['vote_share']); ?>%</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">
                            <?php echo e($campaign['total_votes']); ?> votes · <?php echo e($campaign['total_candidates']); ?> candidate(s) · <?php echo e($campaign['winning_candidates']); ?> winning
                        </p>
                        <?php if(! empty($campaign['winning_positions'])): ?>
                            <p class="mt-1 text-xs text-emerald-300">Won: <?php echo e(implode(', ', $campaign['winning_positions'])); ?></p>
                        <?php endif; ?>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-800">
                            <div class="h-full rounded-full bg-violet-500" style="width: <?php echo e(min(100, $campaign['vote_share'])); ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-slate-400">No campaign vote data for your assigned election yet.</p>
                <?php endif; ?>
            </div>
        </section>

        <section id="talent-competitions" class="mt-4 scroll-mt-24 rounded-2xl border border-violet-500/15 bg-slate-900/80 p-5">
            <h3 class="text-base font-semibold text-white">Talent Competition Insights</h3>
            <p class="mt-0.5 text-xs text-slate-400">Category, contestants, votes, voting method, and winner settings</p>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-2">Event</th>
                            <th class="px-3 py-2">Category</th>
                            <th class="px-3 py-2">Contestants</th>
                            <th class="px-3 py-2">Total Votes</th>
                            <th class="px-3 py-2">Voting Method</th>
                            <th class="px-3 py-2">Winners</th>
                            <th class="px-3 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        <?php $__empty_1 = true; $__currentLoopData = $report['talentCompetitions'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="text-slate-300">
                                <td class="px-3 py-3 font-medium text-white"><?php echo e($event['name']); ?></td>
                                <td class="px-3 py-3"><?php echo e($event['talent_category']); ?></td>
                                <td class="px-3 py-3"><?php echo e($event['contestants']); ?></td>
                                <td class="px-3 py-3"><?php echo e(number_format($event['total_votes'])); ?></td>
                                <td class="px-3 py-3"><?php echo e($event['voting_method']); ?></td>
                                <td class="px-3 py-3"><?php echo e($event['winner_count']); ?></td>
                                <td class="px-3 py-3"><?php echo e($event['display_status']); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="px-3 py-6 text-center text-slate-400">No talent competition data in your scope yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mt-4 rounded-2xl border border-violet-500/15 bg-slate-900/80 p-5">
            <h3 class="text-base font-semibold text-white">Fundraising Performance</h3>
            <p class="mt-0.5 text-xs text-slate-400">Year-to-date donation summary</p>

            <?php
                $ytdTotal = array_sum($report['fundraising']['values']);
                $fundraisingValues = $report['fundraising']['values'];
                $bestMonthIndex = count($fundraisingValues) > 0
                    ? (int) array_search(max($fundraisingValues), $fundraisingValues, true)
                    : 0;
                $bestMonth = $report['fundraising']['labels'][$bestMonthIndex] ?? '—';
                $bestAmount = $fundraisingValues[$bestMonthIndex] ?? 0;
                $avgMonth = count($fundraisingValues) > 0
                    ? $ytdTotal / count($fundraisingValues)
                    : 0;
            ?>

            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">YTD Total</p>
                    <p data-live-fundraising-ytd class="mt-1 text-2xl font-bold text-white">₱<?php echo e(number_format($ytdTotal, 2)); ?></p>
                </div>
                <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Best Month</p>
                    <p data-live-fundraising-best-month class="mt-1 text-2xl font-bold text-white"><?php echo e($bestMonth); ?></p>
                    <p data-live-fundraising-best-amount class="text-xs text-slate-500">₱<?php echo e(number_format($bestAmount, 2)); ?></p>
                </div>
                <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Monthly Average</p>
                    <p data-live-fundraising-avg class="mt-1 text-2xl font-bold text-white">₱<?php echo e(number_format($avgMonth, 2)); ?></p>
                </div>
            </div>
        </section>
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

    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/admin-analytics-live.js']); ?>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/analytics/index.blade.php ENDPATH**/ ?>
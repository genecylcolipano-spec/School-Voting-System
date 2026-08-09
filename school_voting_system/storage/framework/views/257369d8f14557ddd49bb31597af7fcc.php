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
    <?php if (isset($component)) { $__componentOriginal81e0f81d610b853005bdb68b3f312adb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81e0f81d610b853005bdb68b3f312adb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.student-portal','data' => ['title' => 'Statistics','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('student-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Statistics','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        
        <section class="overflow-hidden rounded-2xl border border-cyan-500/20 bg-gradient-to-br from-sky-900/60 via-slate-900 to-violet-900/20 p-6 sm:p-8">
            <span class="inline-block rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-cyan-200">Analytics</span>
            <h2 class="mt-4 text-2xl font-bold text-white sm:text-3xl">Your Statistics</h2>
            <p class="mt-2 max-w-2xl text-sm text-slate-300 sm:text-base">
                Track your participation across elections, school events, talent competitions, and fundraising campaigns.
            </p>
        </section>

        
        <section>
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-400">Participation Overview</h3>
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <?php $__currentLoopData = [
                    ['label' => 'Votes Cast', 'value' => $overview['votes_cast']],
                    ['label' => 'Elections Joined', 'value' => $overview['elections_joined']],
                    ['label' => 'Competitions Joined', 'value' => $overview['competitions_joined']],
                    ['label' => 'Fundraisers Supported', 'value' => $overview['fundraisers_supported']],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-4 sm:p-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400"><?php echo e($stat['label']); ?></p>
                        <p class="mt-1 text-2xl font-bold text-white"><?php echo e($stat['value']); ?></p>
                        <p class="mt-1 text-xs text-slate-500">Lifetime</p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>

        
        <section class="grid gap-4 lg:grid-cols-3">
            <div class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-5 lg:col-span-2">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Student Activity Summary</h3>
                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Recent Vote</p>
                        <p class="mt-1 text-sm font-semibold text-white"><?php echo e($activitySummary['recent_vote'] ?? 'No voting history yet.'); ?></p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Last Event Joined</p>
                        <p class="mt-1 text-sm font-semibold text-white">Event registration is not available yet.</p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Last Competition Joined</p>
                        <p class="mt-1 text-sm font-semibold text-white"><?php echo e($activitySummary['last_competition'] ?? 'No competitions joined.'); ?></p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Last Donation</p>
                        <p class="mt-1 text-sm font-semibold text-white"><?php echo e($activitySummary['last_donation'] ?? 'No donations yet.'); ?></p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Registered Devices</p>
                        <p class="mt-1 text-sm font-semibold text-white">
                            <?php echo e($activitySummary['passkeys']); ?> <?php echo e(\Illuminate\Support\Str::plural('Passkey', $activitySummary['passkeys'])); ?>

                        </p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Member Since</p>
                        <p class="mt-1 text-sm font-semibold text-white"><?php echo e(optional($activitySummary['member_since'])->format('M d, Y') ?? '—'); ?></p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-5">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Participation Analytics</h3>

                <?php if($votingAnalytics['has_history'] || $votingAnalytics['has_open_elections']): ?>
                    <div class="mt-6 flex flex-col items-center">
                        <div class="relative flex h-32 w-32 items-center justify-center">
                            <svg class="h-32 w-32 -rotate-90" viewBox="0 0 120 120" aria-hidden="true">
                                <circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="10" class="text-slate-800"/>
                                <circle
                                    cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="10"
                                    stroke-linecap="round"
                                    class="text-cyan-400"
                                    stroke-dasharray="<?php echo e(2 * 3.14159 * 52); ?>"
                                    stroke-dashoffset="<?php echo e(2 * 3.14159 * 52 * (1 - $votingAnalytics['lifetime_percent'] / 100)); ?>"
                                />
                            </svg>
                            <span class="absolute text-2xl font-bold text-white"><?php echo e($votingAnalytics['lifetime_percent']); ?>%</span>
                        </div>
                        <p class="mt-4 text-center text-sm font-semibold text-cyan-300">
                            <?php echo e($votingAnalytics['elections_joined']); ?> / <?php echo e(max($votingAnalytics['eligible_elections'], 0)); ?> Elections Joined
                        </p>
                    </div>

                    <dl class="mt-6 space-y-3 border-t border-slate-800 pt-4 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-400">Completed Votes</dt>
                            <dd class="font-semibold text-white"><?php echo e($votingAnalytics['completed_votes']); ?></dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-400">Eligible Elections</dt>
                            <dd class="font-semibold text-white"><?php echo e($votingAnalytics['eligible_elections']); ?></dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-400">Last Vote Date</dt>
                            <dd class="font-semibold text-white"><?php echo e(optional($votingAnalytics['last_vote_at'])->format('M d, Y') ?? '—'); ?></dd>
                        </div>
                        <?php if($votingAnalytics['has_open_elections']): ?>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-slate-400">Open categories voted</dt>
                                <dd class="font-semibold text-white"><?php echo e($votingAnalytics['votes_in_open']); ?>/<?php echo e($votingAnalytics['open_categories']); ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                <?php else: ?>
                    <div class="mt-8 rounded-xl border border-dashed border-slate-700 px-4 py-10 text-center">
                        <p class="text-sm font-medium text-slate-300">No voting history yet.</p>
                        <p class="mt-2 text-xs text-slate-500">Your participation rate will appear here after you cast your first vote.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        
        <section class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Recent Activity</h3>
                <ul class="mt-4 divide-y divide-slate-800">
                    <?php $__empty_1 = true; $__currentLoopData = $recentActivity; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <li class="flex items-start gap-3 py-3 first:pt-0 last:pb-0">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-300" aria-hidden="true">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-slate-200"><?php echo e($activity['message']); ?></p>
                                <p class="mt-0.5 text-xs text-slate-500"><?php echo e($activity['time']); ?></p>
                            </div>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <li class="py-8 text-center text-sm text-slate-500">No recent activity yet.</li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Donation Summary</h3>

                <?php if($donationSummary['donations_made'] > 0): ?>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Total Donations</p>
                            <p class="mt-1 text-lg font-bold text-white">₱<?php echo e(number_format($donationSummary['total_donated'], 2)); ?></p>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Campaigns Supported</p>
                            <p class="mt-1 text-lg font-bold text-white"><?php echo e($donationSummary['campaigns_supported']); ?></p>
                        </div>
                    </div>

                    <div class="mt-4 rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Latest Donation</p>
                        <p class="mt-1 text-sm font-semibold text-white"><?php echo e($donationSummary['latest_title'] ?? '—'); ?></p>
                        <p class="mt-1 text-xs text-slate-400">
                            <?php if($donationSummary['latest_amount'] !== null): ?>
                                ₱<?php echo e(number_format($donationSummary['latest_amount'], 2)); ?>

                                ·
                            <?php endif; ?>
                            <?php echo e(optional($donationSummary['latest_at'])->format('M d, Y') ?? '—'); ?>

                        </p>
                    </div>

                    <div class="mt-4">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Recent Donation History</p>
                        <ul class="space-y-2">
                            <?php $__currentLoopData = $donationSummary['history']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $donation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="flex items-center justify-between gap-3 rounded-xl border border-slate-800 bg-slate-950/40 px-3 py-2.5">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-white"><?php echo e($donation->fundraiser?->title ?? 'Fundraiser'); ?></p>
                                        <p class="text-xs text-slate-500"><?php echo e(optional($donation->donated_at)->format('M d, Y')); ?></p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <p class="text-sm font-semibold text-emerald-300">₱<?php echo e(number_format((float) $donation->amount, 2)); ?></p>
                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Completed</p>
                                    </div>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <div class="mt-6 rounded-xl border border-dashed border-slate-700 px-4 py-10 text-center">
                        <p class="text-sm font-medium text-slate-300">No donations yet.</p>
                        <p class="mt-2 text-xs text-slate-500">Your fundraising support history will appear here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        
        <section class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Student Engagement</h3>
                <div class="mt-5 space-y-5">
                    <?php $__currentLoopData = [
                        ['label' => 'Voting Participation', 'value' => $engagement['voting'], 'available' => true],
                        ['label' => 'Event Participation', 'value' => $engagement['events'], 'available' => false],
                        ['label' => 'Competition Participation', 'value' => $engagement['competitions'], 'available' => true],
                        ['label' => 'Fundraising Participation', 'value' => $engagement['fundraising'], 'available' => true],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bar): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div>
                            <div class="mb-1.5 flex items-center justify-between gap-3 text-sm">
                                <span class="text-slate-300"><?php echo e($bar['label']); ?></span>
                                <?php if($bar['available']): ?>
                                    <span class="font-semibold text-white"><?php echo e($bar['value']); ?>%</span>
                                <?php else: ?>
                                    <span class="text-xs text-slate-500">Not available yet</span>
                                <?php endif; ?>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-800">
                                <?php if($bar['available']): ?>
                                    <div
                                        class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-sky-400 transition-all"
                                        style="width: <?php echo e(max(0, min(100, (int) $bar['value']))); ?>%"
                                    ></div>
                                <?php else: ?>
                                    <div class="h-full w-0 rounded-full bg-slate-700"></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Achievements</h3>
                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Most Active Semester</p>
                        <p class="mt-1 text-sm font-semibold text-white"><?php echo e($achievements['most_active_semester'] ?? 'No activity yet'); ?></p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Total Activities</p>
                        <p class="mt-1 text-2xl font-bold text-white"><?php echo e($achievements['total_activities']); ?></p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Certificates Earned</p>
                        <p class="mt-1 text-sm font-semibold text-white">
                            <?php if($achievements['certificates_earned'] > 0): ?>
                                <?php echo e($achievements['certificates_earned']); ?>

                            <?php else: ?>
                                Not available yet
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Current Participation Level</p>
                        <p class="mt-1 text-sm font-semibold text-cyan-300"><?php echo e($achievements['participation_level']); ?></p>
                    </div>
                </div>
            </div>
        </section>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal81e0f81d610b853005bdb68b3f312adb)): ?>
<?php $attributes = $__attributesOriginal81e0f81d610b853005bdb68b3f312adb; ?>
<?php unset($__attributesOriginal81e0f81d610b853005bdb68b3f312adb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal81e0f81d610b853005bdb68b3f312adb)): ?>
<?php $component = $__componentOriginal81e0f81d610b853005bdb68b3f312adb; ?>
<?php unset($__componentOriginal81e0f81d610b853005bdb68b3f312adb); ?>
<?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/student/statistics/index.blade.php ENDPATH**/ ?>
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
    <div
        x-data="{
            sidebarOpen: false,
            collapsed: sessionStorage.getItem('studentSidebarCollapsed') === 'true',
            toggleSidebar() {
                if (window.innerWidth >= 1024) {
                    this.collapsed = !this.collapsed;
                    sessionStorage.setItem('studentSidebarCollapsed', this.collapsed ? 'true' : 'false');
                } else {
                    this.sidebarOpen = !this.sidebarOpen;
                }
            }
        }"
        @keydown.escape.window="sidebarOpen = false"
        class="h-screen overflow-hidden bg-slate-950 text-slate-100"
    >
        <div
            x-show="sidebarOpen"
            x-transition.opacity
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-black/60 lg:hidden"
            style="display: none;"
        ></div>

        <div class="flex h-full min-h-0 overflow-hidden">
            <aside
                :class="{
                    'translate-x-0': sidebarOpen,
                    '-translate-x-full': !sidebarOpen,
                    'lg:translate-x-0': true,
                    'lg:w-20': collapsed,
                    'lg:w-72': !collapsed
                }"
                class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-violet-500/15 bg-slate-950 transition-all duration-300"
            >
                <div class="border-b border-violet-500/10 px-5 py-5">
                    <?php if (isset($component)) { $__componentOriginal3a1587ba40472411c16c01b94592189a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3a1587ba40472411c16c01b94592189a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.school-brand','data' => ['subtitle' => 'Student Portal','collapsedAware' => true,'gradient' => 'from-violet-600 to-indigo-500','iconClass' => 'text-white','shadowClass' => 'shadow-violet-900/40']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('school-brand'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['subtitle' => 'Student Portal','collapsed-aware' => true,'gradient' => 'from-violet-600 to-indigo-500','icon-class' => 'text-white','shadow-class' => 'shadow-violet-900/40']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3a1587ba40472411c16c01b94592189a)): ?>
<?php $attributes = $__attributesOriginal3a1587ba40472411c16c01b94592189a; ?>
<?php unset($__attributesOriginal3a1587ba40472411c16c01b94592189a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3a1587ba40472411c16c01b94592189a)): ?>
<?php $component = $__componentOriginal3a1587ba40472411c16c01b94592189a; ?>
<?php unset($__componentOriginal3a1587ba40472411c16c01b94592189a); ?>
<?php endif; ?>
                </div>

                <?php echo $__env->make('student.partials.sidebar-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </aside>

            <div
                class="flex min-w-0 flex-1 flex-col overflow-y-auto"
                :class="collapsed ? 'lg:pl-20' : 'lg:pl-72'"
            >
                <header class="sticky top-0 z-30 border-b border-cyan-500/10 bg-slate-950/90 backdrop-blur-md">
                    <div class="flex flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                        <div class="flex min-w-0 items-center gap-3">
                            <button
                                type="button"
                                @click="toggleSidebar()"
                                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-cyan-500/20 bg-slate-900 text-cyan-300 hover:bg-slate-800"
                                aria-label="Toggle sidebar"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            </button>
                            <div class="min-w-0">
                                <p class="truncate text-sm text-slate-400">Welcome back, <?php echo e($user->name); ?></p>
                                <h1 class="truncate text-lg font-bold text-white sm:text-xl">Student Dashboard</h1>
                            </div>
                        </div>

                        <div class="relative flex items-center gap-2">
                            <?php if (isset($component)) { $__componentOriginal7169a5b356633be5dafc74bf7a8eb300 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7169a5b356633be5dafc74bf7a8eb300 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.notification-center','data' => ['feedUrl' => route('student.notifications.feed'),'indexUrl' => route('student.notifications.index'),'markAllUrl' => route('student.notifications.read'),'markOneUrlTemplate' => ''.e(route('student.notifications.read-one', ['notification' => '__ID__'])).'','initialCount' => $notificationsCount,'theme' => 'student']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('notification-center'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['feed-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('student.notifications.feed')),'index-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('student.notifications.index')),'mark-all-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('student.notifications.read')),'mark-one-url-template' => ''.e(route('student.notifications.read-one', ['notification' => '__ID__'])).'','initial-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount),'theme' => 'student']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7169a5b356633be5dafc74bf7a8eb300)): ?>
<?php $attributes = $__attributesOriginal7169a5b356633be5dafc74bf7a8eb300; ?>
<?php unset($__attributesOriginal7169a5b356633be5dafc74bf7a8eb300); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7169a5b356633be5dafc74bf7a8eb300)): ?>
<?php $component = $__componentOriginal7169a5b356633be5dafc74bf7a8eb300; ?>
<?php unset($__componentOriginal7169a5b356633be5dafc74bf7a8eb300); ?>
<?php endif; ?>

                            <?php echo $__env->make('student.partials.account-menu', ['user' => $user], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </div>
                    </div>
                </header>

                <main id="top" class="min-h-0 flex-1 space-y-8 px-4 py-6 sm:px-6 lg:px-8">
                    <?php if($user->passkeys_count === 0): ?>
                        <div class="max-w-xl">
                            <?php if (isset($component)) { $__componentOriginal15a615f1c082febb5f28527938415021 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal15a615f1c082febb5f28527938415021 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.passkey-register','data' => ['registerOptionsUrl' => route('register.passkey.options'),'registerVerifyUrl' => route('register.passkey.verify')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('passkey-register'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['register-options-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('register.passkey.options')),'register-verify-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('register.passkey.verify'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal15a615f1c082febb5f28527938415021)): ?>
<?php $attributes = $__attributesOriginal15a615f1c082febb5f28527938415021; ?>
<?php unset($__attributesOriginal15a615f1c082febb5f28527938415021); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal15a615f1c082febb5f28527938415021)): ?>
<?php $component = $__componentOriginal15a615f1c082febb5f28527938415021; ?>
<?php unset($__componentOriginal15a615f1c082febb5f28527938415021); ?>
<?php endif; ?>
                        </div>
                    <?php endif; ?>

                    
                    <section class="overflow-hidden rounded-2xl border border-cyan-500/20 bg-gradient-to-br from-sky-900/80 via-slate-900 to-emerald-900/30 p-6 sm:p-8">
                        <span class="inline-block rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-cyan-200">Student Portal</span>
                        <h2 class="mt-4 text-2xl font-bold text-white sm:text-3xl">Welcome back, <?php echo e($firstName); ?>!</h2>
                        <p class="mt-3 max-w-2xl text-slate-300">
                            Participate in school elections, discover campus events, support fundraising campaigns, join talent competitions, and stay updated with school announcements.
                        </p>

                        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                            <?php if($hasActiveElection): ?>
                                <a
                                    href="<?php echo e($voteNowUrl); ?>"
                                    class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-900/30 transition hover:opacity-90 focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-400 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900 sm:w-auto"
                                    aria-label="Vote now"
                                >
                                    Vote Now
                                </a>
                            <?php else: ?>
                                <button
                                    type="button"
                                    disabled
                                    aria-disabled="true"
                                    class="inline-flex min-h-11 w-full cursor-not-allowed items-center justify-center gap-2 rounded-xl border border-slate-700 bg-slate-800/60 px-5 py-2.5 text-sm font-semibold text-slate-400 sm:w-auto"
                                >
                                    Vote Now
                                </button>
                            <?php endif; ?>

                            <a
                                href="<?php echo e(route('student.events.index')); ?>"
                                class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border border-cyan-400/40 bg-transparent px-5 py-2.5 text-sm font-semibold text-cyan-100 transition hover:border-cyan-300/60 hover:bg-cyan-500/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-cyan-400 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900 sm:w-auto"
                                aria-label="View school events"
                            >
                                View School Events
                            </a>
                        </div>

                        <?php if (! ($hasActiveElection)): ?>
                            <p class="mt-3 text-sm text-slate-400">No active elections at the moment.</p>
                        <?php endif; ?>
                    </section>

                    
                    <section>
                        <div class="mb-4">
                            <h2 class="text-xl font-bold text-white">Student Overview</h2>
                            <p class="mt-1 text-sm text-slate-400">Current status of your activities and available opportunities.</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-5">
                            <?php $__currentLoopData = $activityCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $cardClasses = 'group relative flex h-full min-h-[9.5rem] flex-col rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-4 shadow-lg shadow-black/10 transition duration-300 sm:p-5 '
                                        .($card['enabled']
                                            ? 'cursor-pointer hover:-translate-y-1 hover:border-violet-400/45 hover:bg-slate-900 hover:shadow-xl hover:shadow-violet-900/25 focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-400 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950'
                                            : 'cursor-default opacity-80');
                                ?>

                                <?php if($card['enabled']): ?>
                                    <a href="<?php echo e($card['href']); ?>" class="<?php echo e($cardClasses); ?>" aria-label="<?php echo e($card['title']); ?>">
                                <?php else: ?>
                                    <div class="<?php echo e($cardClasses); ?>" aria-disabled="true">
                                <?php endif; ?>
                                    <div class="mb-3 flex items-start justify-between gap-2">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-500/15 text-violet-300 ring-1 ring-violet-500/20 <?php echo e($card['enabled'] ? 'transition group-hover:bg-violet-500/25' : ''); ?>">
                                            <?php switch($card['icon']):
                                                case ('vote'): ?>
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                                    <?php break; ?>
                                                <?php case ('events'): ?>
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    <?php break; ?>
                                                <?php case ('talent'): ?>
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                                    <?php break; ?>
                                                <?php case ('fundraising'): ?>
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    <?php break; ?>
                                                <?php default: ?>
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                                            <?php endswitch; ?>
                                        </span>
                                        <span class="text-2xl font-bold text-white"><?php echo e($card['count']); ?></span>
                                    </div>

                                    <p class="text-sm font-semibold text-white"><?php echo e($card['title']); ?></p>
                                    <p class="mt-1 text-xs text-slate-400"><?php echo e($card['status']); ?></p>

                                    <p class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                        'mt-auto pt-3 text-sm font-semibold',
                                        'text-cyan-300 transition group-hover:text-cyan-200' => $card['enabled'],
                                        'text-slate-500' => ! $card['enabled'],
                                    ]); ?>">
                                        <?php if($card['enabled']): ?>
                                            <?php echo e($card['action']); ?>

                                        <?php else: ?>
                                            Unavailable
                                        <?php endif; ?>
                                    </p>

                                <?php if($card['enabled']): ?>
                                    </a>
                                <?php else: ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </section>

                    
                    <?php echo $__env->make('dashboards._upcoming-activities', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                    
                    <section>
                        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h2 class="text-xl font-bold text-white">Latest Announcements</h2>
                                <p class="mt-1 text-sm text-slate-400">Newest updates for students and the school community.</p>
                            </div>
                            <a href="<?php echo e(route('student.announcements.index')); ?>" class="text-sm font-semibold text-cyan-300 hover:text-cyan-200">View All</a>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                            <?php $__empty_1 = true; $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <article class="flex h-full flex-col rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <?php if($item->category): ?>
                                            <span class="rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide <?php echo e($item->category->badgeClasses()); ?>"><?php echo e($item->category->label()); ?></span>
                                        <?php endif; ?>
                                        <span class="text-xs text-slate-500"><?php echo e(optional($item->published_at)->format('M d, Y')); ?></span>
                                    </div>
                                    <h3 class="mt-3 font-semibold text-white"><?php echo e($item->title); ?></h3>
                                    <p class="mt-2 line-clamp-2 flex-1 text-sm text-slate-400"><?php echo e($item->summary); ?></p>
                                    <a href="<?php echo e(route('student.announcements.show', $item)); ?>" class="mt-4 inline-flex text-sm font-semibold text-cyan-300 hover:text-cyan-200">Read more →</a>
                                </article>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <p class="text-sm text-slate-500 md:col-span-2 xl:col-span-3">No announcements right now.</p>
                            <?php endif; ?>
                        </div>
                    </section>

                    
                    <section>
                        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h2 class="text-xl font-bold text-white">Recent Notifications</h2>
                                <p class="mt-1 text-sm text-slate-400">Your latest portal alerts.</p>
                            </div>
                            <a href="<?php echo e(route('student.notifications.index')); ?>" class="text-sm font-semibold text-cyan-300 hover:text-cyan-200">View All Notifications</a>
                        </div>
                        <div class="divide-y divide-slate-800 rounded-2xl border border-cyan-500/15 bg-slate-900/70">
                            <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="flex gap-3 px-5 py-4">
                                    <span class="text-xl" aria-hidden="true"><?php echo e($note['icon']); ?></span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="text-sm font-medium text-slate-200"><?php echo e($note['title']); ?></p>
                                            <?php if($note['read']): ?>
                                                <span class="rounded-full bg-slate-800 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Read</span>
                                            <?php else: ?>
                                                <span class="rounded-full bg-cyan-500/15 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-cyan-300">Unread</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="mt-1 line-clamp-2 text-sm text-slate-400"><?php echo e($note['message']); ?></p>
                                        <p class="mt-1 text-xs text-slate-500"><?php echo e($note['time']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <p class="px-5 py-8 text-center text-sm text-slate-500">No notifications yet.</p>
                            <?php endif; ?>
                        </div>
                    </section>
                </main>
            </div>
        </div>
    </div>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/notification-center.js']); ?>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/dashboards/student.blade.php ENDPATH**/ ?>
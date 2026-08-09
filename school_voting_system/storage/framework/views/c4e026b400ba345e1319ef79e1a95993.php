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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => 'Results','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Results','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <?php echo $__env->make('admin.partials.page-header', [
            'title' => 'Results',
            'description' => 'View official results for all elections and voting-based events.',
            'showAction' => false,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php if(! $hasEvents): ?>
            <div class="rs-empty flex flex-col items-center justify-center rounded-2xl border border-dashed border-violet-500/20 bg-slate-900/50 px-6 py-16 text-center">
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-violet-500/10 text-3xl">🏆</div>
                <h2 class="text-xl font-bold text-white">No Results Available</h2>
                <p class="mt-2 max-w-md text-sm text-slate-400">Results will appear here once elections or talent competitions are set up in your scope.</p>
            </div>
        <?php else: ?>
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-slate-400"><?php echo e($events->count()); ?> voting event<?php echo e($events->count() === 1 ? '' : 's'); ?> in your scope</p>
                <?php if($filterOptions->isNotEmpty()): ?>
                    <form method="GET" action="<?php echo e(route('admin.results.index')); ?>" class="flex items-center gap-2">
                        <label for="event-filter" class="sr-only">Filter events</label>
                        <select
                            id="event-filter"
                            name="event"
                            onchange="this.form.submit()"
                            class="rounded-xl border border-slate-700 bg-slate-950/60 px-4 py-2 text-sm text-white focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500"
                        >
                            <option value="">All voting events</option>
                            <?php $__currentLoopData = $filterOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($option['key']); ?>" <?php if($selectedEvent === $option['key']): echo 'selected'; endif; ?>><?php echo e($option['label']); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php if($selectedEvent): ?>
                            <a href="<?php echo e(route('admin.results.index')); ?>" class="text-sm font-semibold text-violet-300 hover:text-violet-200">Clear</a>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
            </div>

            <?php
                $typeFilter = request('type');
                $visibleEvents = collect($events)->filter(function ($event) use ($typeFilter) {
                    if (! in_array($typeFilter, ['election', 'talent'], true)) {
                        return true;
                    }
                    $isTalent = str_contains($event['show_url'] ?? '', '/results/talent/');
                    return $typeFilter === 'talent' ? $isTalent : ! $isTalent;
                });
            ?>

            <?php if($visibleEvents->isEmpty()): ?>
                <div id="<?php echo e($typeFilter === 'talent' ? 'talent-results' : 'election-results'); ?>" class="rounded-2xl border border-violet-500/15 bg-slate-900/70 px-6 py-12 text-center text-slate-400">
                    No <?php echo e($typeFilter === 'talent' ? 'talent competition' : ($typeFilter === 'election' ? 'election' : '')); ?> results match the selected filter.
                </div>
            <?php else: ?>
                <div id="<?php echo e($typeFilter === 'talent' ? 'talent-results' : 'election-results'); ?>" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <?php $__currentLoopData = $visibleEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo $__env->make('admin.results._event-card', ['event' => $event], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
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

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/admin-live-voting.css', 'resources/css/admin-results.css']); ?>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/results/index.blade.php ENDPATH**/ ?>
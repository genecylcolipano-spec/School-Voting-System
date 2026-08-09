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
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between gap-4">
                <a href="<?php echo e(route('student.voting.index')); ?>" class="text-sm font-semibold text-cyan-300 hover:text-cyan-200">← Back to voting</a>
                <a href="<?php echo e(route('student.dashboard')); ?>" class="text-sm text-slate-300 hover:text-white">Dashboard</a>
            </div>

            <?php if(session('error')): ?>
                <div class="mb-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>

            <div class="rounded-2xl border border-amber-500/20 bg-slate-900/70 p-8 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-amber-500/10 text-amber-300">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-white"><?php echo e($election->title); ?></h1>

                <?php if($election->description): ?>
                    <p class="mx-auto mt-3 max-w-xl text-sm text-slate-400"><?php echo e($election->description); ?></p>
                <?php endif; ?>

                <p class="mx-auto mt-6 max-w-md text-base text-amber-200"><?php echo e($message); ?></p>

                <?php if($election->isBeforeVotingStart() && $election->voting_starts_at): ?>
                    <p class="mt-3 text-sm text-slate-400">
                        Opens <?php echo e($election->voting_starts_at->format('M d, Y g:i A')); ?>

                    </p>
                <?php elseif($election->isAfterVotingEnd() && $election->voting_ends_at): ?>
                    <p class="mt-3 text-sm text-slate-400">
                        Closed <?php echo e($election->voting_ends_at->format('M d, Y g:i A')); ?>

                    </p>
                <?php endif; ?>

                <?php if($election->shouldShowOfficialResultsToStudents()): ?>
                    <a href="<?php echo e(route('student.results.election.show', $election)); ?>" class="mt-6 inline-block rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-4 py-2 text-sm font-semibold text-slate-950">
                        View Results
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/student/voting/unavailable.blade.php ENDPATH**/ ?>
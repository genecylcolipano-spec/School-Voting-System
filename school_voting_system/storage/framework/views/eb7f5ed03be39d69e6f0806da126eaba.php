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
    <div class="mx-auto max-w-2xl px-4 py-10 sm:px-6">
        <div class="rounded-2xl border border-emerald-500/25 bg-slate-900/70 p-6 text-center sm:p-8">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full border border-emerald-500/30 bg-emerald-500/15 text-emerald-300">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="mt-4 text-2xl font-bold text-white">Registration Successful</h1>
            <p class="mt-2 text-sm text-slate-400">Your performance entry has been submitted and is awaiting review.</p>

            <dl class="mt-8 grid gap-4 text-left sm:grid-cols-2">
                <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3 sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Competition Name</dt>
                    <dd class="mt-1 text-sm font-medium text-white"><?php echo e($talentEvent->title); ?></dd>
                </div>
                <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Entry Number</dt>
                    <dd class="mt-1 text-sm font-medium text-cyan-200"><?php echo e($entry->entry_number ?: '—'); ?></dd>
                </div>
                <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Registration Status</dt>
                    <dd class="mt-1 text-sm font-medium text-amber-200"><?php echo e($entry->statusLabel()); ?></dd>
                </div>
                <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Submission Date</dt>
                    <dd class="mt-1 text-sm font-medium text-white"><?php echo e(optional($entry->submitted_at)->format('M d, Y') ?? '—'); ?></dd>
                </div>
                <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Submission Time</dt>
                    <dd class="mt-1 text-sm font-medium text-white"><?php echo e(optional($entry->submitted_at)->format('g:i A') ?? '—'); ?></dd>
                </div>
            </dl>

            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="<?php echo e(route('student.talent-registration.entry.show', $entry)); ?>" class="rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-5 py-2.5 text-sm font-semibold text-slate-950 transition hover:from-cyan-400 hover:to-sky-300">
                    View My Entry
                </a>
                <a href="<?php echo e(route('student.talent-registration.index')); ?>" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm font-semibold text-slate-300 transition hover:bg-slate-800">
                    Return to Competitions
                </a>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/student/talent-registration/success.blade.php ENDPATH**/ ?>
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
    <?php
        $fields = $draft['fields'] ?? [];
        $files = $draft['files'] ?? [];
        $category = \App\Enums\TalentCategory::tryFrom((string) ($fields['talent_category'] ?? ''));
        $mediaLabel = ! empty($files['video']['name'])
            ? $files['video']['name']
            : ($fields['video_url'] ?? '—');
    ?>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6" x-data="{ confirmOpen: false }">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-cyan-300">Step 2 of 2 · Review Entry</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Review Your Entry</h1>
                <p class="mt-1 text-sm text-slate-400"><?php echo e($talentEvent->title); ?></p>
            </div>
            <a href="<?php echo e(route('student.talent-registration.register', $talentEvent)); ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:bg-slate-800">
                Edit Entry
            </a>
        </div>

        <?php if($errors->any()): ?>
            <div class="mt-4 rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                <ul class="list-disc space-y-1 pl-5">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/70">
            <div class="border-b border-slate-800 px-5 py-4 sm:px-6">
                <h2 class="text-lg font-semibold text-white">Entry Summary</h2>
                <p class="mt-1 text-sm text-slate-400">Confirm the details below before submitting.</p>
            </div>

            <dl class="grid gap-4 px-5 py-5 sm:grid-cols-2 sm:px-6">
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Student Information</dt>
                    <dd class="mt-1 text-sm text-white">
                        <?php echo e($fields['display_name'] ?? '—'); ?>

                        <span class="text-slate-500">·</span>
                        ID <?php echo e($fields['student_id_number'] ?? '—'); ?>

                        <span class="text-slate-500">·</span>
                        Grade <?php echo e($fields['grade_level'] ?? '—'); ?>-<?php echo e($fields['section'] ?? '—'); ?>

                        <?php if(! empty($fields['course_strand'])): ?>
                            <span class="text-slate-500">·</span> <?php echo e($fields['course_strand']); ?>

                        <?php endif; ?>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Selected Category</dt>
                    <dd class="mt-1 text-sm font-medium text-white"><?php echo e($category?->label() ?? '—'); ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Social Links</dt>
                    <dd class="mt-1 text-sm font-medium text-white"><?php echo e($fields['social_media'] ?? '—'); ?></dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Performance Title</dt>
                    <dd class="mt-1 text-sm font-medium text-white"><?php echo e($fields['performance_title'] ?? '—'); ?></dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Uploaded Media</dt>
                    <dd class="mt-1 break-all text-sm font-medium text-white"><?php echo e($mediaLabel); ?></dd>
                    <?php if(! empty($files['photo']['name'])): ?>
                        <dd class="mt-1 text-xs text-slate-400">Photo: <?php echo e($files['photo']['name']); ?></dd>
                    <?php endif; ?>
                    <?php if(! empty($files['thumbnail']['name'])): ?>
                        <dd class="mt-1 text-xs text-slate-400">Thumbnail: <?php echo e($files['thumbnail']['name']); ?></dd>
                    <?php endif; ?>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Performance Description</dt>
                    <dd class="mt-1 whitespace-pre-line text-sm text-slate-300"><?php echo e($fields['performance_description'] ?? '—'); ?></dd>
                </div>
            </dl>
        </div>

        <div class="mt-6 flex flex-wrap justify-end gap-3">
            <a href="<?php echo e(route('student.talent-registration.show', $talentEvent)); ?>" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm font-semibold text-slate-300 transition hover:bg-slate-800">Back</a>
            <a href="<?php echo e(route('student.talent-registration.register', $talentEvent)); ?>" class="rounded-xl border border-cyan-500/30 bg-cyan-500/10 px-5 py-2.5 text-sm font-semibold text-cyan-100 transition hover:bg-cyan-500/20">Edit Entry</a>
            <button type="button" @click="confirmOpen = true" class="rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-6 py-2.5 text-sm font-semibold text-slate-950 transition hover:from-cyan-400 hover:to-sky-300">
                Submit Entry
            </button>
        </div>

        
        <div
            x-show="confirmOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="talent-submit-confirm-title"
        >
            <div class="w-full max-w-md rounded-2xl border border-slate-700 bg-slate-900 p-6 shadow-xl" @click.outside="confirmOpen = false">
                <h2 id="talent-submit-confirm-title" class="text-lg font-semibold text-white">Submit your competition entry?</h2>
                <p class="mt-2 text-sm text-slate-400">After submission you cannot edit unless organizers reopen registration.</p>

                <form method="POST" action="<?php echo e(route('student.talent-registration.store', $talentEvent)); ?>" class="mt-6">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="confirm" value="1">
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="confirmOpen = false" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm font-semibold text-slate-300 transition hover:bg-slate-800">
                            Cancel
                        </button>
                        <button type="submit" class="rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-5 py-2.5 text-sm font-semibold text-slate-950 transition hover:from-cyan-400 hover:to-sky-300">
                            Submit Entry
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <style>[x-cloak]{display:none !important;}</style>
    <?php $__env->stopPush(); ?>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/student/talent-registration/review.blade.php ENDPATH**/ ?>
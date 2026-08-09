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
        $old = fn (string $key, $default = '') => old($key, $fields[$key] ?? $default);
    ?>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6" x-data="{ mode: '<?php echo e(old('video_url', $fields['video_url'] ?? '') ? 'url' : 'upload'); ?>' }">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-cyan-300">Step 1 of 2 · Registration Form</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Register for <?php echo e($talentEvent->title); ?></h1>
                <p class="mt-1 text-sm text-cyan-300"><?php echo e($talentEvent->talent_category?->label()); ?></p>
            </div>
            <a href="<?php echo e(route('student.talent-registration.show', $talentEvent)); ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:bg-slate-800">
                Cancel
            </a>
        </div>

        <div class="mt-4 rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3 text-xs text-slate-400">
            <p><span class="font-semibold text-slate-300">Video guidelines:</span>
                max duration <?php echo e($talentEvent->maxVideoDurationLabel()); ?>,
                max size <?php echo e($talentEvent->maxUploadSizeMb()); ?> MB,
                accepted formats: <?php echo e(implode(', ', $talentEvent->acceptedVideoFormatsArray())); ?>.</p>
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

        <?php if(session('error')): ?>
            <div class="mt-4 rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('student.talent-registration.review.store', $talentEvent)); ?>" enctype="multipart/form-data" class="mt-6 space-y-5">
            <?php echo csrf_field(); ?>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-300">Full Name</label>
                    <input type="text" name="display_name" value="<?php echo e($old('display_name', auth()->user()->name ?? '')); ?>" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300">Student ID</label>
                    <input type="text" name="student_id_number" value="<?php echo e($old('student_id_number')); ?>" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300">Grade</label>
                    <input type="text" name="grade_level" value="<?php echo e($old('grade_level')); ?>" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300">Section</label>
                    <input type="text" name="section" value="<?php echo e($old('section')); ?>" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300">Course / Strand</label>
                    <input type="text" name="course_strand" value="<?php echo e($old('course_strand')); ?>" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300">Talent Category</label>
                    <select name="talent_category" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                        <?php $__currentLoopData = \App\Enums\TalentCategory::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($category->value); ?>" <?php if($old('talent_category', $talentEvent->talent_category?->value) === $category->value): echo 'selected'; endif; ?>><?php echo e($category->label()); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300">Performance Title</label>
                <input type="text" name="performance_title" value="<?php echo e($old('performance_title')); ?>" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300">Short Bio (optional)</label>
                <input type="text" name="profile_summary" value="<?php echo e($old('profile_summary')); ?>" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300">Performance Description</label>
                <textarea name="performance_description" rows="3" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100"><?php echo e($old('performance_description')); ?></textarea>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-300">Profile Photo (optional)</label>
                    <input type="file" name="photo" accept="image/*" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-sm text-slate-300 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-800 file:px-3 file:py-1.5 file:text-slate-200">
                    <p class="mt-1 text-xs text-slate-500">Recommended: 600 × 600 px · Square (1:1) · Max 2MB</p>
                    <?php if(! empty($draft['files']['photo']['name'])): ?>
                        <p class="mt-1 text-xs text-cyan-300">Previously selected: <?php echo e($draft['files']['photo']['name']); ?> — re-upload to replace.</p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300">Social Media (optional)</label>
                    <input type="text" name="social_media" value="<?php echo e($old('social_media')); ?>" placeholder="@handle or link" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                </div>
            </div>

            <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                <div class="flex gap-4">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-200">
                        <input type="radio" name="video_mode" value="upload" x-model="mode" class="text-cyan-500 focus:ring-cyan-500/40"> Upload Video
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-200">
                        <input type="radio" name="video_mode" value="url" x-model="mode" class="text-cyan-500 focus:ring-cyan-500/40"> Video URL
                    </label>
                </div>

                <div class="mt-3" x-show="mode === 'upload'">
                    <label class="block text-sm font-medium text-slate-300">Performance Video</label>
                    <input type="file" name="video" accept="video/*" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-sm text-slate-300 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-800 file:px-3 file:py-1.5 file:text-slate-200">
                    <p class="mt-1 text-xs text-slate-500">Accepted: <?php echo e(implode(', ', $talentEvent->acceptedVideoFormatsArray())); ?> · Max <?php echo e($talentEvent->maxUploadSizeMb()); ?> MB.</p>
                    <?php if(! empty($draft['files']['video']['name'])): ?>
                        <p class="mt-1 text-xs text-cyan-300">Previously selected: <?php echo e($draft['files']['video']['name']); ?> — re-upload to replace.</p>
                    <?php endif; ?>
                </div>

                <div class="mt-3" x-show="mode === 'url'" x-cloak>
                    <label class="block text-sm font-medium text-slate-300">Video URL</label>
                    <input type="url" name="video_url" value="<?php echo e($old('video_url')); ?>" placeholder="https://youtu.be/…" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                    <p class="mt-1 text-xs text-slate-500">Paste a YouTube or Vimeo link to your performance.</p>
                </div>

                <div class="mt-3">
                    <label class="block text-sm font-medium text-slate-300">Video Thumbnail (optional)</label>
                    <input type="file" name="thumbnail" accept="image/*" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-sm text-slate-300 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-800 file:px-3 file:py-1.5 file:text-slate-200">
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="<?php echo e(route('student.talent-registration.show', $talentEvent)); ?>" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm font-semibold text-slate-300 transition hover:bg-slate-800">Back</a>
                <button type="submit" class="rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-6 py-2.5 text-sm font-semibold text-slate-950 transition hover:from-cyan-400 hover:to-sky-300">Continue to Review</button>
            </div>
        </form>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/student/talent-registration/create.blade.php ENDPATH**/ ?>
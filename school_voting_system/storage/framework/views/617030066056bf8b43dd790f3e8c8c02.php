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
    <?php if (isset($component)) { $__componentOriginalb20b972531fcf7f7b6d831b8639eeddf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb20b972531fcf7f7b6d831b8639eeddf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.faculty-portal','data' => ['title' => 'Score Performance','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('faculty-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Score Performance','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="<?php echo e(route('faculty.judging.show', $competition)); ?>" class="text-sm font-semibold text-teal-300 hover:text-teal-200">&larr; Back to <?php echo e($competition->title); ?></a>
            <?php if($locked): ?>
                <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-200">Submitted</span>
            <?php elseif(! $acceptingScores): ?>
                <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-200">Judging closed</span>
            <?php endif; ?>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h2 class="text-xl font-bold text-white"><?php echo e($entry->display_name); ?></h2>
                <p class="mt-1 text-sm text-slate-400">
                    <?php echo e($entry->performance_title ?: 'Untitled performance'); ?>

                    <?php if($entry->grade_level || $entry->section): ?>
                        · <?php echo e(trim(($entry->grade_level ?? '').' '.($entry->section ?? ''))); ?>

                    <?php endif; ?>
                </p>

                <?php if($entry->performance_description): ?>
                    <p class="mt-4 whitespace-pre-line text-sm text-slate-300"><?php echo e($entry->performance_description); ?></p>
                <?php endif; ?>

                <?php if($entry->video_path || $entry->video_url): ?>
                    <div class="mt-5 overflow-hidden rounded-xl border border-slate-800 bg-black">
                        <?php if($entry->video_path): ?>
                            <video controls class="aspect-video w-full" src="<?php echo e(route('talent.video.stream', $entry)); ?>"></video>
                        <?php elseif($entry->video_url): ?>
                            <div class="p-4 text-sm">
                                <a href="<?php echo e($entry->video_url); ?>" target="_blank" rel="noopener" class="font-semibold text-teal-300 hover:text-teal-200">Open performance video</a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="mt-5 rounded-xl border border-dashed border-slate-700 px-4 py-6 text-center text-sm text-slate-500">No video uploaded for this performance.</p>
                <?php endif; ?>
            </section>

            <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h3 class="text-lg font-semibold text-white">Score sheet</h3>
                <p class="mt-1 text-sm text-slate-400">Enter points for each criterion. Max total: <?php echo e($criteria->sum('max_points')); ?>.</p>

                <form method="POST" class="mt-5 space-y-4" x-data="{
                    scores: {
                        <?php $__currentLoopData = $criteria; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $criterion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            '<?php echo e($criterion->id); ?>': <?php echo e(old('scores.'.$criterion->id, $existingScores[$criterion->id] ?? 0)); ?>,
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    },
                    get total() {
                        return Object.values(this.scores).reduce((sum, value) => sum + (parseFloat(value) || 0), 0);
                    }
                }">
                    <?php echo csrf_field(); ?>

                    <?php $__currentLoopData = $criteria; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $criterion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div>
                            <label class="flex items-center justify-between text-sm font-medium text-slate-200">
                                <span><?php echo e($criterion->name); ?></span>
                                <span class="text-xs text-slate-500">max <?php echo e($criterion->max_points); ?></span>
                            </label>
                            <input
                                type="number"
                                name="scores[<?php echo e($criterion->id); ?>]"
                                x-model.number="scores['<?php echo e($criterion->id); ?>']"
                                min="0"
                                max="<?php echo e($criterion->max_points); ?>"
                                step="0.5"
                                <?php if($locked || (! $acceptingScores && ! $sheet)): echo 'disabled'; endif; ?>
                                class="mt-2 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white focus:border-teal-500 focus:outline-none"
                                <?php if(! $locked): echo 'required'; endif; ?>
                            >
                            <?php $__errorArgs = ['scores.'.$criterion->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-xs text-rose-300"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <div>
                        <label class="text-sm font-medium text-slate-200">Notes (optional)</label>
                        <textarea
                            name="notes"
                            rows="3"
                            <?php if($locked): echo 'disabled'; endif; ?>
                            class="mt-2 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white focus:border-teal-500 focus:outline-none"
                        ><?php echo e(old('notes', $sheet?->notes)); ?></textarea>
                    </div>

                    <div class="rounded-xl border border-teal-500/20 bg-teal-500/10 px-4 py-3 text-sm text-teal-100">
                        Running total: <span class="font-bold" x-text="total.toFixed(2)"><?php echo e(number_format((float) ($sheet?->total_score ?? 0), 2)); ?></span>
                    </div>

                    <?php if (! ($locked)): ?>
                        <div class="flex flex-wrap gap-3 pt-2">
                            <button
                                type="submit"
                                formaction="<?php echo e(route('faculty.judging.draft', [$competition, $entry])); ?>"
                                <?php if(! $acceptingScores && ! $sheet): echo 'disabled'; endif; ?>
                                class="rounded-xl border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-slate-800 disabled:opacity-40"
                            >
                                Save draft
                            </button>
                            <button
                                type="submit"
                                formaction="<?php echo e(route('faculty.judging.submit', [$competition, $entry])); ?>"
                                <?php if(! $acceptingScores): echo 'disabled'; endif; ?>
                                class="rounded-xl bg-gradient-to-r from-teal-500 to-emerald-400 px-4 py-2 text-sm font-semibold text-slate-950 disabled:opacity-40"
                                onclick="return confirm('Submit these scores? You will not be able to edit them afterward.');"
                            >
                                Submit scores
                            </button>
                        </div>
                    <?php endif; ?>
                </form>
            </section>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb20b972531fcf7f7b6d831b8639eeddf)): ?>
<?php $attributes = $__attributesOriginalb20b972531fcf7f7b6d831b8639eeddf; ?>
<?php unset($__attributesOriginalb20b972531fcf7f7b6d831b8639eeddf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb20b972531fcf7f7b6d831b8639eeddf)): ?>
<?php $component = $__componentOriginalb20b972531fcf7f7b6d831b8639eeddf; ?>
<?php unset($__componentOriginalb20b972531fcf7f7b6d831b8639eeddf); ?>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/faculty/judging/score.blade.php ENDPATH**/ ?>
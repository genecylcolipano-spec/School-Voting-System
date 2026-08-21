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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => 'Judges — '.$talentEvent->title,'user' => $user,'notificationsCount' => $notificationsCount,'assignedRole' => $assignedRole]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Judges — '.$talentEvent->title),'user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount),'assigned-role' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($assignedRole)]); ?>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <a href="<?php echo e(route('admin.talent-competition.show', $talentEvent)); ?>" class="text-sm font-semibold text-violet-300 hover:text-violet-200">&larr; Back to competition</a>
            <span class="rounded-full border border-violet-500/30 bg-violet-500/10 px-3 py-1 text-xs font-semibold text-violet-100"><?php echo e($talentEvent->votingMethodLabel()); ?></span>
        </div>

        <?php if (! ($talentEvent->requiresJudges())): ?>
            <div class="mb-4 rounded-xl border border-amber-500/25 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                This competition uses student-only voting. Change the voting method to <strong>Judges Only</strong> or <strong>Judges + Students</strong> in Settings before judges can be assigned.
            </div>
        <?php endif; ?>

        <?php if (! ($canAssignJudges)): ?>
            <div class="mb-4 rounded-xl border border-cyan-500/25 bg-cyan-500/10 px-4 py-3 text-sm text-cyan-100">
                Judge assignment is managed by the Super Administrator (Faculty → Assign Judges). Operations Admins can configure scoring criteria here.
            </div>
        <?php endif; ?>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-white">Assigned judges</h2>
                <p class="mt-1 text-sm text-slate-400">Faculty accounts who can score performances in My Judging.</p>

                <ul class="mt-4 space-y-3">
                    <?php $__empty_1 = true; $__currentLoopData = $talentEvent->judges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3">
                            <div>
                                <p class="font-medium text-white"><?php echo e($assignment->user?->name ?? 'Unknown'); ?></p>
                                <p class="text-xs text-slate-500">
                                    <?php echo e($assignment->user?->account_id); ?>

                                    · <?php echo e($assignment->roleLabel()); ?>

                                    · <?php echo e($assignment->statusLabel()); ?>

                                </p>
                            </div>
                            <?php if($canAssignJudges): ?>
                                <form method="POST" action="<?php echo e(route('admin.talent-competition.judges.remove', [$talentEvent, $assignment->user])); ?>" onsubmit="return confirm('Remove this judge assignment?');">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="text-sm font-semibold text-rose-300 hover:text-rose-200">Remove</button>
                                </form>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <li class="rounded-xl border border-dashed border-slate-700 px-4 py-6 text-center text-sm text-slate-500">No judges assigned yet.</li>
                    <?php endif; ?>
                </ul>

                <?php if($canAssignJudges && $talentEvent->requiresJudges()): ?>
                    <form method="POST" action="<?php echo e(route('admin.talent-competition.judges.assign', $talentEvent)); ?>" class="mt-5 space-y-3 border-t border-slate-800 pt-5">
                        <?php echo csrf_field(); ?>
                        <label class="block text-sm font-medium text-slate-200">Assign faculty</label>
                        <select name="user_id" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
                            <option value="">Select faculty account…</option>
                            <?php $__currentLoopData = $availableFaculty; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $faculty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($faculty->id); ?>"><?php echo e($faculty->name); ?> (<?php echo e($faculty->account_id); ?>)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <label class="block text-sm font-medium text-slate-200">Judge role</label>
                        <select name="judge_role" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
                            <?php $__currentLoopData = $judgeRoles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($role->value); ?>" <?php if($role === \App\Enums\TalentJudgeRole::Judge): echo 'selected'; endif; ?>><?php echo e($role->label()); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php if($availableFaculty->isEmpty()): ?>
                            <p class="text-xs text-slate-500">No available faculty with an active account and registered Passkey.</p>
                        <?php endif; ?>
                        <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white" <?php if($availableFaculty->isEmpty()): echo 'disabled'; endif; ?>>
                            Assign judge
                        </button>
                    </form>
                <?php endif; ?>
            </section>

            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-white">Scoring criteria</h2>
                <p class="mt-1 text-sm text-slate-400">Default rubric totals 100 points. Criteria lock after scoring begins.</p>

                <form method="POST" action="<?php echo e(route('admin.talent-competition.criteria.update', $talentEvent)); ?>" class="mt-4 space-y-3" x-data="{
                    rows: <?php echo \Illuminate\Support\Js::from($talentEvent->judgingCriteria->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'max_points' => $c->max_points])->values())->toHtml() ?>
                }">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>

                    <template x-for="(row, index) in rows" :key="index">
                        <div class="grid gap-2 rounded-xl border border-slate-800 bg-slate-950/40 p-3 sm:grid-cols-[1fr_100px_auto]">
                            <input type="hidden" :name="`criteria[${index}][id]`" :value="row.id || ''">
                            <input type="text" :name="`criteria[${index}][name]`" x-model="row.name" required class="rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white" placeholder="Criterion name">
                            <input type="number" min="1" max="100" :name="`criteria[${index}][max_points]`" x-model.number="row.max_points" required class="rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white" placeholder="Max">
                            <button type="button" @click="rows.splice(index, 1)" class="text-sm text-rose-300 hover:text-rose-200">Remove</button>
                        </div>
                    </template>

                    <div class="flex flex-wrap gap-3">
                        <button type="button" @click="rows.push({ id: null, name: '', max_points: 25 })" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">
                            Add criterion
                        </button>
                        <?php if($canManageCriteria): ?>
                            <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white">
                                Save criteria
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/talent-competition/judges.blade.php ENDPATH**/ ?>
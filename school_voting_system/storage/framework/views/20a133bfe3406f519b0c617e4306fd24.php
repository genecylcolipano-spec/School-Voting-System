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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => $account->name,'user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($account->name),'user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <a href="<?php echo e($indexRoute); ?>" class="text-sm font-semibold text-violet-300 hover:text-violet-200">&larr; Back to list</a>
            <div class="flex flex-wrap gap-2">
                <a href="<?php echo e($editRoute); ?>" class="rounded-xl border border-violet-500/30 px-4 py-2 text-sm font-semibold text-violet-300 hover:bg-violet-500/10">Edit</a>
                <form method="POST" action="<?php echo e(route('super-admin.staff.enrollment', $account)); ?>" onsubmit="return confirm('Generate a passkey reset / enrollment link?');">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="rounded-xl border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Reset Passkey</button>
                </form>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="mb-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200"><?php echo e(session('error')); ?></div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="mb-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                <ul class="list-disc space-y-1 pl-5">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php echo $__env->make('admin.partials.enrollment-link-banner', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <section class="mb-6 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="font-mono text-xs text-slate-500"><?php echo e($account->account_id); ?></p>
                    <h2 class="mt-1 text-2xl font-bold text-white"><?php echo e($account->name); ?></h2>
                    <p class="mt-1 text-sm text-slate-400"><?php echo e($account->email); ?> · <?php echo e($account->roleLabel()); ?></p>
                    <?php if($account->staffRole): ?>
                        <p class="mt-1 text-sm text-slate-400">Staff role: <?php echo e($account->staffRole->name); ?></p>
                    <?php endif; ?>
                </div>
                <div class="text-right">
                    <?php if($account->archived_at): ?>
                        <span class="rounded-full border border-slate-600 bg-slate-800 px-3 py-1 text-xs font-semibold text-slate-300">Archived</span>
                    <?php elseif($account->is_active): ?>
                        <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-200">Active</span>
                    <?php else: ?>
                        <span class="rounded-full border border-rose-500/30 bg-rose-500/10 px-3 py-1 text-xs font-semibold text-rose-200">Inactive</span>
                    <?php endif; ?>
                    <p class="mt-2 text-xs text-slate-500"><?php echo e($account->passkeys_count); ?> registered device(s)</p>
                </div>
            </div>

            <?php if($removalBlockers !== []): ?>
                <div class="mt-4 rounded-xl border border-amber-500/25 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                    <p class="font-semibold">Removal blocked — prefer deactivate/archive:</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        <?php $__currentLoopData = $removalBlockers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $blocker): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($blocker); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>
        </section>

        <?php if($account->isFaculty()): ?>
            <section id="competitions" class="mb-6 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6" x-data="{ removeOpen: false, removeAction: '', removeTitle: '' }">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-white">Assigned Competitions</h3>
                        <p class="mt-1 text-sm text-slate-400">Super Admin assigns faculty judges after an Administrator creates the competition.</p>
                    </div>
                    <?php if(($account->passkeys_count ?? 0) < 1): ?>
                        <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-200">Passkey required to assign</span>
                    <?php endif; ?>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-800 text-left text-xs uppercase tracking-wide text-slate-500">
                                <th class="px-2 py-2">Competition</th>
                                <th class="px-2 py-2">Category</th>
                                <th class="px-2 py-2">Judge Role</th>
                                <th class="px-2 py-2">Status</th>
                                <th class="px-2 py-2">Assigned</th>
                                <th class="px-2 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $assignedCompetitions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $event = $assignment->talentEvent;
                                    $eventUsable = $event && ! $event->trashed();
                                ?>
                                <tr class="border-b border-slate-800/70 text-slate-200">
                                    <td class="px-2 py-3 font-medium text-white">
                                        <?php echo e($event?->title ?? 'Unavailable competition'); ?>

                                        <?php if($event?->trashed()): ?>
                                            <span class="ml-1 text-xs font-normal text-amber-300">(Archived)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-2 py-3 text-slate-300"><?php echo e($event?->talent_category?->label() ?? $event?->type?->label() ?? '—'); ?></td>
                                    <td class="px-2 py-3">
                                        <?php if($eventUsable): ?>
                                            <form method="POST" action="<?php echo e(route('super-admin.faculty.competitions.role', [$account, $event->getKey()])); ?>" class="flex items-center gap-2">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PUT'); ?>
                                                <select name="judge_role" class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-1 text-xs text-white" onchange="this.form.submit()">
                                                    <?php $__currentLoopData = $judgeRoles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($role->value); ?>" <?php if($assignment->judge_role === $role): echo 'selected'; endif; ?>><?php echo e($role->label()); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-xs text-slate-400"><?php echo e($assignment->roleLabel()); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-2 py-3">
                                        <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-xs font-semibold text-emerald-200"><?php echo e($assignment->statusLabel()); ?></span>
                                    </td>
                                    <td class="px-2 py-3 text-slate-400"><?php echo e(optional($assignment->assigned_at)->format('M d, Y') ?? '—'); ?></td>
                                    <td class="px-2 py-3">
                                        <div class="flex flex-wrap gap-3">
                                            <?php if($eventUsable): ?>
                                                <a href="<?php echo e(route('admin.talent-competition.show', $event)); ?>" class="text-xs font-semibold text-violet-300 hover:text-violet-200">View Competition</a>
                                            <?php endif; ?>
                                            <?php if($event || $assignment->talent_event_id): ?>
                                                <button
                                                    type="button"
                                                    class="text-xs font-semibold text-rose-300 hover:text-rose-200"
                                                    @click="removeOpen = true; removeAction = <?php echo \Illuminate\Support\Js::from(route('super-admin.faculty.competitions.remove', [$account, $event?->getKey() ?? $assignment->talent_event_id]))->toHtml() ?>; removeTitle = <?php echo \Illuminate\Support\Js::from($event?->title ?? 'Unavailable competition')->toHtml() ?>"
                                                >Remove Assignment</button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="px-2 py-8 text-center text-sm text-slate-500">No competitions assigned.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <form method="POST" action="<?php echo e(route('super-admin.faculty.competitions.assign', $account)); ?>" class="mt-5 grid gap-3 border-t border-slate-800 pt-5 sm:grid-cols-2 lg:grid-cols-4">
                    <?php echo csrf_field(); ?>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-300">Competition</label>
                        <select name="talent_event_id" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
                            <option value="">Select competition…</option>
                            <?php $__currentLoopData = $assignableCompetitions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $competition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($competition->id); ?>">
                                    <?php echo e($competition->title); ?>

                                    — <?php echo e($competition->talent_category?->label() ?? $competition->type?->label() ?? 'Talent Competition'); ?>

                                    — <?php echo e($competition->schoolYearLabel()); ?>

                                    — <?php echo e($competition->assignmentPhaseLabel()); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Faculty</label>
                        <input type="text" value="<?php echo e($account->name); ?>" disabled class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/60 px-3 py-2 text-slate-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Judge Role</label>
                        <select name="judge_role" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
                            <?php $__currentLoopData = $judgeRoles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($role->value); ?>" <?php if($role === \App\Enums\TalentJudgeRole::Judge): echo 'selected'; endif; ?>><?php echo e($role->label()); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-4">
                        <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white" <?php if($assignableCompetitions->isEmpty() || ($account->passkeys_count ?? 0) < 1): echo 'disabled'; endif; ?>>
                            Assign Judge
                        </button>
                        <?php if($assignableCompetitions->isEmpty()): ?>
                            <p class="mt-2 text-xs text-slate-500">No eligible competitions available. Published, active competitions created by an Administrator will appear here (excluding archived, completed, and already-assigned events).</p>
                        <?php elseif(($account->passkeys_count ?? 0) < 1): ?>
                            <p class="mt-2 text-xs text-amber-300/90">This faculty account must register a Passkey before a judge assignment can be saved.</p>
                        <?php endif; ?>
                    </div>
                </form>

                <div x-show="removeOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4" role="dialog" aria-modal="true">
                    <div class="w-full max-w-md rounded-2xl border border-slate-700 bg-slate-900 p-6 shadow-xl" @click.outside="removeOpen = false">
                        <h4 class="text-lg font-semibold text-white">Remove judge assignment?</h4>
                        <p class="mt-2 text-sm text-slate-400">
                            <span x-text="removeTitle"></span> — <?php echo e($account->name); ?> will immediately lose judging access.
                        </p>
                        <form method="POST" :action="removeAction" class="mt-5 space-y-3">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <div>
                                <label class="block text-sm font-medium text-slate-300">Reason (optional)</label>
                                <textarea name="removal_reason" rows="2" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white" placeholder="Optional reason for the audit log"></textarea>
                            </div>
                            <div class="flex justify-end gap-3">
                                <button type="button" @click="removeOpen = false" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Cancel</button>
                                <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">Remove Assignment</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <section id="devices" class="mb-6 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
            <h3 class="text-lg font-semibold text-white">Registered Devices</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-800 text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-2 py-2">Name</th>
                            <th class="px-2 py-2">Status</th>
                            <th class="px-2 py-2">Last used</th>
                            <th class="px-2 py-2">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $devices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $device): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="border-b border-slate-800/70 text-slate-200">
                                <td class="px-2 py-2"><?php echo e($device->device_name ?: $device->name); ?></td>
                                <td class="px-2 py-2"><?php echo e($device->status?->value ?? $device->status ?? 'active'); ?></td>
                                <td class="px-2 py-2 text-slate-400"><?php echo e(optional($device->last_used_at)->format('M d, Y g:i A') ?? '—'); ?></td>
                                <td class="px-2 py-2 text-slate-400"><?php echo e(optional($device->created_at)->format('M d, Y')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="4" class="px-2 py-6 text-center text-slate-500">No registered passkey devices.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="login-history" class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
            <h3 class="text-lg font-semibold text-white">Login History</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-800 text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-2 py-2">When</th>
                            <th class="px-2 py-2">Browser</th>
                            <th class="px-2 py-2">OS</th>
                            <th class="px-2 py-2">IP</th>
                            <th class="px-2 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $loginHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="border-b border-slate-800/70 text-slate-200">
                                <td class="px-2 py-2 text-slate-400"><?php echo e(optional($row['occurred_at'])->format('M d, Y g:i A')); ?></td>
                                <td class="px-2 py-2"><?php echo e($row['browser']); ?></td>
                                <td class="px-2 py-2"><?php echo e($row['os']); ?></td>
                                <td class="px-2 py-2 font-mono text-xs"><?php echo e($row['ip_address'] ?? '—'); ?></td>
                                <td class="px-2 py-2"><?php echo e($row['status']); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="5" class="px-2 py-6 text-center text-slate-500">No login history available yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/staff-users/show.blade.php ENDPATH**/ ?>
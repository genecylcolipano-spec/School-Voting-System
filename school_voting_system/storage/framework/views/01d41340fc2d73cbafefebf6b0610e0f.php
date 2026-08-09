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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => 'Edit Student Record','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Edit Student Record','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <?php echo $__env->make('admin.partials.page-header', [
            'title' => 'Edit Student Record',
            'description' => $student->name,
            'showAction' => false,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php if(session('success')): ?>
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6 lg:col-span-2">
                <h2 class="text-lg font-semibold text-white">School Record</h2>
                <p class="mt-1 text-sm text-slate-400">Update profile details and assign grade, section, and enrollment status.</p>

                <form method="POST" action="<?php echo e(route('admin.students.update', $student)); ?>" class="mt-6 space-y-5">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>

                    <div>
                        <label class="block text-sm font-medium text-slate-300">Account ID</label>
                        <input type="text" value="<?php echo e($student->account_id); ?>" disabled class="mt-1 w-full rounded-xl border border-slate-800 bg-slate-950/80 px-4 py-2.5 font-mono text-slate-400">
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-300">Full Name</label>
                        <input id="name" name="name" type="text" value="<?php echo e(old('name', $student->name)); ?>" required
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-300">Email Address</label>
                        <input id="email" name="email" type="email" value="<?php echo e(old('email', $student->email)); ?>" required
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="grade_level" class="block text-sm font-medium text-slate-300">Grade</label>
                            <?php if(count($gradeLevels) > 0): ?>
                                <select id="grade_level" name="grade_level" required
                                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                                    <option value="">Select grade</option>
                                    <?php $__currentLoopData = $gradeLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($grade); ?>" <?php if((string) old('grade_level', $student->grade_level) === (string) $grade): echo 'selected'; endif; ?>>
                                            Grade <?php echo e($grade); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            <?php else: ?>
                                <input id="grade_level" name="grade_level" type="text" value="<?php echo e(old('grade_level', $student->grade_level)); ?>" required
                                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none focus:ring-2 focus:ring-violet-500/20"
                                    placeholder="e.g. 11">
                            <?php endif; ?>
                            <?php $__errorArgs = ['grade_level'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div>
                            <label for="section" class="block text-sm font-medium text-slate-300">Section</label>
                            <?php if(count($sections) > 0): ?>
                                <select id="section" name="section" required
                                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                                    <option value="">Select section</option>
                                    <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sectionOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($sectionOption); ?>" <?php if((string) old('section', $student->section) === (string) $sectionOption): echo 'selected'; endif; ?>>
                                            Section <?php echo e($sectionOption); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            <?php else: ?>
                                <input id="section" name="section" type="text" value="<?php echo e(old('section', $student->section)); ?>" required
                                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none focus:ring-2 focus:ring-violet-500/20"
                                    placeholder="e.g. A">
                            <?php endif; ?>
                            <?php $__errorArgs = ['section'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <div>
                        <label for="student_status" class="block text-sm font-medium text-slate-300">Enrollment Status</label>
                        <select id="student_status" name="student_status" required
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                            <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($status->value); ?>" <?php if((string) old('student_status', $student->student_status?->value) === $status->value): echo 'selected'; endif; ?>>
                                    <?php echo e($status->label()); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['student_status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="flex flex-wrap gap-3 pt-2">
                        <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90">
                            Save changes
                        </button>
                        <a href="<?php echo e(route('admin.students.index')); ?>" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm font-semibold text-slate-300 hover:bg-slate-800">
                            Cancel
                        </a>
                    </div>
                </form>
            </section>

            <aside class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-white">Account Summary</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-3 border-b border-slate-800 pb-2">
                        <dt class="text-slate-500">Account ID</dt>
                        <dd class="font-mono text-slate-200"><?php echo e($student->account_id); ?></dd>
                    </div>
                    <div class="flex justify-between gap-3 border-b border-slate-800 pb-2">
                        <dt class="text-slate-500">Role</dt>
                        <dd class="text-slate-200"><?php echo e($student->roleLabel()); ?></dd>
                    </div>
                    <div class="flex justify-between gap-3 border-b border-slate-800 pb-2">
                        <dt class="text-slate-500">Active</dt>
                        <dd class="<?php echo e($student->is_active ? 'text-emerald-300' : 'text-amber-300'); ?>">
                            <?php echo e($student->is_active ? 'Yes' : 'No'); ?>

                        </dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Member since</dt>
                        <dd class="text-slate-200"><?php echo e(optional($student->created_at)->format('M d, Y') ?? '—'); ?></dd>
                    </div>
                </dl>
            </aside>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/students/edit.blade.php ENDPATH**/ ?>
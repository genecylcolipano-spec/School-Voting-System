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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => 'System Settings','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'System Settings','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <?php echo $__env->make('admin.partials.page-header', [
            'title' => 'System Settings',
            'description' => 'Global application configuration for the school voting platform.',
            'showAction' => false,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <form method="POST" action="<?php echo e(route('super-admin.system.settings.update')); ?>" enctype="multipart/form-data" class="space-y-6">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-white">General</h2>
                <p class="mt-1 text-sm text-slate-400">Product name, school identity, and academic period.</p>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-300">System Name</label>
                        <input name="system_name" type="text" value="<?php echo e(old('system_name', $settings['system_name'])); ?>"
                            placeholder="School Voting System"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none">
                        <p class="mt-1 text-xs text-slate-500">Shown as the product name in portals and page titles.</p>
                        <?php $__errorArgs = ['system_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-300">School Name</label>
                        <input name="school_name" type="text" value="<?php echo e(old('school_name', $settings['school_name'])); ?>"
                            placeholder="Rosemont Hills Montessori College"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none">
                        <p class="mt-1 text-xs text-slate-500">Shown as “Powered by …” under the system name.</p>
                        <?php $__errorArgs = ['school_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-300">School Logo</label>
                        <div class="mt-2 flex flex-wrap items-center gap-4">
                            <?php if($logoUrl): ?>
                                <div>
                                    <img src="<?php echo e($logoUrl); ?>" alt="School logo" class="h-16 w-16 rounded-xl border border-slate-700 object-cover">
                                    <p class="mt-1 text-center text-[10px] text-emerald-400/90">Custom upload</p>
                                </div>
                            <?php else: ?>
                                <div>
                                    <div class="flex h-16 w-16 items-center justify-center rounded-xl border border-slate-700 bg-gradient-to-br from-violet-600 to-indigo-500 text-white" aria-hidden="true">
                                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                    </div>
                                    <p class="mt-1 text-center text-[10px] text-slate-500">Default icon</p>
                                </div>
                            <?php endif; ?>
                            <div class="min-w-0 flex-1">
                                <input type="file" name="school_logo" accept="image/jpeg,image/png,image/webp"
                                    class="block w-full text-sm text-slate-400 file:mr-3 file:rounded-lg file:border-0 file:bg-violet-600 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white">
                                <p class="mt-1 text-xs text-slate-500">If nothing is uploaded, portals use the purple book icon.</p>
                                <?php if($logoUrl): ?>
                                    <label class="mt-2 inline-flex items-center gap-2 text-xs text-slate-400">
                                        <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-600 bg-slate-900 text-violet-500">
                                        Remove current logo
                                    </label>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php $__errorArgs = ['school_logo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Academic Year</label>
                        <input name="academic_year" type="text" value="<?php echo e(old('academic_year', $settings['academic_year'])); ?>"
                            placeholder="2025-2026"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Semester</label>
                        <input name="semester" type="text" value="<?php echo e(old('semester', $settings['semester'])); ?>"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none">
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-white">Registration</h2>
                <p class="mt-1 text-sm text-slate-400">Control how accounts can be created.</p>
                <div class="mt-5 space-y-4">
                    <label class="flex items-start gap-3 rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3 text-sm text-slate-300">
                        <input type="checkbox" name="enable_student_registration" value="1" class="mt-1 rounded border-slate-600 bg-slate-900 text-violet-500" <?php if(old('enable_student_registration', $settings['enable_student_registration'])): echo 'checked'; endif; ?>>
                        <span>
                            <span class="font-medium text-white">Enable Student Registration</span>
                            <span class="mt-0.5 block text-slate-500">Users can register after matching Student, Faculty, or Administrator Roster records.</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3 text-sm text-slate-300">
                        <input type="checkbox" name="enable_faculty_registration" value="1" class="mt-1 rounded border-slate-600 bg-slate-900 text-violet-500" <?php if(old('enable_faculty_registration', $settings['enable_faculty_registration'])): echo 'checked'; endif; ?>>
                        <span>
                            <span class="font-medium text-white">Enable Faculty Registration</span>
                            <span class="mt-0.5 block text-slate-500">Future-ready. Faculty accounts are still created by Super Admin today.</span>
                        </span>
                    </label>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                        <p class="text-sm font-medium text-white">Passwordless Authentication Status</p>
                        <p class="mt-1 text-sm text-emerald-300">Enabled (Passkeys) — read-only</p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-white">Voting</h2>
                <p class="mt-1 text-sm text-slate-400">Module availability flags for the platform.</p>
                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    <?php $__currentLoopData = [
                        'enable_elections' => ['Enable Elections', 'Student election ballots'],
                        'enable_talent_voting' => ['Enable Talent Competition Voting', 'Talent voting & judging'],
                        'enable_fundraising' => ['Enable Fundraising', 'Donation campaigns'],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => [$label, $hint]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-start gap-3 rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3 text-sm text-slate-300">
                            <input type="checkbox" name="<?php echo e($field); ?>" value="1" class="mt-1 rounded border-slate-600 bg-slate-900 text-violet-500" <?php if(old($field, $settings[$field])): echo 'checked'; endif; ?>>
                            <span>
                                <span class="font-medium text-white"><?php echo e($label); ?></span>
                                <span class="mt-0.5 block text-slate-500"><?php echo e($hint); ?></span>
                            </span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>

            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-white">Announcements</h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Default Visibility</label>
                        <select name="announcement_default_visibility" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white">
                            <?php $__currentLoopData = ['all' => 'All users', 'students' => 'Students', 'faculty' => 'Faculty', 'admins' => 'Administrators']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value); ?>" <?php if(old('announcement_default_visibility', $settings['announcement_default_visibility']) === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Default Expiration (days)</label>
                        <input name="announcement_default_expiration_days" type="number" min="1" max="365"
                            value="<?php echo e(old('announcement_default_expiration_days', $settings['announcement_default_expiration_days'])); ?>"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white">
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-white">Security & Support</h2>
                <p class="mt-1 text-sm text-slate-400">Previously managed on the Super Admin dashboard.</p>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Session Timeout (minutes)</label>
                        <input name="session_timeout_minutes" type="number" min="5" max="480"
                            value="<?php echo e(old('session_timeout_minutes', $settings['session_timeout_minutes'])); ?>"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Support Email</label>
                        <input name="support_email" type="email" value="<?php echo e(old('support_email', $settings['support_email'])); ?>"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-300">IP Whitelist (comma-separated)</label>
                        <input name="ip_whitelist" type="text" value="<?php echo e(old('ip_whitelist', $ipWhitelistText)); ?>"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white">
                    </div>
                    <label class="flex items-start gap-3 rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3 text-sm text-slate-300">
                        <input type="checkbox" name="ip_whitelist_enabled" value="1" class="mt-1 rounded border-slate-600 bg-slate-900 text-violet-500" <?php if(old('ip_whitelist_enabled', $settings['ip_whitelist_enabled'])): echo 'checked'; endif; ?>>
                        <span class="font-medium text-white">Enable IP Whitelist for Admin Access</span>
                    </label>
                    <label class="flex items-start gap-3 rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3 text-sm text-slate-300">
                        <input type="checkbox" name="two_factor_recovery_enabled" value="1" class="mt-1 rounded border-slate-600 bg-slate-900 text-violet-500" <?php if(old('two_factor_recovery_enabled', $settings['two_factor_recovery_enabled'])): echo 'checked'; endif; ?>>
                        <span class="font-medium text-white">Enable Passkey Recovery Flow</span>
                    </label>
                    <label class="flex items-start gap-3 rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3 text-sm text-slate-300 sm:col-span-2">
                        <input type="checkbox" name="public_results_published" value="1" class="mt-1 rounded border-slate-600 bg-slate-900 text-violet-500" <?php if(old('public_results_published', $settings['public_results_published'])): echo 'checked'; endif; ?>>
                        <span class="font-medium text-white">Mark public results as published (platform flag)</span>
                    </label>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Support Team Label</label>
                        <input name="support_team_label" type="text" value="<?php echo e(old('support_team_label', $settings['support_team_label'])); ?>"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white">
                    </div>
                </div>
            </section>

            <div class="flex justify-end">
                <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90">
                    Save Settings
                </button>
            </div>
        </form>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/system/settings.blade.php ENDPATH**/ ?>
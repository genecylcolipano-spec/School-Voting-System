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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => 'Maintenance Mode','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Maintenance Mode','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <?php echo $__env->make('admin.partials.page-header', [
            'title' => 'Maintenance Mode',
            'description' => 'Take the platform offline for scheduled maintenance while optionally allowing Super Admin access.',
            'showAction' => false,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="mb-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Current Status</p>
                <p class="mt-2 text-xl font-bold <?php echo e($status['enabled'] ? 'text-amber-300' : 'text-emerald-300'); ?>">
                    <?php echo e($status['enabled'] ? 'Maintenance' : 'Online'); ?>

                </p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Last Updated</p>
                <p class="mt-2 text-sm font-semibold text-white"><?php echo e($status['updated_at']?->timezone(config('app.timezone'))->format('M d, Y g:i A') ?? '—'); ?></p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Updated By</p>
                <p class="mt-2 text-sm font-semibold text-white"><?php echo e($status['updated_by']?->name ?? '—'); ?></p>
            </div>
        </div>

        <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
            <form method="POST" action="<?php echo e($status['enabled'] ? route('super-admin.system.maintenance.update') : route('super-admin.system.maintenance.enable')); ?>" class="space-y-5">
                <?php echo csrf_field(); ?>
                <?php if($status['enabled']): ?>
                    <?php echo method_field('PUT'); ?>
                <?php endif; ?>

                <div>
                    <p class="text-sm font-medium text-slate-300">Mode</p>
                    <div class="mt-2 flex flex-wrap gap-4 text-sm text-slate-300">
                        <span class="inline-flex items-center gap-2 <?php echo e(! $status['enabled'] ? 'text-emerald-300' : ''); ?>">
                            <span class="h-2.5 w-2.5 rounded-full <?php echo e(! $status['enabled'] ? 'bg-emerald-400' : 'bg-slate-600'); ?>"></span>
                            Online
                        </span>
                        <span class="inline-flex items-center gap-2 <?php echo e($status['enabled'] ? 'text-amber-300' : ''); ?>">
                            <span class="h-2.5 w-2.5 rounded-full <?php echo e($status['enabled'] ? 'bg-amber-400' : 'bg-slate-600'); ?>"></span>
                            Maintenance
                        </span>
                    </div>
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-slate-300">Maintenance Message</label>
                    <textarea id="message" name="message" rows="3" required
                        class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none"><?php echo e(old('message', $status['message'])); ?></textarea>
                    <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="return_at" class="block text-sm font-medium text-slate-300">Estimated Return Date</label>
                        <input id="return_at" name="return_at" type="datetime-local"
                            value="<?php echo e(old('return_at', optional($status['return_at'])->format('Y-m-d\\TH:i'))); ?>"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white">
                        <?php $__errorArgs = ['return_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <p class="block text-sm font-medium text-slate-300">Allow Super Administrator Access</p>
                        <label class="mt-3 flex items-center gap-3 text-sm text-slate-300">
                            <input type="checkbox" name="allow_super_admin" value="1" class="rounded border-slate-600 bg-slate-900 text-violet-500" <?php if(old('allow_super_admin', $status['allow_super_admin'])): echo 'checked'; endif; ?>>
                            Yes — Super Admin may bypass maintenance
                        </label>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <?php if($status['enabled']): ?>
                        <button type="submit" class="rounded-xl border border-violet-500/30 px-5 py-2.5 text-sm font-semibold text-violet-200 hover:bg-violet-500/10">
                            Save Message
                        </button>
                    <?php else: ?>
                        <button type="submit" class="rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:opacity-90"
                            onclick="return confirm('Enable maintenance mode? Students, Administrators, and Faculty will be blocked.')">
                            Enable Maintenance
                        </button>
                    <?php endif; ?>
                </div>
            </form>

            <?php if($status['enabled']): ?>
                <form method="POST" action="<?php echo e(route('super-admin.system.maintenance.disable')); ?>" class="mt-4 border-t border-slate-800 pt-4"
                    onsubmit="return confirm('Disable maintenance mode and bring the system online?')">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:opacity-90">
                        Disable Maintenance
                    </button>
                </form>
            <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/system/maintenance.blade.php ENDPATH**/ ?>
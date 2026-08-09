<?php
    $section = in_array($section ?? 'profile', ['profile', 'devices', 'security'], true)
        ? ($section ?? 'profile')
        : 'profile';
    $isSuperAdmin = $user->isSuperAdmin();
    $passwordlessEnabled = $passwordlessEnabled ?? ($user->passkeys_count > 0);
    $securityContext = $securityContext ?? [];
    $departmentLabel = $departmentLabel ?? ($user->staffRole?->name ?? ($isSuperAdmin ? 'System Administration' : '—'));
    $trustedDeviceCount = $trustedDeviceCount ?? 0;
    $systemAccessHistory = $systemAccessHistory ?? [];
    $navClass = fn (string $key) => 'rounded-xl px-4 py-2 text-sm font-semibold transition '.($section === $key
        ? 'bg-cyan-500/20 text-cyan-100 ring-1 ring-cyan-500/30'
        : 'text-slate-400 hover:bg-slate-800/70 hover:text-white');
?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => 'Settings','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Settings','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <div class="mb-6">
            <h1 class="text-xl font-bold text-white">Settings</h1>
            <p class="mt-1 text-sm text-slate-400">
                <?php echo e($isSuperAdmin ? 'Manage your super administrator profile, devices, and security posture.' : 'Manage your administrator profile, devices, and account security.'); ?>

            </p>
        </div>

        <nav class="mb-6 flex flex-wrap gap-2" aria-label="Settings sections">
            <a href="<?php echo e(route('profile.edit', ['section' => 'profile'])); ?>" class="<?php echo e($navClass('profile')); ?>">Profile</a>
            <a href="<?php echo e(route('profile.edit', ['section' => 'devices'])); ?>" class="<?php echo e($navClass('devices')); ?>">Devices</a>
            <a href="<?php echo e(route('profile.edit', ['section' => 'security'])); ?>" class="<?php echo e($navClass('security')); ?>">Security</a>
        </nav>

        <?php if(session('status') === 'profile-updated'): ?>
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">Profile updated successfully.</div>
        <?php endif; ?>
        <?php if(session('status') === 'other-sessions-logged-out'): ?>
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">Other devices have been signed out.</div>
        <?php endif; ?>

        <?php if($section === 'profile'): ?>
            <div class="grid gap-6 lg:grid-cols-2">
                <section class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-5 sm:p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-white">Profile</h2>
                            <p class="mt-1 text-sm text-slate-400">Update your name, email, and profile picture.</p>
                        </div>
                        <span class="rounded-full border border-slate-700 bg-slate-950/50 px-3 py-1 text-xs font-semibold text-slate-300"><?php echo e($user->roleLabel()); ?></span>
                    </div>

                    <form method="POST" action="<?php echo e(route('profile.update')); ?>" enctype="multipart/form-data" class="mt-6 space-y-5" x-data="{ preview: <?php echo \Illuminate\Support\Js::from($user->avatarUrl())->toHtml() ?>, removeAvatar: false }">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PATCH'); ?>

                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                            <div class="relative h-20 w-20 shrink-0 overflow-hidden rounded-2xl border border-slate-700 bg-slate-950">
                                <template x-if="preview && !removeAvatar">
                                    <img :src="preview" alt="Profile picture" class="h-full w-full object-cover">
                                </template>
                                <template x-if="!preview || removeAvatar">
                                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-cyan-500 to-sky-400 text-2xl font-bold text-slate-950"><?php echo e($user->initials()); ?></div>
                                </template>
                            </div>
                            <div class="min-w-0 flex-1">
                                <label class="block text-sm font-medium text-slate-300"><?php echo e($isSuperAdmin ? 'Avatar' : 'Profile Picture'); ?></label>
                                <input
                                    type="file"
                                    name="avatar"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="mt-2 block w-full text-sm text-slate-400 file:mr-3 file:rounded-lg file:border-0 file:bg-cyan-500 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-950 hover:file:bg-cyan-400"
                                    @change="
                                        removeAvatar = false;
                                        const file = $event.target.files?.[0];
                                        preview = file ? URL.createObjectURL(file) : preview;
                                    "
                                />
                                <input type="hidden" name="remove_avatar" :value="removeAvatar ? 1 : 0">
                                <?php if($user->avatarUrl()): ?>
                                    <button type="button" class="mt-2 text-xs font-semibold text-rose-300 hover:text-rose-200" @click="removeAvatar = true; preview = null">Remove photo</button>
                                <?php endif; ?>
                                <?php $__errorArgs = ['avatar'];
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
                            <label for="name" class="block text-sm font-medium text-slate-300">Name</label>
                            <input id="name" name="name" type="text" value="<?php echo e(old('name', $user->name)); ?>" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100" />
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
                            <label for="email" class="block text-sm font-medium text-slate-300">Email</label>
                            <input id="email" name="email" type="email" value="<?php echo e(old('email', $user->email)); ?>" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100" />
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
                            <?php if (! ($isSuperAdmin)): ?>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300">Department</label>
                                    <input type="text" value="<?php echo e($departmentLabel); ?>" readonly class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-800 bg-slate-950/80 px-4 py-2 text-slate-400" />
                                </div>
                            <?php endif; ?>
                            <div>
                                <label class="block text-sm font-medium text-slate-300">Role</label>
                                <input type="text" value="<?php echo e($user->roleLabel()); ?>" readonly class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-800 bg-slate-950/80 px-4 py-2 text-slate-400" />
                            </div>
                        </div>

                        <button type="submit" class="rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:opacity-90">
                            Save changes
                        </button>
                    </form>
                </section>

                <?php
                    $adminSummaryRows = [
                        ['label' => 'Account ID', 'value' => $user->account_id ?? '—'],
                        ['label' => 'Role', 'value' => $user->roleLabel()],
                    ];
                    if (! $isSuperAdmin) {
                        $adminSummaryRows[] = ['label' => 'Department', 'value' => $departmentLabel];
                    }
                    $adminSummaryRows = array_merge($adminSummaryRows, [
                        ['label' => 'Registered Devices', 'value' => (string) $user->passkeys_count],
                        ['label' => 'Authentication', 'value' => 'Passwordless (Passkeys)', 'valueClass' => 'text-emerald-300'],
                        ['label' => 'Last Login', 'value' => optional($securityContext['at'] ?? null)->format('M d, Y g:i A') ?? '—'],
                    ]);
                ?>
                <?php if (isset($component)) { $__componentOriginal240716bf037491311dbd23ddc8e07de8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal240716bf037491311dbd23ddc8e07de8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.settings.account-summary','data' => ['rows' => $adminSummaryRows]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('settings.account-summary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['rows' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($adminSummaryRows)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal240716bf037491311dbd23ddc8e07de8)): ?>
<?php $attributes = $__attributesOriginal240716bf037491311dbd23ddc8e07de8; ?>
<?php unset($__attributesOriginal240716bf037491311dbd23ddc8e07de8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal240716bf037491311dbd23ddc8e07de8)): ?>
<?php $component = $__componentOriginal240716bf037491311dbd23ddc8e07de8; ?>
<?php unset($__componentOriginal240716bf037491311dbd23ddc8e07de8); ?>
<?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if($section === 'devices'): ?>
            <div class="grid gap-6 lg:grid-cols-2">
                <section class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-5 sm:p-6">
                    <h2 class="text-lg font-semibold text-white">Register New Passkey</h2>
                    <p class="mt-1 text-sm text-slate-400">Add another trusted device for passwordless administrator sign-in.</p>
                    <div class="mt-5 [&_.rounded-xl]:border-cyan-500/20 [&_.rounded-xl]:bg-slate-950/50 [&_p]:text-slate-300 [&_label]:text-slate-300 [&_input]:border-slate-700 [&_input]:bg-slate-950 [&_input]:text-slate-100">
                        <?php if (isset($component)) { $__componentOriginal15a615f1c082febb5f28527938415021 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal15a615f1c082febb5f28527938415021 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.passkey-register','data' => ['registerOptionsUrl' => route('register.passkey.options'),'registerVerifyUrl' => route('register.passkey.verify')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('passkey-register'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['register-options-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('register.passkey.options')),'register-verify-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('register.passkey.verify'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal15a615f1c082febb5f28527938415021)): ?>
<?php $attributes = $__attributesOriginal15a615f1c082febb5f28527938415021; ?>
<?php unset($__attributesOriginal15a615f1c082febb5f28527938415021); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal15a615f1c082febb5f28527938415021)): ?>
<?php $component = $__componentOriginal15a615f1c082febb5f28527938415021; ?>
<?php unset($__componentOriginal15a615f1c082febb5f28527938415021); ?>
<?php endif; ?>
                    </div>
                </section>

                <section class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-5 sm:p-6">
                    <h2 class="text-lg font-semibold text-white">Registered Devices</h2>
                    <p class="mt-1 text-sm text-slate-400">Credential IDs and public keys are never displayed.</p>

                    <ul
                        id="passkey-device-list"
                        class="mt-5 space-y-3"
                        data-index-url="<?php echo e(route('passkeys.index')); ?>"
                        data-update-url-template="<?php echo e(url('/user/passkeys/__ID__')); ?>"
                        data-csrf="<?php echo e(csrf_token()); ?>"
                    >
                        <?php $__empty_1 = true; $__currentLoopData = $passkeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $passkey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php if (isset($component)) { $__componentOriginal6dc722ea16a74ac6614a901af2b376f8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6dc722ea16a74ac6614a901af2b376f8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.settings.device-card','data' => ['passkey' => $passkey]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('settings.device-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['passkey' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($passkey)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6dc722ea16a74ac6614a901af2b376f8)): ?>
<?php $attributes = $__attributesOriginal6dc722ea16a74ac6614a901af2b376f8; ?>
<?php unset($__attributesOriginal6dc722ea16a74ac6614a901af2b376f8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6dc722ea16a74ac6614a901af2b376f8)): ?>
<?php $component = $__componentOriginal6dc722ea16a74ac6614a901af2b376f8; ?>
<?php unset($__componentOriginal6dc722ea16a74ac6614a901af2b376f8); ?>
<?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <li>
                                <?php if (isset($component)) { $__componentOriginal67bef064234bc89558edfdc95407152e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal67bef064234bc89558edfdc95407152e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.settings.empty-state','data' => ['title' => 'No authentication devices registered','description' => 'Register a passkey to secure administrator access to the portal.','actionLabel' => 'Register Passkey','actionHref' => route('profile.edit', ['section' => 'devices']).'#register-passkey-btn']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('settings.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'No authentication devices registered','description' => 'Register a passkey to secure administrator access to the portal.','action-label' => 'Register Passkey','action-href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('profile.edit', ['section' => 'devices']).'#register-passkey-btn')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal67bef064234bc89558edfdc95407152e)): ?>
<?php $attributes = $__attributesOriginal67bef064234bc89558edfdc95407152e; ?>
<?php unset($__attributesOriginal67bef064234bc89558edfdc95407152e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal67bef064234bc89558edfdc95407152e)): ?>
<?php $component = $__componentOriginal67bef064234bc89558edfdc95407152e; ?>
<?php unset($__componentOriginal67bef064234bc89558edfdc95407152e); ?>
<?php endif; ?>
                            </li>
                        <?php endif; ?>
                    </ul>
                </section>
            </div>
            <?php echo app('Illuminate\Foundation\Vite')('resources/js/passkey-devices.js'); ?>
        <?php endif; ?>

        <?php if($section === 'security'): ?>
            <div class="space-y-6">
                <?php if (isset($component)) { $__componentOriginal62dbacd692c711ea598dbab1f13617fd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal62dbacd692c711ea598dbab1f13617fd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.settings.security-card','data' => ['title' => 'Authentication Status','description' => 'Passkey authentication is required for portal access.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('settings.security-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Authentication Status','description' => 'Passkey authentication is required for portal access.']); ?>
                     <?php $__env->slot('actions', null, []); ?> 
                        <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'rounded-full border px-3 py-1 text-xs font-semibold',
                            'border-emerald-500/30 bg-emerald-500/10 text-emerald-200' => $passwordlessEnabled,
                            'border-amber-500/30 bg-amber-500/10 text-amber-200' => ! $passwordlessEnabled,
                        ]); ?>">
                            <?php echo e($passwordlessEnabled ? 'Enabled' : 'Disabled'); ?>

                        </span>
                     <?php $__env->endSlot(); ?>

                    <dl class="grid gap-3 text-sm sm:grid-cols-2 <?php echo e($isSuperAdmin ? 'lg:grid-cols-4' : ''); ?>">
                        <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                            <dt class="text-xs text-slate-500">Require Passkey Authentication</dt>
                            <dd class="mt-1 font-medium text-emerald-300">Required</dd>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                            <dt class="text-xs text-slate-500">Last Login</dt>
                            <dd class="mt-1 font-medium text-slate-200"><?php echo e(optional($securityContext['at'] ?? null)->format('M d, Y g:i A') ?? '—'); ?></dd>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                            <dt class="text-xs text-slate-500">Last Login IP</dt>
                            <dd class="mt-1 font-medium text-slate-200"><?php echo e($securityContext['ip'] ?? '—'); ?></dd>
                        </div>
                        <?php if($isSuperAdmin): ?>
                            <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                                <dt class="text-xs text-slate-500">Registered Devices</dt>
                                <dd class="mt-1 font-medium text-slate-200"><?php echo e($user->passkeys_count); ?></dd>
                            </div>
                            <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                                <dt class="text-xs text-slate-500">Trusted Devices</dt>
                                <dd class="mt-1 font-medium text-slate-200"><?php echo e($trustedDeviceCount); ?></dd>
                            </div>
                            <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                                <dt class="text-xs text-slate-500">Last Authentication</dt>
                                <dd class="mt-1 font-medium text-slate-200"><?php echo e(optional($lastAuthentication->last_used_at ?? null)->diffForHumans() ?? '—'); ?></dd>
                            </div>
                        <?php endif; ?>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3 sm:col-span-2">
                            <dt class="text-xs text-slate-500"><?php echo e($isSuperAdmin ? 'Emergency Recovery Email' : 'Recovery Email'); ?></dt>
                            <dd class="mt-1 font-medium text-slate-200"><?php echo e($user->email ?: '—'); ?></dd>
                        </div>
                    </dl>

                    <div class="mt-5 flex flex-wrap gap-3">
                        <a href="<?php echo e(route('profile.edit', ['section' => 'devices'])); ?>" class="rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:opacity-90">
                            Manage Passkeys
                        </a>
                    </div>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal62dbacd692c711ea598dbab1f13617fd)): ?>
<?php $attributes = $__attributesOriginal62dbacd692c711ea598dbab1f13617fd; ?>
<?php unset($__attributesOriginal62dbacd692c711ea598dbab1f13617fd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal62dbacd692c711ea598dbab1f13617fd)): ?>
<?php $component = $__componentOriginal62dbacd692c711ea598dbab1f13617fd; ?>
<?php unset($__componentOriginal62dbacd692c711ea598dbab1f13617fd); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal62dbacd692c711ea598dbab1f13617fd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal62dbacd692c711ea598dbab1f13617fd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.settings.security-card','data' => ['title' => 'Active Sessions','description' => 'Devices currently signed in with your administrator credentials.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('settings.security-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Active Sessions','description' => 'Devices currently signed in with your administrator credentials.']); ?>
                     <?php $__env->slot('actions', null, []); ?> 
                        <form method="POST" action="<?php echo e(route('profile.logout-other-sessions')); ?>" onsubmit="return confirm('Sign out of every other device? You will stay signed in on this device.')">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="rounded-xl border border-rose-500/30 px-4 py-2 text-sm font-semibold text-rose-200 hover:bg-rose-500/10">
                                Logout Other Devices
                            </button>
                        </form>
                     <?php $__env->endSlot(); ?>

                    <ul class="space-y-3">
                        <?php $__empty_1 = true; $__currentLoopData = $activeSessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php if (isset($component)) { $__componentOriginal32762cd2e627a151fa721c4484f7573f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal32762cd2e627a151fa721c4484f7573f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.settings.session-card','data' => ['session' => $session]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('settings.session-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['session' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($session)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal32762cd2e627a151fa721c4484f7573f)): ?>
<?php $attributes = $__attributesOriginal32762cd2e627a151fa721c4484f7573f; ?>
<?php unset($__attributesOriginal32762cd2e627a151fa721c4484f7573f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal32762cd2e627a151fa721c4484f7573f)): ?>
<?php $component = $__componentOriginal32762cd2e627a151fa721c4484f7573f; ?>
<?php unset($__componentOriginal32762cd2e627a151fa721c4484f7573f); ?>
<?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <li class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3 text-sm text-slate-500">No active sessions found.</li>
                        <?php endif; ?>
                    </ul>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal62dbacd692c711ea598dbab1f13617fd)): ?>
<?php $attributes = $__attributesOriginal62dbacd692c711ea598dbab1f13617fd; ?>
<?php unset($__attributesOriginal62dbacd692c711ea598dbab1f13617fd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal62dbacd692c711ea598dbab1f13617fd)): ?>
<?php $component = $__componentOriginal62dbacd692c711ea598dbab1f13617fd; ?>
<?php unset($__componentOriginal62dbacd692c711ea598dbab1f13617fd); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal62dbacd692c711ea598dbab1f13617fd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal62dbacd692c711ea598dbab1f13617fd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.settings.security-card','data' => ['title' => ''.e($isSuperAdmin ? 'Recent Login History' : 'Login History').'','description' => ''.e($isSuperAdmin ? 'Your most recent successful authentication events.' : 'Last 10 successful sign-ins to your account.').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('settings.security-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e($isSuperAdmin ? 'Recent Login History' : 'Login History').'','description' => ''.e($isSuperAdmin ? 'Your most recent successful authentication events.' : 'Last 10 successful sign-ins to your account.').'']); ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="border-b border-slate-800 text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-2 py-2 font-semibold">Date & Time</th>
                                    <th class="px-2 py-2 font-semibold">Browser</th>
                                    <th class="px-2 py-2 font-semibold">Device</th>
                                    <th class="px-2 py-2 font-semibold">IP</th>
                                    <th class="px-2 py-2 font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800">
                                <?php $__empty_1 = true; $__currentLoopData = $loginHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td class="px-2 py-3 text-slate-300"><?php echo e(optional($entry['occurred_at'])->format('M d, Y g:i A') ?? '—'); ?></td>
                                        <td class="px-2 py-3 text-slate-300"><?php echo e($entry['browser']); ?></td>
                                        <td class="px-2 py-3 text-slate-300"><?php echo e($entry['device']); ?></td>
                                        <td class="px-2 py-3 text-slate-300"><?php echo e($entry['ip_address'] ?? '—'); ?></td>
                                        <td class="px-2 py-3">
                                            <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-xs font-semibold text-emerald-200"><?php echo e($entry['status']); ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="5" class="px-2 py-4 text-slate-500">No login history available yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal62dbacd692c711ea598dbab1f13617fd)): ?>
<?php $attributes = $__attributesOriginal62dbacd692c711ea598dbab1f13617fd; ?>
<?php unset($__attributesOriginal62dbacd692c711ea598dbab1f13617fd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal62dbacd692c711ea598dbab1f13617fd)): ?>
<?php $component = $__componentOriginal62dbacd692c711ea598dbab1f13617fd; ?>
<?php unset($__componentOriginal62dbacd692c711ea598dbab1f13617fd); ?>
<?php endif; ?>

                <?php if($isSuperAdmin): ?>
                    <?php if (isset($component)) { $__componentOriginal62dbacd692c711ea598dbab1f13617fd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal62dbacd692c711ea598dbab1f13617fd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.settings.security-card','data' => ['title' => 'System Access History','description' => 'Recent privileged actions attributed to your super administrator account.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('settings.security-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'System Access History','description' => 'Recent privileged actions attributed to your super administrator account.']); ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="border-b border-slate-800 text-xs uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th class="px-2 py-2 font-semibold">Date & Time</th>
                                        <th class="px-2 py-2 font-semibold">Action</th>
                                        <th class="px-2 py-2 font-semibold">Type</th>
                                        <th class="px-2 py-2 font-semibold">IP</th>
                                        <th class="px-2 py-2 font-semibold">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800">
                                    <?php $__empty_1 = true; $__currentLoopData = $systemAccessHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td class="px-2 py-3 text-slate-300"><?php echo e(optional($entry['occurred_at'])->format('M d, Y g:i A') ?? '—'); ?></td>
                                            <td class="px-2 py-3 text-slate-300"><?php echo e($entry['action']); ?></td>
                                            <td class="px-2 py-3 text-slate-300"><?php echo e($entry['type']); ?></td>
                                            <td class="px-2 py-3 text-slate-300"><?php echo e($entry['ip_address'] ?? '—'); ?></td>
                                            <td class="px-2 py-3">
                                                <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-xs font-semibold text-emerald-200"><?php echo e($entry['status']); ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="5" class="px-2 py-4 text-slate-500">No system access history available yet.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal62dbacd692c711ea598dbab1f13617fd)): ?>
<?php $attributes = $__attributesOriginal62dbacd692c711ea598dbab1f13617fd; ?>
<?php unset($__attributesOriginal62dbacd692c711ea598dbab1f13617fd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal62dbacd692c711ea598dbab1f13617fd)): ?>
<?php $component = $__componentOriginal62dbacd692c711ea598dbab1f13617fd; ?>
<?php unset($__componentOriginal62dbacd692c711ea598dbab1f13617fd); ?>
<?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/profile/edit-admin.blade.php ENDPATH**/ ?>
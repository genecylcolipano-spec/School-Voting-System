<?php
    $section = in_array($section ?? 'profile', ['profile', 'devices', 'security'], true)
        ? ($section ?? 'profile')
        : 'profile';
    $portalComponent = $portalComponent ?? 'student-portal';
    $isFacultyPortal = $portalComponent === 'faculty-portal';
    $isStudentProfile = $user->isStudent();
    $accentBorder = $isFacultyPortal ? 'border-teal-500/15' : 'border-cyan-500/15';
    $accent = $isFacultyPortal ? 'teal' : 'cyan';
    $accountStatus = $accountStatus ?? (filled($user->archived_at ?? null) ? 'Archived' : ($user->is_active ? 'Active' : 'Inactive'));
    $passwordlessEnabled = $passwordlessEnabled ?? ($user->passkeys_count > 0);
    $securityContext = $securityContext ?? [];
    $navClass = fn (string $key) => 'rounded-xl px-4 py-2 text-sm font-semibold transition '.($section === $key
        ? ($isFacultyPortal
            ? 'bg-teal-500/20 text-teal-100 ring-1 ring-teal-500/30'
            : 'bg-cyan-500/20 text-cyan-100 ring-1 ring-cyan-500/30')
        : 'text-slate-400 hover:bg-slate-800/70 hover:text-white');

    $summaryRows = [
        ['label' => $isStudentProfile ? 'Student ID' : 'Account ID', 'value' => $user->account_id ?? '—'],
    ];

    if ($isFacultyPortal) {
        $summaryRows[] = [
            'label' => 'Assigned Competitions',
            'value' => ($user->judging_assignments_count ?? 0) > 0
                ? (string) $user->judging_assignments_count
                : 'None Assigned',
        ];
    } elseif ($isStudentProfile) {
        $summaryRows[] = [
            'label' => 'Grade & Section',
            'value' => trim(($user->grade_level ?: '—').' · '.($user->section ?: '—')),
        ];
    }

    $summaryRows = array_merge($summaryRows, [
        ['label' => 'Registered Devices', 'value' => (string) $user->passkeys_count],
        ['label' => 'Authentication Method', 'value' => 'Passwordless (Passkeys)', 'valueClass' => 'text-emerald-300'],
        [
            'label' => 'Last Login',
            'value' => optional($securityContext['at'] ?? null)->format('M d, Y g:i A') ?? '—',
        ],
        [
            'label' => 'Member Since',
            'value' => optional($user->created_at)->format('M d, Y') ?? '—',
        ],
    ]);

    if ($isFacultyPortal) {
        $summaryRows[] = [
            'label' => 'Account Status',
            'value' => $accountStatus,
            'valueClass' => match ($accountStatus) {
                'Active' => 'text-emerald-300',
                'Inactive' => 'text-amber-300',
                default => 'text-slate-400',
            },
        ];
    }
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
    <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => $portalComponent] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Settings','user' => $user,'notifications-count' => $notificationsCount]); ?>
        <div class="mb-6">
            <h1 class="text-xl font-bold text-white">Settings</h1>
            <p class="mt-1 text-sm text-slate-400">Manage your profile, authentication devices, and account security.</p>
        </div>

        <nav class="mb-6 flex flex-wrap gap-2" aria-label="Settings sections">
            <a href="<?php echo e(route('profile.edit', ['section' => 'profile'])); ?>" class="<?php echo e($navClass('profile')); ?>">My Profile</a>
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
                <section class="rounded-2xl border <?php echo e($accentBorder); ?> bg-slate-900/70 p-5 sm:p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-white">My Profile</h2>
                            <p class="mt-1 text-sm text-slate-400">Update your personal account details.</p>
                        </div>
                        <span class="rounded-full border border-slate-700 bg-slate-950/50 px-3 py-1 text-xs font-semibold text-slate-300"><?php echo e($user->roleLabel()); ?></span>
                    </div>

                    <form method="POST" action="<?php echo e(route('profile.update')); ?>" enctype="multipart/form-data" class="mt-6 space-y-5" x-data="{ preview: <?php echo \Illuminate\Support\Js::from($user->avatarUrl())->toHtml() ?>, removeAvatar: false }">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PATCH'); ?>

                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                            <div class="relative h-20 w-20 shrink-0 overflow-hidden rounded-2xl border border-slate-700 bg-slate-950">
                                <template x-if="preview && !removeAvatar">
                                    <img :src="preview" alt="Profile photo" class="h-full w-full object-cover">
                                </template>
                                <template x-if="!preview || removeAvatar">
                                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-cyan-500 to-sky-400 text-2xl font-bold text-slate-950"><?php echo e($user->initials()); ?></div>
                                </template>
                            </div>
                            <div class="min-w-0 flex-1">
                                <label class="block text-sm font-medium text-slate-300">Profile Photo</label>
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
                                <p class="mt-1 text-xs text-slate-500">JPG, PNG, or WEBP. Max 2 MB.</p>
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
                            <label for="name" class="block text-sm font-medium text-slate-300">Full Name</label>
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

                        <div>
                            <label for="phone" class="block text-sm font-medium text-slate-300">Phone Number <span class="text-slate-500">(optional)</span></label>
                            <input id="phone" name="phone" type="text" value="<?php echo e(old('phone', $user->phone)); ?>" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100" placeholder="+63…" />
                            <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <?php if($isStudentProfile): ?>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-slate-300">Student ID</label>
                                    <input type="text" value="<?php echo e($user->account_id ?: '—'); ?>" readonly class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-800 bg-slate-950/80 px-4 py-2 text-slate-400" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300">Course</label>
                                    <input type="text" value="—" readonly class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-800 bg-slate-950/80 px-4 py-2 text-slate-400" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300">Grade</label>
                                    <input type="text" value="<?php echo e($user->grade_level ?: '—'); ?>" readonly class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-800 bg-slate-950/80 px-4 py-2 text-slate-400" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300">Section</label>
                                    <input type="text" value="<?php echo e($user->section ?: '—'); ?>" readonly class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-800 bg-slate-950/80 px-4 py-2 text-slate-400" />
                                </div>
                            </div>
                            <p class="text-xs text-slate-500">Student ID, grade, section, and course are managed by the school administration.</p>
                        <?php endif; ?>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-slate-300">Role</label>
                                <input type="text" value="<?php echo e($user->roleLabel()); ?>" readonly class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-800 bg-slate-950/80 px-4 py-2 text-slate-400" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300">Account Status</label>
                                <input type="text" value="<?php echo e($accountStatus); ?>" readonly class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-800 bg-slate-950/80 px-4 py-2 text-slate-400" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300">Account Created</label>
                                <input type="text" value="<?php echo e(optional($user->created_at)->format('M d, Y') ?? '—'); ?>" readonly class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-800 bg-slate-950/80 px-4 py-2 text-slate-400" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300">Last Login</label>
                                <input type="text" value="<?php echo e(optional($securityContext['at'] ?? null)->format('M d, Y g:i A') ?? '—'); ?>" readonly class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-800 bg-slate-950/80 px-4 py-2 text-slate-400" />
                            </div>
                        </div>

                        <button type="submit" class="rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:opacity-90">
                            Save Profile
                        </button>
                    </form>
                </section>

                <?php if (isset($component)) { $__componentOriginal240716bf037491311dbd23ddc8e07de8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal240716bf037491311dbd23ddc8e07de8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.settings.account-summary','data' => ['rows' => $summaryRows,'borderClass' => $accentBorder]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('settings.account-summary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['rows' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summaryRows),'border-class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($accentBorder)]); ?>
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
                <section class="rounded-2xl border <?php echo e($accentBorder); ?> bg-slate-900/70 p-5 sm:p-6">
                    <h2 class="text-lg font-semibold text-white">Register New Passkey</h2>
                    <p class="mt-1 text-sm text-slate-400">Add another device for passwordless sign-in. Credential secrets are never stored or displayed.</p>
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

                <section class="rounded-2xl border <?php echo e($accentBorder); ?> bg-slate-900/70 p-5 sm:p-6">
                    <h2 class="text-lg font-semibold text-white">Registered Devices</h2>
                    <p class="mt-1 text-sm text-slate-400">Only device metadata is shown. You must keep at least one device registered.</p>

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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.settings.device-card','data' => ['passkey' => $passkey,'accent' => $accent]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('settings.device-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['passkey' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($passkey),'accent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($accent)]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.settings.empty-state','data' => ['title' => 'No authentication devices registered','description' => 'Register a passkey on this device to enable secure passwordless sign-in.','actionLabel' => 'Register Passkey','actionHref' => route('profile.edit', ['section' => 'devices']).'#register-passkey-btn']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('settings.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'No authentication devices registered','description' => 'Register a passkey on this device to enable secure passwordless sign-in.','action-label' => 'Register Passkey','action-href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('profile.edit', ['section' => 'devices']).'#register-passkey-btn')]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.settings.security-card','data' => ['title' => 'Passwordless Authentication Status','description' => 'Your account uses passkeys for passwordless access.','borderClass' => $accentBorder]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('settings.security-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Passwordless Authentication Status','description' => 'Your account uses passkeys for passwordless access.','border-class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($accentBorder)]); ?>
                     <?php $__env->slot('actions', null, []); ?> 
                        <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'rounded-full border px-3 py-1 text-xs font-semibold',
                            'border-emerald-500/30 bg-emerald-500/10 text-emerald-200' => $passwordlessEnabled,
                            'border-amber-500/30 bg-amber-500/10 text-amber-200' => ! $passwordlessEnabled,
                        ]); ?>">
                            <?php echo e($passwordlessEnabled ? 'Enabled' : 'Disabled'); ?>

                        </span>
                     <?php $__env->endSlot(); ?>

                    <div class="flex flex-wrap gap-3">
                        <a href="<?php echo e(route('profile.edit', ['section' => 'devices'])); ?>" class="rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:opacity-90">
                            Register Passkey
                        </a>
                        <a href="<?php echo e(route('profile.edit', ['section' => 'devices'])); ?>" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-slate-800/70">
                            Manage Passkeys
                        </a>
                    </div>

                    <dl class="mt-5 grid gap-3 text-sm sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                            <dt class="text-xs text-slate-500">Last Login</dt>
                            <dd class="mt-1 font-medium text-slate-200"><?php echo e(optional($securityContext['at'] ?? null)->format('M d, Y g:i A') ?? '—'); ?></dd>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                            <dt class="text-xs text-slate-500">Last Login IP</dt>
                            <dd class="mt-1 font-medium text-slate-200"><?php echo e($securityContext['ip'] ?? '—'); ?></dd>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                            <dt class="text-xs text-slate-500">Browser Used</dt>
                            <dd class="mt-1 font-medium text-slate-200"><?php echo e($securityContext['browser'] ?? '—'); ?></dd>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                            <dt class="text-xs text-slate-500">Operating System</dt>
                            <dd class="mt-1 font-medium text-slate-200"><?php echo e($securityContext['os'] ?? '—'); ?></dd>
                        </div>
                    </dl>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.settings.security-card','data' => ['title' => 'Active Sessions','description' => 'Devices currently signed in to your account.','borderClass' => $accentBorder]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('settings.security-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Active Sessions','description' => 'Devices currently signed in to your account.','border-class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($accentBorder)]); ?>
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

                <?php if($isStudentProfile): ?>
                    <div
                        x-data="{ confirmDelete: <?php echo e($errors->userDeletion->isNotEmpty() ? 'true' : 'false'); ?> }"
                        class="rounded-2xl border border-rose-500/25 bg-slate-900/70 p-5 sm:p-6"
                    >
                        <h2 class="text-lg font-semibold text-white">Danger Zone</h2>
                        <p class="mt-1 text-sm text-slate-400">Permanently delete your student account and associated portal data.</p>

                        <button
                            type="button"
                            class="mt-5 rounded-xl border border-rose-500/40 bg-rose-500/10 px-4 py-2 text-sm font-semibold text-rose-200 hover:bg-rose-500/20"
                            @click="confirmDelete = true"
                        >
                            Delete Account
                        </button>

                        <div
                            x-show="confirmDelete"
                            x-cloak
                            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 px-4"
                            @keydown.escape.window="confirmDelete = false"
                        >
                            <form method="post" action="<?php echo e(route('profile.destroy')); ?>" class="w-full max-w-md space-y-4 rounded-2xl border border-slate-700 bg-slate-900 p-6 shadow-xl" @click.outside="confirmDelete = false">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('delete'); ?>

                                <h3 class="text-lg font-semibold text-white">Delete your account?</h3>
                                <p class="text-sm text-slate-400">
                                    This action cannot be undone. Type <span class="font-semibold text-rose-300">DELETE</span> to confirm.
                                </p>

                                <div>
                                    <label for="confirmation" class="block text-sm font-medium text-slate-300">Confirmation</label>
                                    <input
                                        id="confirmation"
                                        name="confirmation"
                                        type="text"
                                        autocomplete="off"
                                        class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100"
                                        placeholder="DELETE"
                                    />
                                    <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->userDeletion->get('confirmation'),'class' => 'mt-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->userDeletion->get('confirmation')),'class' => 'mt-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $attributes = $__attributesOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__attributesOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $component = $__componentOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__componentOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
                                </div>

                                <div class="flex justify-end gap-3">
                                    <button type="button" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-200" @click="confirmDelete = false">
                                        Cancel
                                    </button>
                                    <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">
                                        Delete Account
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/profile/edit-student.blade.php ENDPATH**/ ?>
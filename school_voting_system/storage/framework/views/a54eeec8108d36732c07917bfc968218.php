<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Register Passkey — <?php echo e(\App\Support\SchoolBranding::systemName()); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/passkey-register.js']); ?>
    <script>
        if (window.location.hostname === '127.0.0.1') {
            const port = window.location.port ? ':' + window.location.port : '';
            window.location.replace(window.location.protocol + '//localhost' + port + window.location.pathname + window.location.search);
        }
    </script>
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-8 shadow-lg">
        <h1 class="text-xl font-bold text-slate-900">Register your passkey</h1>

        <?php if($user): ?>
            <p class="mt-2 text-sm text-slate-600">
                Setting up passwordless access for <strong><?php echo e($user->name); ?></strong> (<?php echo e($user->account_id); ?>).
            </p>
            <p class="mt-1 text-xs font-medium uppercase tracking-wide text-cyan-700">
                Role: <?php echo e(str($user->role?->value ?? 'unknown')->title()); ?>

            </p>
            <?php if(session()->has(\App\Services\Auth\PasskeyRecoveryTokenService::SESSION_RECOVERY_REQUEST_ID)): ?>
                <p class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">
                    Registering a new passkey will revoke your previous passkeys on this account.
                </p>
            <?php endif; ?>
        <?php elseif(! empty($pending)): ?>
            <p class="mt-2 text-sm text-slate-600">
                Setting up passwordless access for
                <strong><?php echo e(trim(($pending['first_name'] ?? '').' '.($pending['last_name'] ?? ''))); ?></strong>
                (<?php echo e($pending['account_id'] ?? ''); ?>).
            </p>
            <p class="mt-1 text-xs font-medium uppercase tracking-wide text-cyan-700">
                Student registration
            </p>
        <?php endif; ?>

        <?php if(session('status')): ?>
            <p class="mt-4 rounded-lg border border-cyan-200 bg-cyan-50 px-3 py-2 text-sm text-cyan-800"><?php echo e(session('status')); ?></p>
        <?php endif; ?>

        <div class="mt-6">
            <?php if (isset($component)) { $__componentOriginal15a615f1c082febb5f28527938415021 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal15a615f1c082febb5f28527938415021 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.passkey-register','data' => ['registerOptionsUrl' => $registerOptionsUrl,'registerVerifyUrl' => $registerVerifyUrl]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('passkey-register'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['register-options-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($registerOptionsUrl),'register-verify-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($registerVerifyUrl)]); ?>
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
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/auth/enroll-passkey.blade.php ENDPATH**/ ?>
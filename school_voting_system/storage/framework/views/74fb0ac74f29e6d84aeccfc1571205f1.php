<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Register — <?php echo e(\App\Support\SchoolBranding::systemName()); ?></title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css']); ?>
</head>
<body class="min-h-screen bg-slate-950 font-[Instrument_Sans] text-slate-100 antialiased">
    <div class="flex min-h-screen items-center justify-center px-4 py-12">
        <div class="w-full max-w-md rounded-2xl border border-white/10 bg-white/5 p-8 shadow-2xl backdrop-blur-xl">
            <?php if($registerLogo = \App\Support\SchoolBranding::logoUrl(withFallback: false)): ?>
                <img src="<?php echo e($registerLogo); ?>" alt="<?php echo e(\App\Support\SchoolBranding::schoolName()); ?>" class="mb-4 h-14 w-14 rounded-xl border border-white/10 object-cover">
            <?php else: ?>
                <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-xl border border-white/10 bg-gradient-to-br from-cyan-500 to-sky-400 text-slate-950" aria-hidden="true">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
            <?php endif; ?>
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-cyan-300/80"><?php echo e(\App\Support\SchoolBranding::systemName()); ?></p>
            <?php if($poweredBy = \App\Support\SchoolBranding::poweredBy()): ?>
                <p class="mt-1 text-xs text-slate-500"><?php echo e($poweredBy); ?></p>
            <?php endif; ?>
            <h1 class="mt-2 text-2xl font-bold text-white">Create portal account</h1>
            <p class="mt-2 text-sm text-slate-400">
                <?php echo e(\App\Support\SchoolBranding::periodLabel()); ?> · Confirm your roster details first. A secure passkey setup link (valid <?php echo e($expirationHours ?? 24); ?> hours) is sent only after verification.
            </p>

            <?php if(session('status')): ?>
                <p class="mt-4 rounded-lg border border-cyan-500/30 bg-cyan-500/10 px-3 py-2 text-sm text-cyan-100"><?php echo e(session('status')); ?></p>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('register.store')); ?>" class="mt-6 space-y-4">
                <?php echo csrf_field(); ?>

                <div>
                    <label for="account_id" class="block text-sm font-medium text-slate-300">Account ID</label>
                    <input id="account_id" name="account_id" type="text" value="<?php echo e(old('account_id')); ?>" required
                        placeholder="e.g. 600045 or ADMIN-001"
                        class="mt-1 w-full rounded-xl border border-white/10 bg-slate-900/80 px-4 py-3 text-white focus:border-cyan-400/60 focus:outline-none focus:ring-2 focus:ring-cyan-500/20">
                    <?php $__errorArgs = ['account_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-slate-300">First Name</label>
                        <input id="first_name" name="first_name" type="text" value="<?php echo e(old('first_name')); ?>" required autocomplete="given-name"
                            class="mt-1 w-full rounded-xl border border-white/10 bg-slate-900/80 px-4 py-3 text-white focus:border-cyan-400/60 focus:outline-none focus:ring-2 focus:ring-cyan-500/20">
                        <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label for="last_name" class="block text-sm font-medium text-slate-300">Last Name</label>
                        <input id="last_name" name="last_name" type="text" value="<?php echo e(old('last_name')); ?>" required autocomplete="family-name"
                            class="mt-1 w-full rounded-xl border border-white/10 bg-slate-900/80 px-4 py-3 text-white focus:border-cyan-400/60 focus:outline-none focus:ring-2 focus:ring-cyan-500/20">
                        <?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300">Email Address</label>
                    <input id="email" name="email" type="email" value="<?php echo e(old('email')); ?>" required
                        class="mt-1 w-full rounded-xl border border-white/10 bg-slate-900/80 px-4 py-3 text-white focus:border-cyan-400/60 focus:outline-none focus:ring-2 focus:ring-cyan-500/20">
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <button type="submit"
                    class="w-full rounded-xl bg-cyan-500 py-3 text-sm font-semibold text-slate-950 hover:bg-cyan-400">
                    Confirm &amp; Validate
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500">
                Already have a passkey?
                <a href="<?php echo e($loginUrl); ?>" class="text-cyan-300 hover:text-cyan-200">Sign in</a>
            </p>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/auth/register.blade.php ENDPATH**/ ?>
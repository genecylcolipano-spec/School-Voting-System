<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Passkey Recovery — <?php echo e(\App\Support\SchoolBranding::systemName()); ?></title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/passkey-recovery.js']); ?>
    <script>
        if (window.location.hostname === '127.0.0.1' || window.location.hostname === '[::1]') {
            const port = window.location.port ? ':' + window.location.port : '';
            window.location.replace(window.location.protocol + '//localhost' + port + window.location.pathname + window.location.search);
        }
    </script>
</head>
<body class="min-h-screen overflow-x-hidden bg-slate-950 font-[Instrument_Sans] text-slate-100 antialiased">
    <div class="relative flex min-h-screen items-center justify-center px-4 py-8 sm:py-12">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(56,189,248,0.16),_transparent_55%)]"></div>

        <div class="relative w-[calc(100%-32px)] max-w-[440px] min-w-0 rounded-2xl border border-white/10 bg-white/5 p-6 shadow-2xl backdrop-blur-xl sm:p-8">
            <h1 class="text-center text-2xl font-bold text-white sm:text-3xl">Lost your passkey?</h1>
            <p class="mt-3 text-center text-sm leading-relaxed text-slate-400">
                Request a secure recovery link to register a new passkey on this or another device.
            </p>

                <?php if(session('status')): ?>
                    <div class="mt-6 break-words rounded-xl border border-cyan-400/30 bg-cyan-500/10 px-4 py-3 text-sm text-cyan-100" role="status">
                        <?php echo e(session('status')); ?>

                    </div>
                <?php endif; ?>

                <?php if(session('error')): ?>
                    <div class="mt-6 break-words rounded-xl border border-amber-400/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-100" role="alert">
                        <?php echo e(session('error')); ?>

                    </div>
                <?php endif; ?>

                <div id="recovery-status" class="mt-6 hidden break-words rounded-xl border px-4 py-3 text-sm" role="status" aria-live="polite"></div>

                <form
                    id="recovery-form"
                    class="mt-6"
                    method="POST"
                    action="<?php echo e(route('login.recovery.request')); ?>"
                    data-url="<?php echo e(route('login.recovery.request')); ?>"
                >
                    <?php echo csrf_field(); ?>

                    <div>
                        <label for="account_id" class="block text-sm font-medium text-slate-200">Account ID</label>
                        <input
                            id="account_id"
                            name="account_id"
                            type="text"
                            required
                            autocomplete="username"
                            inputmode="text"
                            autocapitalize="none"
                            spellcheck="false"
                            value="<?php echo e(old('account_id')); ?>"
                            placeholder="e.g. 600045 "
                            class="mt-2 w-full min-w-0 rounded-xl border border-white/10 bg-slate-950/60 px-3 py-3 text-base text-slate-100 placeholder:text-slate-500 focus:border-cyan-400/60 focus:outline-none focus:ring-2 focus:ring-cyan-300/40"
                        >
                        <?php $__errorArgs = ['account_id'];
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

                    <div class="mt-5">
                        <label for="email" class="block text-sm font-medium text-slate-200">Email Address</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            required
                            autocomplete="email"
                            inputmode="email"
                            autocapitalize="none"
                            spellcheck="false"
                            value="<?php echo e(old('email')); ?>"
                            placeholder="e.g. name@gmail.com"
                            class="mt-2 w-full min-w-0 rounded-xl border border-white/10 bg-slate-950/60 px-3 py-3 text-base text-slate-100 placeholder:text-slate-500 focus:border-cyan-400/60 focus:outline-none focus:ring-2 focus:ring-cyan-300/40"
                        >
                        <?php $__errorArgs = ['email'];
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

                    <p class="mt-5 text-center text-xs leading-relaxed text-slate-500">
                        The recovery link expires after <?php echo e($resetExpirationMinutes); ?> minutes and can only be used once.
                    </p>

                    <button
                        type="submit"
                        class="mt-4 flex w-full items-center justify-center gap-3 rounded-xl bg-cyan-500 px-4 py-4 text-base font-semibold text-slate-950 transition hover:bg-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-300/60 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <span id="recovery-submit-label">Request Passkey Recovery</span>
                        <svg id="recovery-spinner" class="hidden h-5 w-5 shrink-0 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 100 16v-4l-3 3 3 3v-4a8 8 0 01-8-8z"></path>
                        </svg>
                    </button>
                </form>

                <div class="mt-6 border-t border-white/10 pt-5 text-center">
                    <p class="text-sm font-medium text-slate-300">Need help?</p>
                    <p class="mt-1 text-xs leading-relaxed text-slate-500">
                        Contact your school administrator at
                        <a href="mailto:<?php echo e($supportEmail); ?>" class="break-all text-cyan-300/80 underline hover:text-cyan-200"><?php echo e($supportEmail); ?></a>
                    </p>
                    <p class="mt-5">
                        <a href="<?php echo e($loginUrl); ?>" class="text-xs text-slate-400 transition hover:text-slate-300">Back to Login</a>
                    </p>
                </div>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/auth/recovery.blade.php ENDPATH**/ ?>
<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e(\App\Support\SchoolBranding::systemName()); ?> — Passkey Portal</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/passkey-auth.js']); ?>
    <script>
        if (window.location.hostname === '127.0.0.1' || window.location.hostname === '[::1]') {
            const port = window.location.port ? ':' + window.location.port : '';
            window.location.replace(window.location.protocol + '//localhost' + port + window.location.pathname + window.location.search);
        }
    </script>
</head>
<body class="min-h-screen bg-slate-950 font-[Instrument_Sans] text-slate-100 antialiased">
    <div class="relative flex min-h-screen items-center justify-center px-4 py-12">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(56,189,248,0.16),_transparent_55%)]"></div>

        <div class="relative w-full max-w-md">
            <div class="mb-8 text-center">
                <?php if($loginLogo = \App\Support\SchoolBranding::logoUrl(withFallback: false)): ?>
                    <img src="<?php echo e($loginLogo); ?>" alt="<?php echo e(\App\Support\SchoolBranding::schoolName()); ?>" class="mx-auto h-16 w-16 rounded-2xl border border-white/10 object-cover shadow-lg shadow-cyan-900/30">
                <?php else: ?>
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-white/10 bg-gradient-to-br from-cyan-500 to-sky-400 text-slate-950 shadow-lg shadow-cyan-900/30" aria-hidden="true">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                <?php endif; ?>
                <p class="mt-4 text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/80"><?php echo e(\App\Support\SchoolBranding::systemName()); ?></p>
                <h1 class="mt-3 text-3xl font-bold text-white">Secure Passkey Portal</h1>
                <p class="mt-2 text-sm text-slate-400"><?php echo e(\App\Support\SchoolBranding::periodLabel()); ?></p>
                <?php if($poweredBy = \App\Support\SchoolBranding::poweredBy()): ?>
                    <p class="mt-1 text-sm text-slate-500"><?php echo e($poweredBy); ?></p>
                <?php endif; ?>
                <p class="mt-1 text-sm text-slate-500">Passwordless sign-in using your device fingerprint, face, or PIN.</p>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/5 p-8 shadow-2xl backdrop-blur-xl">
                <div id="passkey-status" class="mb-4 hidden rounded-xl border px-4 py-3 text-sm" role="status" aria-live="polite"></div>

                <button
                    id="passkey-login-btn"
                    type="button"
                    class="flex w-full items-center justify-center gap-3 rounded-xl bg-cyan-500 px-4 py-4 text-base font-semibold text-slate-950 transition hover:bg-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-300/60 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span id="passkey-login-label">Login with Passkey / Fingerprint</span>
                    <svg id="passkey-spinner" class="hidden h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 100 16v-4l-3 3 3 3v-4a8 8 0 01-8-8z"></path>
                    </svg>
                </button>

                <p class="mt-4 text-center text-xs text-slate-500">
                    No password is required. Your private key never leaves this device.
                </p>

                <p class="mt-6 flex flex-col gap-2 text-center text-xs">
                    <a href="<?php echo e(route('register')); ?>" class="text-cyan-300 hover:text-cyan-200"> Create an account?</a>
                    <a href="<?php echo e(route('login.recovery')); ?>" class="text-slate-400 hover:text-slate-300">Lost your passkey? Recovery options</a>
                </p>
            </div>

        </div>
    </div>

    <script>
        window.passkeyPortal = {
            loginOptionsUrl: <?php echo json_encode($loginOptionsUrl, 15, 512) ?>,
            loginVerifyUrl: <?php echo json_encode($loginVerifyUrl, 15, 512) ?>,
        };
    </script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/auth/login.blade.php ENDPATH**/ ?>
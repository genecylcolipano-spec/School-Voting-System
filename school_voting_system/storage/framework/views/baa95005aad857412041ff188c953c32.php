<?php
    $authUser = auth()->user();
    $portalLayout = request()->routeIs([
        'student.*',
        'faculty.*',
        'admin.*',
        'super-admin.*',
        'preview.dashboard',
    ]) || (
        request()->routeIs('profile.*')
        && $authUser
        && (
            $authUser->isStudent()
            || $authUser->isFaculty()
            || $authUser->isAdmin()
            || $authUser->isSuperAdmin()
        )
    );
?>
<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['min-h-full bg-slate-950' => $portalLayout]); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e(\App\Support\SchoolBranding::systemName()); ?></title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
        <?php echo $__env->yieldPushContent('styles'); ?>
    </head>
    <body class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'font-sans antialiased',
        'min-h-full bg-slate-950 text-slate-100' => $portalLayout,
    ]); ?>">
        <?php if($portalLayout): ?>
            
            <?php echo e($slot); ?>

        <?php else: ?>
            <div class="min-h-screen bg-gray-100">
                <?php if(!request()->routeIs('dashboard')): ?>
                    <?php echo $__env->make('layouts.navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php endif; ?>

                <?php if(isset($header)): ?>
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            <?php echo e($header); ?>

                        </div>
                    </header>
                <?php endif; ?>

                <main>
                    <?php echo e($slot); ?>

                </main>
            </div>
        <?php endif; ?>
        <?php echo $__env->yieldPushContent('scripts'); ?>
    </body>
</html>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/layouts/app.blade.php ENDPATH**/ ?>
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => 'Admin Dashboard',
    'user',
    'notificationsCount' => 0,
    'assignedRole' => null,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'title' => 'Admin Dashboard',
    'user',
    'notificationsCount' => 0,
    'assignedRole' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    use App\Support\AdminPortal;
    $dashboardRoute = AdminPortal::dashboardRouteName($user);
    $onDashboard = request()->routeIs('admin.dashboard', 'super-admin.dashboard');
    $isSuperAdmin = $user->isSuperAdmin();
    $portalLabel = $isSuperAdmin ? 'Super Admin Portal' : 'Admin Portal';
    $roleLabel = $user->roleLabel();
    $welcomeLabel = $user->name;
?>


<div
    x-data="{
        sidebarOpen: false,
        collapsed: sessionStorage.getItem('adminSidebarCollapsed') === 'true',
        toggleSidebar() {
            if (window.innerWidth >= 1024) {
                this.collapsed = !this.collapsed;
                sessionStorage.setItem('adminSidebarCollapsed', this.collapsed ? 'true' : 'false');
            } else {
                this.sidebarOpen = !this.sidebarOpen;
            }
        }
    }"
    @keydown.escape.window="sidebarOpen = false"
    class="min-h-screen bg-slate-950 text-slate-100"
>
    <div
        x-show="sidebarOpen"
        x-transition.opacity
        @click="sidebarOpen = false"
        class="fixed inset-0 z-40 bg-black/60 lg:hidden"
        style="display: none;"
    ></div>

    <aside
        :class="{
            'translate-x-0': sidebarOpen,
            '-translate-x-full': !sidebarOpen,
            'lg:translate-x-0': true,
            'lg:w-20': collapsed,
            'lg:w-72': !collapsed
        }"
        class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-violet-500/15 bg-slate-950 transition-all duration-300"
    >
        <div class="border-b border-violet-500/10 px-5 py-5">
            <?php if (isset($component)) { $__componentOriginal3a1587ba40472411c16c01b94592189a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3a1587ba40472411c16c01b94592189a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.school-brand','data' => ['subtitle' => $portalLabel,'collapsedAware' => true,'gradient' => 'from-violet-600 to-indigo-500','iconClass' => 'text-white','shadowClass' => 'shadow-violet-900/40']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('school-brand'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($portalLabel),'collapsed-aware' => true,'gradient' => 'from-violet-600 to-indigo-500','icon-class' => 'text-white','shadow-class' => 'shadow-violet-900/40']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3a1587ba40472411c16c01b94592189a)): ?>
<?php $attributes = $__attributesOriginal3a1587ba40472411c16c01b94592189a; ?>
<?php unset($__attributesOriginal3a1587ba40472411c16c01b94592189a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3a1587ba40472411c16c01b94592189a)): ?>
<?php $component = $__componentOriginal3a1587ba40472411c16c01b94592189a; ?>
<?php unset($__componentOriginal3a1587ba40472411c16c01b94592189a); ?>
<?php endif; ?>
        </div>

        <?php echo $__env->make('admin.partials.sidebar-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </aside>

    <div
        class="min-w-0 bg-slate-950"
        :class="collapsed ? 'lg:pl-20' : 'lg:pl-72'"
    >
        <header class="sticky top-0 z-30 border-b border-violet-500/10 bg-slate-950/90 backdrop-blur-md">
            <div class="flex flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex min-w-0 items-center gap-3">
                    <button
                        type="button"
                        @click="toggleSidebar()"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-violet-500/20 bg-slate-900 text-violet-300 hover:bg-slate-800"
                        aria-label="Toggle sidebar"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="min-w-0">
                        <p class="truncate text-sm text-slate-400">Welcome back, <?php echo e($welcomeLabel); ?></p>
                        <h1 class="truncate text-lg font-bold text-white sm:text-xl"><?php echo e($title); ?></h1>
                    </div>
                </div>

                <div class="relative flex max-w-xl flex-1 items-center gap-2">
                    <?php if($isSuperAdmin): ?>
                        <div class="relative hidden w-full sm:block">
                            <input id="super-admin-search" type="search" placeholder="Search accounts, students, elections…"
                                class="w-full rounded-xl border border-violet-500/20 bg-slate-900/80 px-4 py-2 text-sm text-white placeholder:text-slate-500 focus:border-violet-400/50 focus:outline-none">
                            <div id="super-admin-search-results" class="absolute left-0 right-0 top-full z-50 mt-2 hidden max-h-64 overflow-y-auto rounded-xl border border-violet-500/20 bg-slate-900 shadow-xl"></div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="relative flex items-center gap-2">
                    <?php if (isset($component)) { $__componentOriginal7169a5b356633be5dafc74bf7a8eb300 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7169a5b356633be5dafc74bf7a8eb300 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.notification-center','data' => ['feedUrl' => route('admin.notifications.feed'),'indexUrl' => route('admin.notifications.index'),'markAllUrl' => route('admin.notifications.read'),'markOneUrlTemplate' => ''.e(route('admin.notifications.read-one', ['notification' => '__ID__'])).'','initialCount' => $notificationsCount,'theme' => 'admin']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('notification-center'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['feed-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.notifications.feed')),'index-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.notifications.index')),'mark-all-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.notifications.read')),'mark-one-url-template' => ''.e(route('admin.notifications.read-one', ['notification' => '__ID__'])).'','initial-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount),'theme' => 'admin']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7169a5b356633be5dafc74bf7a8eb300)): ?>
<?php $attributes = $__attributesOriginal7169a5b356633be5dafc74bf7a8eb300; ?>
<?php unset($__attributesOriginal7169a5b356633be5dafc74bf7a8eb300); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7169a5b356633be5dafc74bf7a8eb300)): ?>
<?php $component = $__componentOriginal7169a5b356633be5dafc74bf7a8eb300; ?>
<?php unset($__componentOriginal7169a5b356633be5dafc74bf7a8eb300); ?>
<?php endif; ?>

                    <?php if($isSuperAdmin): ?>
                        <a href="<?php echo e(route('admin.recovery.index')); ?>" class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border border-violet-500/20 bg-slate-900 text-violet-300 hover:bg-slate-800" title="Passkey recovery queue">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        </a>
                    <?php endif; ?>

                    <?php if (isset($component)) { $__componentOriginalca7a0abfe8e944091236a86c0d7e6936 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalca7a0abfe8e944091236a86c0d7e6936 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.responsive-popover','data' => ['align' => 'end','mobileTitle' => 'Account','widthClass' => 'w-72','panelClass' => 'border-violet-500/20']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('responsive-popover'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'end','mobile-title' => 'Account','width-class' => 'w-72','panel-class' => 'border-violet-500/20']); ?>
                         <?php $__env->slot('trigger', null, []); ?> 
                            <button
                                type="button"
                                class="inline-flex max-w-[14rem] items-center gap-2 rounded-xl border border-violet-500/20 bg-slate-900/80 py-1.5 pl-1.5 pr-2.5 text-sm font-semibold text-white hover:bg-slate-800 sm:max-w-xs sm:pr-3"
                                aria-haspopup="menu"
                            >
                                <?php if (isset($component)) { $__componentOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.user-avatar','data' => ['user' => $user,'size' => 'nav','class' => '!h-10 !w-10 sm:!h-11 sm:!w-11']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('user-avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'size' => 'nav','class' => '!h-10 !w-10 sm:!h-11 sm:!w-11']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e)): ?>
<?php $attributes = $__attributesOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e; ?>
<?php unset($__attributesOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e)): ?>
<?php $component = $__componentOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e; ?>
<?php unset($__componentOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e); ?>
<?php endif; ?>
                                <span class="hidden truncate sm:inline"><?php echo e($user->name); ?></span>
                                <svg class="h-4 w-4 shrink-0 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                         <?php $__env->endSlot(); ?>

                        <div class="flex items-center gap-3 border-b border-slate-800 px-4 py-4">
                            <?php if (isset($component)) { $__componentOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.user-avatar','data' => ['user' => $user,'size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('user-avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'size' => 'lg']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e)): ?>
<?php $attributes = $__attributesOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e; ?>
<?php unset($__attributesOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e)): ?>
<?php $component = $__componentOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e; ?>
<?php unset($__componentOriginalaa6ddd3b8ee0acee5a2d1d7ac5c7e40e); ?>
<?php endif; ?>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-white"><?php echo e($user->name); ?></p>
                                <p class="mt-0.5 text-xs font-medium text-violet-300"><?php echo e($roleLabel); ?></p>
                                <p class="mt-0.5 truncate text-xs text-slate-400"><?php echo e($user->email); ?></p>
                            </div>
                        </div>

                        <div class="py-1">
                            <a
                                href="<?php echo e(route('profile.edit', ['section' => 'profile'])); ?>"
                                data-popover-close
                                role="menuitem"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-200 hover:bg-slate-800 hover:text-violet-300"
                            >
                                <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                My Profile
                            </a>
                            <a
                                href="<?php echo e(route('profile.edit')); ?>"
                                data-popover-close
                                role="menuitem"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-200 hover:bg-slate-800 hover:text-violet-300"
                            >
                                <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Settings
                            </a>
                            <?php if($isSuperAdmin): ?>
                                <a
                                    href="<?php echo e(route('admin.reports.index')); ?>"
                                    data-popover-close
                                    role="menuitem"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-200 hover:bg-slate-800 hover:text-violet-300"
                                >
                                    <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    System Reports
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="border-t border-slate-800 py-1">
                            <form method="POST" action="<?php echo e(route('logout')); ?>">
                                <?php echo csrf_field(); ?>
                                <button
                                    type="submit"
                                    role="menuitem"
                                    class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm text-rose-300 hover:bg-slate-800"
                                >
                                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalca7a0abfe8e944091236a86c0d7e6936)): ?>
<?php $attributes = $__attributesOriginalca7a0abfe8e944091236a86c0d7e6936; ?>
<?php unset($__attributesOriginalca7a0abfe8e944091236a86c0d7e6936); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalca7a0abfe8e944091236a86c0d7e6936)): ?>
<?php $component = $__componentOriginalca7a0abfe8e944091236a86c0d7e6936; ?>
<?php unset($__componentOriginalca7a0abfe8e944091236a86c0d7e6936); ?>
<?php endif; ?>
                </div>
            </div>
        </header>

        <main class="space-y-6 bg-slate-950 px-4 py-6 pb-8 sm:px-6 lg:px-8">
            <?php if(session('success')): ?>
                <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>

            <?php echo e($slot); ?>

        </main>
    </div>

    <?php echo $__env->make('admin.partials.confirm-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/notification-center.js', 'resources/js/admin-confirm.js']); ?>
</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/admin-portal.blade.php ENDPATH**/ ?>
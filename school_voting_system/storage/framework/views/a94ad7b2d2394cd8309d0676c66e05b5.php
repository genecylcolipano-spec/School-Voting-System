<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => 'Faculty Portal',
    'user',
    'notificationsCount' => 0,
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
    'title' => 'Faculty Portal',
    'user',
    'notificationsCount' => 0,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $roleLabel = $user->roleLabel();
?>

<div
    x-data="{
        sidebarOpen: false,
        collapsed: sessionStorage.getItem('facultySidebarCollapsed') === 'true',
        toggleSidebar() {
            if (window.innerWidth >= 1024) {
                this.collapsed = !this.collapsed;
                sessionStorage.setItem('facultySidebarCollapsed', this.collapsed ? 'true' : 'false');
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
        class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-teal-500/15 bg-slate-950 transition-all duration-300"
    >
        <div class="border-b border-teal-500/10 px-5 py-5">
            <?php if (isset($component)) { $__componentOriginal3a1587ba40472411c16c01b94592189a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3a1587ba40472411c16c01b94592189a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.school-brand','data' => ['subtitle' => 'Faculty Portal','collapsedAware' => true,'gradient' => 'from-teal-500 to-emerald-400','iconClass' => 'text-slate-950','shadowClass' => 'shadow-teal-900/40']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('school-brand'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['subtitle' => 'Faculty Portal','collapsed-aware' => true,'gradient' => 'from-teal-500 to-emerald-400','icon-class' => 'text-slate-950','shadow-class' => 'shadow-teal-900/40']); ?>
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

        <?php echo $__env->make('faculty.partials.sidebar-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </aside>

    <div
        class="min-w-0 bg-slate-950"
        :class="collapsed ? 'lg:pl-20' : 'lg:pl-72'"
    >
        <header class="sticky top-0 z-30 border-b border-teal-500/10 bg-slate-950/90 backdrop-blur-md">
            <div class="flex flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex min-w-0 items-center gap-3">
                    <button
                        type="button"
                        @click="toggleSidebar()"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-teal-500/20 bg-slate-900 text-teal-300 hover:bg-slate-800"
                        aria-label="Toggle sidebar"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="min-w-0">
                        <p class="truncate text-sm text-slate-400">Welcome back, <?php echo e($user->name); ?></p>
                        <h1 class="truncate text-lg font-bold text-white sm:text-xl"><?php echo e($title); ?></h1>
                    </div>
                </div>

                <div class="relative flex items-center gap-2">
                    <?php if (isset($component)) { $__componentOriginal7169a5b356633be5dafc74bf7a8eb300 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7169a5b356633be5dafc74bf7a8eb300 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.notification-center','data' => ['feedUrl' => route('faculty.notifications.feed'),'indexUrl' => route('faculty.notifications.index'),'markAllUrl' => route('faculty.notifications.read'),'markOneUrlTemplate' => ''.e(route('faculty.notifications.read-one', ['notification' => '__ID__'])).'','initialCount' => $notificationsCount,'theme' => 'faculty']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('notification-center'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['feed-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('faculty.notifications.feed')),'index-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('faculty.notifications.index')),'mark-all-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('faculty.notifications.read')),'mark-one-url-template' => ''.e(route('faculty.notifications.read-one', ['notification' => '__ID__'])).'','initial-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount),'theme' => 'faculty']); ?>
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

                    <?php echo $__env->make('faculty.partials.account-menu', ['user' => $user, 'roleLabel' => $roleLabel], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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

    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/notification-center.js']); ?>
</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/faculty-portal.blade.php ENDPATH**/ ?>
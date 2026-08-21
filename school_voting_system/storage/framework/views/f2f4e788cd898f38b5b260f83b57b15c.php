<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'notifications',
    'filters' => [],
    'indexRoute' => '#',
    'theme' => 'admin',
    'markAllRoute' => null,
    'markOneRouteName' => null,
    'deleteRouteName' => null,
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
    'notifications',
    'filters' => [],
    'indexRoute' => '#',
    'theme' => 'admin',
    'markAllRoute' => null,
    'markOneRouteName' => null,
    'deleteRouteName' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $isAdmin = $theme === 'admin';
    $isFaculty = $theme === 'faculty';
    $cardBorder = match (true) {
        $isAdmin => 'border-violet-500/15',
        $isFaculty => 'border-teal-500/15',
        default => 'border-cyan-500/15',
    };
    $accent = match (true) {
        $isAdmin => 'text-violet-300',
        $isFaculty => 'text-teal-300',
        default => 'text-cyan-300',
    };
    $btnClass = match (true) {
        $isAdmin => 'rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-500',
        $isFaculty => 'rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-500',
        default => 'rounded-lg bg-cyan-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:opacity-90',
    };
?>

<form method="GET" action="<?php echo e($indexRoute); ?>" class="mb-6 grid gap-3 rounded-2xl border <?php echo e($cardBorder); ?> bg-slate-900/70 p-4 md:grid-cols-5">
    <div class="md:col-span-2">
        <label class="text-[10px] uppercase text-slate-500">Search</label>
        <input type="search" name="search" value="<?php echo e($filters['search'] ?? ''); ?>" placeholder="Search notifications…" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white">
    </div>
    <div>
        <label class="text-[10px] uppercase text-slate-500">Status</label>
        <select name="status" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white">
            <option value="">All</option>
            <option value="unread" <?php if(($filters['status'] ?? '') === 'unread'): echo 'selected'; endif; ?>>Unread</option>
            <option value="read" <?php if(($filters['status'] ?? '') === 'read'): echo 'selected'; endif; ?>>Read</option>
        </select>
    </div>
    <div>
        <label class="text-[10px] uppercase text-slate-500">Period</label>
        <select name="period" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white">
            <option value="">Any time</option>
            <option value="today" <?php if(($filters['period'] ?? '') === 'today'): echo 'selected'; endif; ?>>Today</option>
            <option value="week" <?php if(($filters['period'] ?? '') === 'week'): echo 'selected'; endif; ?>>This Week</option>
            <option value="month" <?php if(($filters['period'] ?? '') === 'month'): echo 'selected'; endif; ?>>This Month</option>
        </select>
    </div>
    <div class="flex items-end gap-2">
        <button type="submit" class="<?php echo e($btnClass); ?>">Apply</button>
        <?php if(($filters['search'] ?? '') || ($filters['status'] ?? '') || ($filters['period'] ?? '')): ?>
            <a href="<?php echo e($indexRoute); ?>" class="rounded-lg border border-slate-700 px-4 py-2 text-sm text-slate-300 hover:text-white">Clear</a>
        <?php endif; ?>
    </div>
</form>

<?php if($markAllRoute): ?>
    <div class="mb-4 flex justify-end">
        <form method="POST" action="<?php echo e($markAllRoute); ?>">
            <?php echo csrf_field(); ?>
            <button type="submit" class="rounded-lg border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-slate-800">Mark all as read</button>
        </form>
    </div>
<?php endif; ?>

<div class="space-y-3">
    <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $notification = is_array($entry) ? $entry['model'] : $entry;
            $icon = is_array($entry) ? ($entry['icon'] ?? '📌') : '📌';
            $url = is_array($entry) ? ($entry['url'] ?? null) : null;
            $isUnread = $notification->read_at === null;
        ?>
        <article class="rounded-2xl border <?php echo e($cardBorder); ?> p-4 transition hover:border-slate-600 <?php echo e($isUnread ? 'bg-slate-900/90 ring-1 ring-sky-500/20' : 'bg-slate-900/50 opacity-80'); ?>">
            <div class="flex items-start gap-4">
                <span class="text-2xl" aria-hidden="true"><?php echo e($icon); ?></span>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <?php if($url): ?>
                            <a href="<?php echo e($url); ?>" class="text-sm font-semibold text-white hover:underline"><?php echo e($notification->title); ?></a>
                        <?php else: ?>
                            <p class="text-sm font-semibold text-white"><?php echo e($notification->title); ?></p>
                        <?php endif; ?>
                        <?php if($isUnread): ?>
                            <span class="inline-flex items-center gap-1 rounded-full bg-sky-500/15 px-2 py-0.5 text-[10px] font-bold uppercase text-sky-300">
                                <span class="h-1.5 w-1.5 rounded-full bg-sky-400"></span> Unread
                            </span>
                        <?php else: ?>
                            <span class="text-[10px] uppercase text-slate-500">Read</span>
                        <?php endif; ?>
                    </div>
                    <p class="mt-1 text-sm text-slate-300"><?php echo e($notification->message); ?></p>
                    <p class="mt-2 text-xs text-slate-500">
                        <?php echo e($notification->created_at?->diffForHumans()); ?>

                        · <?php echo e($notification->created_at?->format('M d, Y g:i A')); ?>

                        <?php if($notification->module): ?>
                            · <?php echo e($notification->module->label()); ?>

                        <?php endif; ?>
                    </p>
                </div>
                <div class="flex shrink-0 flex-col items-end gap-2">
                    <?php if($isUnread && $markOneRouteName): ?>
                        <form method="POST" action="<?php echo e(route($markOneRouteName, $notification)); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="text-xs font-semibold <?php echo e($accent); ?> hover:opacity-80">Mark read</button>
                        </form>
                    <?php endif; ?>
                    <?php if($deleteRouteName): ?>
                        <form method="POST" action="<?php echo e(route($deleteRouteName, $notification)); ?>"
                            onsubmit="return confirm('Delete this notification?')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="text-xs font-semibold text-rose-300 hover:text-rose-200">Delete</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </article>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="rounded-2xl border border-dashed border-slate-700 px-6 py-12 text-center text-sm text-slate-400">
            No notifications yet.
        </div>
    <?php endif; ?>
</div>

<div class="mt-6"><?php echo e($notifications->links()); ?></div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/notifications-index.blade.php ENDPATH**/ ?>
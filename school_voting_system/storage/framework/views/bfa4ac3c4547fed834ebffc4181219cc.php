<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'account',
    'variant' => 'admin', // admin | faculty | student
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
    'account',
    'variant' => 'admin', // admin | faculty | student
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $isFaculty = $variant === 'faculty';
    $isStudent = $variant === 'student';
    $isAdmin = $variant === 'admin';

    $showRoute = $isStudent
        ? route('admin.students.show', $account)
        : ($isFaculty ? route('super-admin.faculty.show', $account) : route('super-admin.administrators.show', $account));

    $editRoute = $isStudent
        ? route('admin.students.edit', $account)
        : ($isFaculty ? route('super-admin.faculty.edit', $account) : route('super-admin.administrators.edit', $account));

    $toggleRoute = $isStudent
        ? route('admin.students.toggle-active', $account)
        : route('super-admin.staff.toggle-active', $account);

    $resetRoute = $isStudent
        ? route('admin.passkey.reset', $account)
        : route('super-admin.staff.enrollment', $account);

    $archiveRoute = $isStudent
        ? route('admin.students.archive', $account)
        : route('super-admin.staff.archive', $account);

    $restoreRoute = $isStudent
        ? route('admin.students.restore', $account)
        : route('super-admin.staff.restore', $account);

    $removeRoute = route('super-admin.staff.destroy', $account);

    $removeLabel = $isFaculty ? 'Remove Faculty' : 'Remove Administrator';
    $removeConfirm = $isFaculty
        ? 'Remove Faculty?\n\nThis action cannot be undone if the account has no active assignments. Accounts with active judging or unpublished scores cannot be removed — deactivate instead.'
        : 'Remove Administrator?\n\nThis action cannot be undone if the account has no active assignments. Accounts tied to active elections, competitions, or fundraising cannot be removed — deactivate instead.';

    $itemClass = 'flex w-full items-center gap-3 px-3.5 py-2.5 text-left text-sm text-slate-200 transition hover:bg-slate-800/90 hover:text-white';
    $dangerClass = 'flex w-full items-center gap-3 px-3.5 py-2.5 text-left text-sm text-rose-300 transition hover:bg-rose-500/10 hover:text-rose-200';
    $sepClass = 'my-1 border-t border-slate-800';
?>

<?php if (isset($component)) { $__componentOriginalca7a0abfe8e944091236a86c0d7e6936 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalca7a0abfe8e944091236a86c0d7e6936 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.responsive-popover','data' => ['align' => 'end','mobileTitle' => 'Actions','widthClass' => 'w-64','panelClass' => 'border-violet-500/25','class' => 'justify-end']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('responsive-popover'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'end','mobile-title' => 'Actions','width-class' => 'w-64','panel-class' => 'border-violet-500/25','class' => 'justify-end']); ?>
     <?php $__env->slot('trigger', null, []); ?> 
        <button
            type="button"
            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-800 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-500/50"
            aria-label="Open actions menu"
            aria-haspopup="menu"
        >
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path d="M10 6a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 5.5a1.5 1.5 0 110-3 1.5 1.5 0 010 3zM11.5 16a1.5 1.5 0 10-3 0 1.5 1.5 0 003 0z"/>
            </svg>
        </button>
     <?php $__env->endSlot(); ?>

    <div class="max-h-[min(70vh,28rem)] overflow-y-auto py-1.5" role="none">
        <?php if(! $isStudent || auth()->user()?->can('updateStudentRecord', $account)): ?>
            <a href="<?php echo e($showRoute); ?>" data-popover-close role="menuitem" class="<?php echo e($itemClass); ?>">
                <span class="w-5 shrink-0 text-center" aria-hidden="true">👁</span>
                <span>View Profile</span>
            </a>
            <a href="<?php echo e($editRoute); ?>" data-popover-close role="menuitem" class="<?php echo e($itemClass); ?>">
                <span class="w-5 shrink-0 text-center" aria-hidden="true">✏</span>
                <span>Edit Information</span>
            </a>
        <?php endif; ?>

        <?php if($isFaculty): ?>
            <a href="<?php echo e($showRoute); ?>#competitions" data-popover-close role="menuitem" class="<?php echo e($itemClass); ?>">
                <span class="w-5 shrink-0 text-center" aria-hidden="true">🏆</span>
                <span>Assign Competitions</span>
            </a>
            <a href="<?php echo e($showRoute); ?>#competitions" data-popover-close role="menuitem" class="<?php echo e($itemClass); ?>">
                <span class="w-5 shrink-0 text-center" aria-hidden="true">📋</span>
                <span>View Assigned Competitions</span>
            </a>
        <?php endif; ?>

        <?php if(! $isStudent || auth()->user()?->can('updateStudentRecord', $account)): ?>
            <a href="<?php echo e($showRoute); ?>#devices" data-popover-close role="menuitem" class="<?php echo e($itemClass); ?>">
                <span class="w-5 shrink-0 text-center" aria-hidden="true">📱</span>
                <span>Registered Devices</span>
            </a>
        <?php endif; ?>

        <?php if(($isStudent && auth()->user()?->can('issuePasskeyReset', $account)) || ! $isStudent): ?>
            <form method="POST" action="<?php echo e($resetRoute); ?>" onsubmit="return confirm('Generate a passkey reset / enrollment link for <?php echo e(addslashes($account->name)); ?>?');">
                <?php echo csrf_field(); ?>
                <button type="submit" data-popover-close role="menuitem" class="<?php echo e($itemClass); ?>">
                    <span class="w-5 shrink-0 text-center" aria-hidden="true">🔑</span>
                    <span>Reset Passkey</span>
                </button>
            </form>
        <?php endif; ?>

        <?php if(! $isStudent || auth()->user()?->can('updateStudentRecord', $account)): ?>
            <a href="<?php echo e($showRoute); ?>#login-history" data-popover-close role="menuitem" class="<?php echo e($itemClass); ?>">
                <span class="w-5 shrink-0 text-center" aria-hidden="true">🕒</span>
                <span>Login History</span>
            </a>
        <?php endif; ?>

        <div class="<?php echo e($sepClass); ?>" role="separator"></div>

        <?php if(! $isStudent || auth()->user()?->can('updateStudentRecord', $account)): ?>
            <?php if($account->archived_at): ?>
                <form method="POST" action="<?php echo e($restoreRoute); ?>" onsubmit="return confirm('Restore <?php echo e(addslashes($account->name)); ?>? The account will be reactivated.');">
                    <?php echo csrf_field(); ?>
                    <button type="submit" data-popover-close role="menuitem" class="<?php echo e($itemClass); ?>">
                        <span class="w-5 shrink-0 text-center" aria-hidden="true">🟢</span>
                        <span>Restore Account</span>
                    </button>
                </form>
            <?php elseif($account->is_active): ?>
                <form method="POST" action="<?php echo e($toggleRoute); ?>" onsubmit="return confirm('Deactivate <?php echo e(addslashes($account->name)); ?>?\n\nThey will not be able to sign in until reactivated.');">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PATCH'); ?>
                    <button type="submit" data-popover-close role="menuitem" class="<?php echo e($itemClass); ?>">
                        <span class="w-5 shrink-0 text-center" aria-hidden="true">🚫</span>
                        <span>Deactivate Account</span>
                    </button>
                </form>
            <?php else: ?>
                <form method="POST" action="<?php echo e($toggleRoute); ?>" onsubmit="return confirm('Activate <?php echo e(addslashes($account->name)); ?>?');">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PATCH'); ?>
                    <button type="submit" data-popover-close role="menuitem" class="<?php echo e($itemClass); ?>">
                        <span class="w-5 shrink-0 text-center" aria-hidden="true">🟢</span>
                        <span>Activate Account</span>
                    </button>
                </form>
            <?php endif; ?>
        <?php endif; ?>

        <div class="<?php echo e($sepClass); ?>" role="separator"></div>

        <?php if($isStudent): ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('updateStudentRecord', $account)): ?>
                <?php if (! ($account->archived_at)): ?>
                    <form method="POST" action="<?php echo e($archiveRoute); ?>" onsubmit="return confirm('Archive Student?\n\nVotes, submissions, donations, and login history are preserved. Students are never permanently deleted.');">
                        <?php echo csrf_field(); ?>
                        <button type="submit" data-popover-close role="menuitem" class="<?php echo e($dangerClass); ?>">
                            <span class="w-5 shrink-0 text-center" aria-hidden="true">📦</span>
                            <span>Archive Student</span>
                        </button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        <?php else: ?>
            <form method="POST" action="<?php echo e($removeRoute); ?>" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from($removeConfirm)->toHtml() ?>);">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button type="submit" data-popover-close role="menuitem" class="<?php echo e($dangerClass); ?>">
                    <span class="w-5 shrink-0 text-center" aria-hidden="true">🗑</span>
                    <span><?php echo e($removeLabel); ?></span>
                </button>
            </form>
        <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/admin/user-action-menu.blade.php ENDPATH**/ ?>
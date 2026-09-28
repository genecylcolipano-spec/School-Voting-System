<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'action',
    'warning' => null,
    'buttonClass' => 'ml-3 text-rose-300 hover:text-rose-200',
    'label' => 'Delete',
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
    'action',
    'warning' => null,
    'buttonClass' => 'ml-3 text-rose-300 hover:text-rose-200',
    'label' => 'Delete',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $baseMessage = "Are you sure you want to delete this activity?\n\nThis action will remove the activity from the system.\n\nThis action cannot be undone.";

    if (filled($warning)) {
        $baseMessage = "Are you sure you want to delete this activity?\n\n{$warning}\n\nThis action will remove the activity from the system.\n\nThis action cannot be undone.";
    }
?>

<form
    method="POST"
    action="<?php echo e($action); ?>"
    <?php echo e($attributes->merge(['class' => 'inline'])); ?>

    data-confirm-sensitive
    data-confirm-title="Delete Activity?"
    data-confirm-message="<?php echo e($baseMessage); ?>"
    data-confirm-ok-label="Delete Activity"
    data-confirm-danger="1"
>
    <?php echo csrf_field(); ?>
    <?php echo method_field('DELETE'); ?>
    <button type="submit" class="<?php echo e($buttonClass); ?>"><?php echo e($label); ?></button>
</form>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/admin/delete-action.blade.php ENDPATH**/ ?>
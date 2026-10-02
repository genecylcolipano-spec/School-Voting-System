<?php
    $schoolName = \App\Support\SchoolBranding::schoolName();
?>

<?php if(filled($schoolName)): ?>
    <p <?php echo e($attributes->class('text-slate-500')); ?>>
        Powered by <span class="school-name"><?php echo e($schoolName); ?></span>
    </p>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/powered-by.blade.php ENDPATH**/ ?>
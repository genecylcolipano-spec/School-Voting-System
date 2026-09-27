<?php
    $photo = $entry->photoUrl() ?: $entry->thumbnailUrl();
    $initial = strtoupper(substr((string) $entry->display_name, 0, 1));
?>

<?php if($photo): ?>
    <img src="<?php echo e($photo); ?>" alt="<?php echo e($entry->display_name); ?>" class="h-full w-full object-cover object-center" loading="lazy">
<?php else: ?>
    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-teal-900/50 to-slate-900">
        <span class="font-bold text-teal-200/70 <?php echo e($initialClass ?? 'text-2xl'); ?>"><?php echo e($initial); ?></span>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/faculty/judging/_entry-photo.blade.php ENDPATH**/ ?>
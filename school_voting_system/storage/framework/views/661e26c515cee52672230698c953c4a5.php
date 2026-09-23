<?php
    $isEmpty = ($cards ?? collect())->isEmpty();
    $filtered = $hasFilters ?? false;
?>

<?php if($isEmpty): ?>
    <div class="col-span-full flex flex-col items-center justify-center rounded-2xl border border-dashed border-violet-500/20 bg-slate-900/50 px-6 py-16 text-center" data-live-empty>
        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-violet-500/10 text-3xl">📡</div>
        <?php if($filtered): ?>
            <h2 class="text-xl font-bold text-white">No activities match these filters</h2>
            <p class="mt-2 max-w-md text-sm text-slate-400">Try clearing filters or adjusting status / school year to see monitoring cards again.</p>
            <a href="<?php echo e(url()->current()); ?>" class="mt-4 rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Clear Filters</a>
        <?php else: ?>
            <h2 class="text-xl font-bold text-white">No Live Activities</h2>
            <p class="mt-2 max-w-md text-sm text-slate-400">There are currently no Elections or Talent Competitions requiring live monitoring.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/live-monitoring/_empty.blade.php ENDPATH**/ ?>
<section id="overview" class="scroll-mt-28 space-y-4">
    <div class="flex items-center justify-end gap-2">
        <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
        <p id="dashboard-live-updated" class="text-[11px] font-medium text-slate-500">Live · syncing…</p>
    </div>

    
    <div class="grid gap-4 xl:grid-cols-12">
        <div class="xl:col-span-5">
            <?php echo $__env->make('admin.dashboard._hero', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
        <div class="xl:col-span-7">
            <?php echo $__env->make('admin.dashboard._stats', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>

    
    <?php echo $__env->make('admin.dashboard._analytics-widgets', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <div class="-mx-4 sm:-mx-6 lg:-mx-8">
        <?php echo $__env->make('admin.dashboard._live-voting', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/dashboard/_bento.blade.php ENDPATH**/ ?>
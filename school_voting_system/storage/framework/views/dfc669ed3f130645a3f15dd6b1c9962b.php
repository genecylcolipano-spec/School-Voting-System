<?php
    $active = $active ?? 'election';
?>

<div class="mb-5 flex flex-wrap items-center gap-2">
    <a href="<?php echo e(route('admin.reports.index')); ?>" class="rounded-full px-4 py-1.5 text-sm font-semibold transition <?php echo e($active === 'election' ? 'bg-gradient-to-r from-violet-600 to-indigo-500 text-white' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white'); ?>">Election Reports</a>
    <a href="<?php echo e(route('admin.reports.talent')); ?>" class="rounded-full px-4 py-1.5 text-sm font-semibold transition <?php echo e($active === 'talent' ? 'bg-gradient-to-r from-violet-600 to-indigo-500 text-white' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white'); ?>">Talent Reports</a>
    <a href="<?php echo e(route('admin.reports.fundraising')); ?>" class="rounded-full px-4 py-1.5 text-sm font-semibold transition <?php echo e($active === 'fundraising' ? 'bg-gradient-to-r from-violet-600 to-indigo-500 text-white' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white'); ?>">Fundraising Reports</a>
</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/reports/partials/report-tabs.blade.php ENDPATH**/ ?>
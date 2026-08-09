<?php if($isReadOnly): ?>
    <div class="rounded-xl border border-slate-600/40 bg-slate-800/50 px-4 py-3 text-sm text-slate-300">
        <strong class="text-white">Read-only role</strong> — you can view dashboard data and export reports, but modification actions are disabled.
    </div>
<?php elseif($isAuditor): ?>
    <div class="rounded-xl border border-fuchsia-500/25 bg-fuchsia-950/30 px-4 py-3 text-sm text-fuchsia-100">
        <strong>Auditor mode</strong> — cross-check turnout and verified results. Operational actions are hidden.
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/dashboard/_role-banner.blade.php ENDPATH**/ ?>
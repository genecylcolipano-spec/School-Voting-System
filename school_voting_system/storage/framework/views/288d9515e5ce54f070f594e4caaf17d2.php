<?php
    $pickerElections = $elections ?? collect();
    $pickerElection = $election ?? null;
    $pickerAction = $pickerAction ?? url()->current();
?>

<?php if($pickerElections->isNotEmpty()): ?>
    <form method="GET" action="<?php echo e($pickerAction); ?>" class="mb-5 flex flex-wrap items-end gap-3">
        <div class="min-w-[16rem] flex-1">
            <label for="report-election" class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Election</label>
            <select
                id="report-election"
                name="election"
                onchange="this.form.submit()"
                class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-sm text-slate-100"
            >
                <?php $__currentLoopData = $pickerElections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($option->id); ?>" <?php if($pickerElection?->id === $option->id): echo 'selected'; endif; ?>>
                        <?php echo e($option->title); ?> · <?php echo e($option->status?->label() ?? '—'); ?>

                        <?php if($option->public_results_published): ?>
                            · Published
                        <?php endif; ?>
                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <noscript>
            <button type="submit" class="rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white">View</button>
        </noscript>
    </form>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/reports/partials/election-picker.blade.php ENDPATH**/ ?>
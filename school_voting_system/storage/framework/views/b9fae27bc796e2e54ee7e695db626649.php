<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['recoveryRequests']));

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

foreach (array_filter((['recoveryRequests']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-5 sm:p-6">
    <h3 class="text-lg font-semibold text-white">Passkey Recovery Requests</h3>
    <p class="mt-1 text-sm text-slate-400">
        Self-service email reset still requires a matching Account ID and email. Use this queue for unmatched or stuck attempts — issue a link to the email on file, or dismiss junk.
    </p>

    <div id="recovery-admin-status" class="mt-4 hidden rounded-xl border px-3 py-2 text-sm" role="status"></div>

    <p class="mt-4 text-sm text-slate-400 <?php echo e($recoveryRequests->isEmpty() ? '' : 'hidden'); ?>" data-recovery-empty>
        No pending recovery requests.
    </p>

    <div class="<?php echo e($recoveryRequests->isEmpty() ? 'hidden' : ''); ?>" data-recovery-lists>
        <div class="mt-4 space-y-3 lg:hidden" data-recovery-cards>
            <?php $__currentLoopData = $recoveryRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recoveryRequest): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="rounded-xl border border-slate-800 bg-slate-950/50 p-3" data-recovery-row="<?php echo e($recoveryRequest->id); ?>">
                    <p class="font-mono text-sm text-violet-300"><?php echo e($recoveryRequest->account_id); ?></p>
                    <p class="mt-1 break-all text-sm text-slate-200"><?php echo e($recoveryRequest->email); ?></p>
                    <dl class="mt-3 grid grid-cols-2 gap-2 text-xs text-slate-400">
                        <div class="col-span-2">
                            <dt class="uppercase tracking-wide text-slate-500">Match</dt>
                            <dd class="mt-0.5"><?php echo $__env->make('admin.partials.passkey-recovery-match', ['recoveryRequest' => $recoveryRequest], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></dd>
                        </div>
                        <div>
                            <dt class="uppercase tracking-wide text-slate-500">Requested</dt>
                            <dd class="mt-0.5 text-slate-200"><?php echo e($recoveryRequest->created_at?->diffForHumans()); ?></dd>
                        </div>
                        <div>
                            <dt class="uppercase tracking-wide text-slate-500">Last email</dt>
                            <dd class="mt-0.5 text-slate-200"><?php echo e($recoveryRequest->last_sent_at?->diffForHumans() ?? 'Never'); ?></dd>
                        </div>
                    </dl>
                    <div class="mt-3">
                        <?php echo $__env->make('admin.partials.passkey-recovery-actions', ['recoveryRequest' => $recoveryRequest, 'stacked' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="mt-4 hidden overflow-x-auto lg:block">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800 text-left text-slate-400">
                        <th class="py-2 pr-4 font-medium">Account ID</th>
                        <th class="py-2 pr-4 font-medium">Requested Email</th>
                        <th class="py-2 pr-4 font-medium">Match</th>
                        <th class="py-2 pr-4 font-medium">Requested</th>
                        <th class="py-2 pr-4 font-medium">Last Email Sent</th>
                        <th class="py-2 font-medium">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $recoveryRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recoveryRequest): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="border-b border-slate-800/80 last:border-0 text-slate-200" data-recovery-row="<?php echo e($recoveryRequest->id); ?>">
                            <td class="py-3 pr-4 font-mono text-violet-300"><?php echo e($recoveryRequest->account_id); ?></td>
                            <td class="py-3 pr-4"><?php echo e($recoveryRequest->email); ?></td>
                            <td class="py-3 pr-4"><?php echo $__env->make('admin.partials.passkey-recovery-match', ['recoveryRequest' => $recoveryRequest], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></td>
                            <td class="py-3 pr-4"><?php echo e($recoveryRequest->created_at?->diffForHumans()); ?></td>
                            <td class="py-3 pr-4"><?php echo e($recoveryRequest->last_sent_at?->diffForHumans() ?? 'Never'); ?></td>
                            <td class="py-3">
                                <?php echo $__env->make('admin.partials.passkey-recovery-actions', ['recoveryRequest' => $recoveryRequest, 'stacked' => false], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/passkey-recovery-queue-dark.blade.php ENDPATH**/ ?>
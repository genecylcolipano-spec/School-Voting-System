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

<div class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-6">
    <h3 class="text-lg font-semibold text-white">Passkey Recovery Requests</h3>
    <p class="mt-1 text-sm text-slate-400">Review pending requests and issue a signed enrollment link.</p>

    <div id="recovery-admin-status" class="mt-4 hidden rounded-xl border px-3 py-2 text-sm" role="status"></div>

    <?php if($recoveryRequests->isEmpty()): ?>
        <p class="mt-4 text-sm text-slate-400">No pending recovery requests.</p>
    <?php else: ?>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800 text-left text-slate-400">
                        <th class="py-2 pr-4 font-medium">Student ID</th>
                        <th class="py-2 pr-4 font-medium">Email</th>
                        <th class="py-2 pr-4 font-medium">Matched User</th>
                        <th class="py-2 pr-4 font-medium">Requested</th>
                        <th class="py-2 pr-4 font-medium">Last Email Sent</th>
                        <th class="py-2 font-medium">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $recoveryRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recoveryRequest): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $lastSentAt = $recoveryRequest->last_sent_at
                                ? \Illuminate\Support\Carbon::parse($recoveryRequest->last_sent_at)
                                : null;
                            $cooldownRemaining = $lastSentAt
                                ? max(0, 120 - $lastSentAt->diffInSeconds(now()))
                                : 0;
                            $cooldownActive = $cooldownRemaining > 0;
                        ?>
                        <tr class="border-b border-slate-800/80 last:border-0 text-slate-200">
                            <td class="py-3 pr-4"><?php echo e($recoveryRequest->account_id); ?></td>
                            <td class="py-3 pr-4"><?php echo e($recoveryRequest->email); ?></td>
                            <td class="py-3 pr-4"><?php echo e($recoveryRequest->user?->name ?? 'No exact user match'); ?></td>
                            <td class="py-3 pr-4"><?php echo e($recoveryRequest->created_at?->diffForHumans()); ?></td>
                            <td class="py-3 pr-4">
                                <?php echo e($recoveryRequest->last_sent_at ? \Illuminate\Support\Carbon::parse($recoveryRequest->last_sent_at)->diffForHumans() : 'Never'); ?>

                            </td>
                            <td class="py-3">
                                <?php if($recoveryRequest->user_id): ?>
                                    <button
                                        type="button"
                                        class="rounded-lg bg-gradient-to-r from-cyan-500 to-sky-400 px-3 py-2 text-xs font-semibold text-slate-950 hover:opacity-90"
                                        data-reset-url="<?php echo e(route('admin.passkey.reset', $recoveryRequest->user_id)); ?>"
                                        data-recovery-request-id="<?php echo e($recoveryRequest->id); ?>"
                                        <?php if($cooldownActive): echo 'disabled'; endif; ?>
                                    >
                                        Generate enrollment link
                                    </button>
                                    <?php if($cooldownActive): ?>
                                        <p class="mt-1 text-xs text-amber-300">Cooldown: retry in <?php echo e($cooldownRemaining); ?>s</p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-xs text-amber-300">Needs manual verification</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/passkey-recovery-queue-dark.blade.php ENDPATH**/ ?>
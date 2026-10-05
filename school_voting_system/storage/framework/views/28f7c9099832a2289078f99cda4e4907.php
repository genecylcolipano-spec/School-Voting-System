<?php
    $donateAction = $donateAction ?? route('student.fundraising.donate', $fundraiser);
    $accent = $accent ?? 'cyan';
    $focusBorder = $accent === 'teal' ? 'focus:border-teal-500 focus:ring-teal-500/30' : 'focus:border-cyan-500 focus:ring-cyan-500/30';
    $radioAccent = $accent === 'teal' ? 'text-teal-500 focus:ring-teal-500/30' : 'text-cyan-500 focus:ring-cyan-500/30';
    $selectedBorder = $accent === 'teal' ? 'border-teal-500/50 bg-teal-500/5' : 'border-cyan-500/50 bg-cyan-500/5';
    $buttonGradient = $accent === 'teal' ? 'from-teal-500 to-emerald-400' : 'from-cyan-500 to-sky-400';
?>

<?php if($paymentMethods === []): ?>
    <p class="mt-3 text-sm text-slate-400">This campaign has no payment methods enabled.</p>
<?php elseif($hasOnlineMethod && ! $paymongoConfigured): ?>
    <p class="mt-3 text-sm text-amber-200">Online payments are not configured yet. Cash can still be submitted for confirmation if this campaign accepts it.</p>
<?php endif; ?>
<form
    method="POST"
    action="<?php echo e($donateAction); ?>"
    class="mt-4 space-y-4"
    x-data="{
        method: <?php echo \Illuminate\Support\Js::from(old('payment_method', $defaultPaymentMethod))->toHtml() ?>,
        labels: <?php echo \Illuminate\Support\Js::from($submitLabels)->toHtml() ?>,
        get submitLabel() {
            return this.labels[this.method] ?? 'Submit donation';
        }
    }"
>
    <?php echo csrf_field(); ?>

    <div>
        <label class="block text-sm font-medium text-slate-300">Amount (PHP)</label>
        <input
            name="amount"
            type="number"
            step="0.01"
            min="<?php echo e($minDonation); ?>"
            <?php if($maxDonation): ?> max="<?php echo e($maxDonation); ?>" <?php endif; ?>
            required
            value="<?php echo e(old('amount', $minDonation)); ?>"
            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100 placeholder:text-slate-500 <?php echo e($focusBorder); ?> focus:outline-none focus:ring-2"
        />
        <p class="mt-1 text-xs text-slate-400">
            Minimum ₱<?php echo e(number_format($minDonation, 2)); ?>

            <?php if($maxDonation): ?>
                · Maximum ₱<?php echo e(number_format($maxDonation, 2)); ?>

            <?php endif; ?>
        </p>
        <?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <fieldset>
        <legend class="block text-sm font-medium text-slate-300">Payment method</legend>
        <div class="mt-2 grid gap-2 sm:grid-cols-2">
            <?php $__currentLoopData = $paymentMethods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $disabled = $method->isOnline() && ! $paymongoConfigured;
                    $selected = old('payment_method', $defaultPaymentMethod) === $method->value;
                ?>
                <label
                    class="flex items-center gap-2 rounded-xl border px-3 py-2.5 text-sm transition <?php echo e($disabled ? 'cursor-not-allowed border-slate-800 bg-slate-950/40 text-slate-500' : ($selected ? $selectedBorder.' text-white' : 'border-slate-800 bg-slate-950/40 text-slate-300')); ?>"
                    <?php if (! ($disabled)): ?>
                        :class="method === '<?php echo e($method->value); ?>'
                            ? '<?php echo e($selectedBorder); ?> text-white'
                            : 'border-slate-800 bg-slate-950/40 text-slate-300'"
                    <?php endif; ?>
                >
                    <input
                        type="radio"
                        name="payment_method"
                        value="<?php echo e($method->value); ?>"
                        x-model="method"
                        <?php if($selected): echo 'checked'; endif; ?>
                        <?php if($disabled): echo 'disabled'; endif; ?>
                        required
                        class="border-slate-700 bg-slate-950/50 <?php echo e($radioAccent); ?>"
                    />
                    <span>
                        <?php echo e($method->label()); ?>

                        <?php if($method->isOnline()): ?>
                            <span class="text-xs text-slate-400">via PayMongo</span>
                        <?php else: ?>
                            <span class="text-xs text-slate-400">pending confirmation</span>
                        <?php endif; ?>
                    </span>
                </label>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php $__errorArgs = ['payment_method'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        <p class="mt-2 text-xs text-slate-400">QR Ph opens a PayMongo checkout page. Cash stays on this page until an organizer confirms. Donations are counted only after payment succeeds or is confirmed.</p>
    </fieldset>

    <div>
        <label class="block text-sm font-medium text-slate-300">Message (optional)</label>
        <input
            name="message"
            type="text"
            maxlength="255"
            value="<?php echo e(old('message')); ?>"
            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100 placeholder:text-slate-500 <?php echo e($focusBorder); ?> focus:outline-none focus:ring-2"
        />
        <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <?php if($fundraiser->allow_anonymous !== false): ?>
        <label class="flex items-center gap-2 text-sm text-slate-300">
            <input type="checkbox" name="is_anonymous" value="1" <?php if(old('is_anonymous')): echo 'checked'; endif; ?> class="rounded border-slate-700 bg-slate-950/50 <?php echo e($radioAccent); ?>" />
            Donate anonymously
        </label>
    <?php endif; ?>

    <button type="submit" class="inline-flex rounded-xl bg-gradient-to-r <?php echo e($buttonGradient); ?> px-5 py-2.5 text-sm font-semibold text-slate-950" x-text="submitLabel">
        <?php echo e($submitLabel); ?>

    </button>
</form>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/fundraising/_donate-form.blade.php ENDPATH**/ ?>
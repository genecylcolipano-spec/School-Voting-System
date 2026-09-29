<?php
    $phoneValue = old('phone', $phoneValue ?? '');
    $phoneHint = $phoneHint ?? 'Used if they cannot receive email — Super Admin can see this on their profile and share a passkey link or call.';
?>

<div>
    <label for="phone" class="block text-sm font-medium text-slate-300">Phone Number <span class="text-slate-500">(optional)</span></label>
    <input id="phone" name="phone" type="tel" value="<?php echo e($phoneValue); ?>"
        autocomplete="tel"
        placeholder="+63…"
        class="<?php echo e($inputClass ?? 'mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none focus:ring-2 focus:ring-violet-500/20'); ?>">
    <p class="mt-1 text-xs text-slate-500"><?php echo e($phoneHint); ?></p>
    <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/partials/phone-field.blade.php ENDPATH**/ ?>
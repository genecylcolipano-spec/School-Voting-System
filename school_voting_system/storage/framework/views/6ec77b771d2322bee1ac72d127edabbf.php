<?php
    $fundraiser = $fundraiser ?? null;
    $isEdit = $fundraiser !== null;
    $donationStats = $donationStats ?? [
        'total' => 0,
        'successful' => 0,
        'pending' => 0,
        'cancelled' => 0,
        'average' => 0,
        'largest' => 0,
    ];
    $resolved = $isEdit ? $fundraiser->resolvedStatus() : null;
    $progress = $isEdit ? $fundraiser->progressPercent() : 0;
    $remaining = $isEdit ? $fundraiser->remainingAmount() : 0;
    $daysLeft = $isEdit ? $fundraiser->daysRemaining() : null;
    $donorCount = $isEdit ? $fundraiser->donorCount() : 0;
    $bannerSrc = $isEdit && $fundraiser->hasUploadedBanner()
        ? $fundraiser->bannerUrl()
        : \App\Support\EventImageUrl::placeholder();
?>
<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginal57da683fe32826f08aa9f05c3342a7e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal57da683fe32826f08aa9f05c3342a7e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => $isEdit ? 'Edit Fundraising Campaign' : 'Create Fundraising Campaign','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isEdit ? 'Edit Fundraising Campaign' : 'Create Fundraising Campaign'),'user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <?php if(session('success')): ?>
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-white"><?php echo e($isEdit ? 'Edit Campaign' : 'Create Campaign'); ?></h1>
                <p class="mt-1 text-sm text-slate-400">Organize campaign details, goals, donation settings, and visibility.</p>
            </div>
            <a href="<?php echo e(route('admin.fundraisers.index')); ?>" class="text-sm font-semibold text-violet-300 hover:text-violet-200">← Back to campaigns</a>
        </div>

        <?php if($isEdit): ?>
            
            <section class="mb-4 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:p-5" aria-label="Campaign overview">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-violet-200">Campaign Overview</h2>
                    <span class="rounded-full border border-violet-500/30 bg-violet-500/10 px-3 py-1 text-xs font-semibold text-violet-100">
                        <?php echo e($resolved?->label() ?? 'Draft'); ?>

                    </span>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-6">
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Goal</p>
                        <p class="mt-1 text-sm font-bold text-white">₱<?php echo e(number_format((float) $fundraiser->goal_amount, 2)); ?></p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Raised</p>
                        <p class="mt-1 text-sm font-bold text-emerald-300">₱<?php echo e(number_format((float) $fundraiser->amount_raised, 2)); ?></p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Remaining</p>
                        <p class="mt-1 text-sm font-bold text-amber-200">₱<?php echo e(number_format($remaining, 2)); ?></p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Progress</p>
                        <p class="mt-1 text-sm font-bold text-white"><?php echo e(number_format($progress, 1)); ?>%</p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Donors</p>
                        <p class="mt-1 text-sm font-bold text-white"><?php echo e(number_format($donorCount)); ?></p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Days Left</p>
                        <p class="mt-1 text-sm font-bold text-white"><?php echo e($daysLeft === null ? '—' : number_format($daysLeft)); ?></p>
                    </div>
                </div>
                <div class="mt-4 h-2.5 overflow-hidden rounded-full bg-slate-800">
                    <div class="h-full rounded-full bg-gradient-to-r from-violet-500 to-cyan-400 transition-all" style="width: <?php echo e($progress); ?>%"></div>
                </div>
            </section>
        <?php endif; ?>

        <form
            method="POST"
            action="<?php echo e($isEdit ? route('admin.fundraisers.update', $fundraiser) : route('admin.fundraisers.store')); ?>"
            enctype="multipart/form-data"
            class="space-y-4"
        >
            <?php echo csrf_field(); ?>
            <?php if($isEdit): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

            
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:p-5">
                <h2 class="text-sm font-bold uppercase tracking-wide text-violet-200">Campaign Information</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-300">Campaign Title</label>
                        <input type="text" name="title" required value="<?php echo e(old('title', $fundraiser?->title)); ?>"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/30">
                        <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-300">Description</label>
                        <textarea name="description" rows="5" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100"><?php echo e(old('description', $fundraiser?->description)); ?></textarea>
                        <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Campaign Category</label>
                        <select name="category" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                            <option value="">Select category</option>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($category->value); ?>" <?php if(old('category', $fundraiser?->category?->value) === $category->value): echo 'selected'; endif; ?>><?php echo e($category->label()); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
            <div>
                        <label class="block text-sm font-medium text-slate-300">Base Status</label>
                        <select name="status" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                    <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($status->value); ?>" <?php if(old('status', $fundraiser?->status?->value ?? 'draft') === $status->value): echo 'selected'; endif; ?>><?php echo e($status->label()); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <p class="mt-1 text-xs text-slate-500">Lifecycle display auto-updates to Scheduled / Active / Goal Reached / Completed from dates and donations. Use Cancelled or Archived to override.</p>
                        <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
            </section>

            
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:p-5">
                <h2 class="text-sm font-bold uppercase tracking-wide text-violet-200">Beneficiary Information</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-300">Beneficiary</label>
                        <input type="text" name="beneficiary" value="<?php echo e(old('beneficiary', $fundraiser?->beneficiary)); ?>" placeholder="e.g. College Library"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                        <?php $__errorArgs = ['beneficiary'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Purpose</label>
                        <input type="text" name="purpose" value="<?php echo e(old('purpose', $fundraiser?->purpose)); ?>" placeholder="e.g. Purchase updated reference books"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                        <?php $__errorArgs = ['purpose'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Expected Beneficiaries</label>
                        <input type="text" name="expected_beneficiaries" value="<?php echo e(old('expected_beneficiaries', $fundraiser?->expected_beneficiaries)); ?>" placeholder="e.g. 450 Students"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                        <?php $__errorArgs = ['expected_beneficiaries'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
            </section>

            
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:p-5">
                <h2 class="text-sm font-bold uppercase tracking-wide text-violet-200">Fundraising Goal</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Goal Amount (₱)</label>
                        <input type="number" name="goal_amount" required min="1" step="0.01" value="<?php echo e(old('goal_amount', $fundraiser?->goal_amount)); ?>"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                        <?php $__errorArgs = ['goal_amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500">Current Raised</label>
                        <input type="text" disabled value="₱<?php echo e(number_format((float) ($fundraiser?->amount_raised ?? 0), 2)); ?>"
                            class="mt-1 w-full rounded-xl border border-slate-800 bg-slate-950/30 px-4 py-2 text-slate-400">
                        <p class="mt-1 text-[11px] text-slate-600">From donation transactions only</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500">Remaining</label>
                        <input type="text" disabled value="₱<?php echo e(number_format($isEdit ? $remaining : 0, 2)); ?>"
                            class="mt-1 w-full rounded-xl border border-slate-800 bg-slate-950/30 px-4 py-2 text-slate-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500">Progress</label>
                        <input type="text" disabled value="<?php echo e(number_format($progress, 1)); ?>%"
                            class="mt-1 w-full rounded-xl border border-slate-800 bg-slate-950/30 px-4 py-2 text-slate-400">
                    </div>
                </div>
            </section>

            
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:p-5">
                <h2 class="text-sm font-bold uppercase tracking-wide text-violet-200">Campaign Schedule</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Start Date</label>
                        <input type="date" name="starts_on" value="<?php echo e(old('starts_on', optional($fundraiser?->starts_on)->format('Y-m-d'))); ?>"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                        <?php $__errorArgs = ['starts_on'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">End Date</label>
                        <input type="date" name="ends_on" value="<?php echo e(old('ends_on', optional($fundraiser?->ends_on)->format('Y-m-d'))); ?>"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                        <?php $__errorArgs = ['ends_on'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500">Days Remaining</label>
                        <input type="text" disabled value="<?php echo e($daysLeft === null ? '—' : $daysLeft.' day(s)'); ?>"
                            class="mt-1 w-full rounded-xl border border-slate-800 bg-slate-950/30 px-4 py-2 text-slate-400">
                    </div>
                </div>
            </section>

            
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:p-5">
                <h2 class="text-sm font-bold uppercase tracking-wide text-violet-200">Donation Settings</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Minimum Donation (₱)</label>
                        <input type="number" name="min_donation" min="1" step="0.01" value="<?php echo e(old('min_donation', $fundraiser?->min_donation)); ?>" placeholder="Default 1.00"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                        <?php $__errorArgs = ['min_donation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Maximum Donation (₱)</label>
                        <input type="number" name="max_donation" min="1" step="0.01" value="<?php echo e(old('max_donation', $fundraiser?->max_donation)); ?>" placeholder="Optional"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                        <?php $__errorArgs = ['max_donation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
                <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <?php $__currentLoopData = [
                        ['allow_anonymous', 'Allow Anonymous Donations', $fundraiser?->allow_anonymous ?? true],
                        ['generate_receipt', 'Generate Donation Receipt', $fundraiser?->generate_receipt ?? true],
                        ['accept_cash', 'Accept Cash', $fundraiser?->accept_cash ?? true],
                        ['accept_gcash', 'Accept GCash', $fundraiser?->accept_gcash ?? true],
                        ['accept_maya', 'Accept Maya', $fundraiser?->accept_maya ?? true],
                        ['accept_bank_transfer', 'Accept Bank Transfer', $fundraiser?->accept_bank_transfer ?? true],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$name, $label, $default]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="inline-flex items-center gap-2 rounded-xl border border-slate-800 bg-slate-950/40 px-3 py-2 text-sm text-slate-300">
                            <input type="checkbox" name="<?php echo e($name); ?>" value="1" <?php if(old($name, $default)): echo 'checked'; endif; ?>
                                class="rounded border-slate-600 bg-slate-900 text-violet-500 focus:ring-violet-500/40">
                            <?php echo e($label); ?>

                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>

            
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:p-5">
                <h2 class="text-sm font-bold uppercase tracking-wide text-violet-200">Campaign Banner</h2>
                <div class="mt-4">
                    <?php if (isset($component)) { $__componentOriginal3da75ac8d1b06da7c4908acd9e717d9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3da75ac8d1b06da7c4908acd9e717d9d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.event-image-field','data' => ['src' => $bannerSrc,'hasUploaded' => $isEdit && $fundraiser->hasUploadedBanner(),'contain' => $isEdit && $fundraiser->bannerNeedsContainLayout(),'orientation' => $isEdit ? $fundraiser->bannerOrientation() : null,'warnPortrait' => $isEdit && $fundraiser->bannerNeedsContainLayout(),'label' => 'Campaign Banner','inputId' => 'event-image-input','previewId' => 'event-image-preview']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('event-image-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($bannerSrc),'has-uploaded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isEdit && $fundraiser->hasUploadedBanner()),'contain' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isEdit && $fundraiser->bannerNeedsContainLayout()),'orientation' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isEdit ? $fundraiser->bannerOrientation() : null),'warn-portrait' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isEdit && $fundraiser->bannerNeedsContainLayout()),'label' => 'Campaign Banner','input-id' => 'event-image-input','preview-id' => 'event-image-preview']); ?>
                        <input id="event-image-input" type="file" name="banner" accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100 file:mr-4 file:rounded-lg file:border-0 file:bg-violet-500/20 file:px-3 file:py-1.5 file:text-sm file:text-violet-200">
                        <div class="mt-2 rounded-xl border border-slate-800 bg-slate-950/40 px-3 py-2 text-xs text-slate-400">
                            <p class="font-semibold text-slate-300">Upload guidelines</p>
                            <ul class="mt-1 list-inside list-disc space-y-0.5">
                                <li>Recommended: <span class="text-slate-200">1600 × 900 px</span> · 16:9 landscape</li>
                                <li>Formats: JPG / PNG · Maximum 2 MB</li>
                                <li>Portrait uploads stay fully visible over a blurred backdrop</li>
                            </ul>
                        </div>
                        <?php $__errorArgs = ['banner'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-400"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3da75ac8d1b06da7c4908acd9e717d9d)): ?>
<?php $attributes = $__attributesOriginal3da75ac8d1b06da7c4908acd9e717d9d; ?>
<?php unset($__attributesOriginal3da75ac8d1b06da7c4908acd9e717d9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3da75ac8d1b06da7c4908acd9e717d9d)): ?>
<?php $component = $__componentOriginal3da75ac8d1b06da7c4908acd9e717d9d; ?>
<?php unset($__componentOriginal3da75ac8d1b06da7c4908acd9e717d9d); ?>
<?php endif; ?>
                </div>
            </section>

            
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:p-5">
                <h2 class="text-sm font-bold uppercase tracking-wide text-violet-200">Campaign Visibility</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Visibility</label>
                        <select name="visibility" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                            <?php $__currentLoopData = $visibilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $visibility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($visibility->value); ?>" <?php if(old('visibility', $fundraiser?->visibility?->value ?? 'public') === $visibility->value): echo 'selected'; endif; ?>><?php echo e($visibility->label()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                        <?php $__errorArgs = ['visibility'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-300"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <label class="mt-6 inline-flex items-center gap-2 text-sm text-slate-300">
                        <input type="checkbox" name="is_featured" value="1" <?php if(old('is_featured', $fundraiser?->is_featured ?? false)): echo 'checked'; endif; ?>
                            class="rounded border-slate-600 bg-slate-900 text-violet-500 focus:ring-violet-500/40">
                        Featured Campaign
                    </label>
                    <label class="mt-6 inline-flex items-center gap-2 text-sm text-slate-300">
                        <input type="checkbox" name="accept_donations" value="1" <?php if(old('accept_donations', $fundraiser?->accept_donations ?? true)): echo 'checked'; endif; ?>
                            class="rounded border-slate-600 bg-slate-900 text-violet-500 focus:ring-violet-500/40">
                        Accept Donations
                    </label>
                </div>
            </section>

            <?php if($isEdit): ?>
                
                <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:p-5">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-violet-200">Donation Statistics</h2>
                    <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-6">
                        <?php $__currentLoopData = [
                            ['Total Donations', number_format($donationStats['total'])],
                            ['Successful', number_format($donationStats['successful'])],
                            ['Pending', number_format($donationStats['pending'])],
                            ['Cancelled', number_format($donationStats['cancelled'])],
                            ['Average', '₱'.number_format($donationStats['average'], 2)],
                            ['Largest', '₱'.number_format($donationStats['largest'], 2)],
                        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $value]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-3 py-2.5">
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e($label); ?></p>
                                <p class="mt-1 truncate text-sm font-bold text-white"><?php echo e($value); ?></p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </section>

                
                <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:p-5">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-violet-200">Audit Information</h2>
                    <dl class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 text-sm">
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Created By</dt>
                            <dd class="mt-0.5 text-slate-200"><?php echo e($fundraiser->creator?->name ?? '—'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Created Date</dt>
                            <dd class="mt-0.5 text-slate-200"><?php echo e(optional($fundraiser->created_at)->format('M d, Y g:i A') ?? '—'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Last Updated By</dt>
                            <dd class="mt-0.5 text-slate-200"><?php echo e($fundraiser->updater?->name ?? '—'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Last Updated</dt>
                            <dd class="mt-0.5 text-slate-200"><?php echo e(optional($fundraiser->updated_at)->format('M d, Y g:i A') ?? '—'); ?></dd>
            </div>
                    </dl>
                </section>
            <?php endif; ?>

            
            <div class="flex flex-wrap gap-3 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <a href="<?php echo e(route('admin.fundraisers.index')); ?>" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm font-semibold text-slate-300 hover:bg-slate-800">Cancel</a>
                <?php if($isEdit): ?>
                    <a href="<?php echo e(route('admin.fundraisers.preview', $fundraiser)); ?>" target="_blank" rel="noopener noreferrer"
                        class="rounded-xl border border-cyan-500/30 px-5 py-2.5 text-sm font-semibold text-cyan-200 hover:bg-cyan-500/10">
                        Preview Campaign
                    </a>
                <?php endif; ?>
                <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90">
                    <?php echo e($isEdit ? 'Save Changes' : 'Create Campaign'); ?>

                </button>
            </div>
        </form>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal57da683fe32826f08aa9f05c3342a7e2)): ?>
<?php $attributes = $__attributesOriginal57da683fe32826f08aa9f05c3342a7e2; ?>
<?php unset($__attributesOriginal57da683fe32826f08aa9f05c3342a7e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal57da683fe32826f08aa9f05c3342a7e2)): ?>
<?php $component = $__componentOriginal57da683fe32826f08aa9f05c3342a7e2; ?>
<?php unset($__componentOriginal57da683fe32826f08aa9f05c3342a7e2); ?>
<?php endif; ?>

    <?php echo app('Illuminate\Foundation\Vite')('resources/js/event-image-preview.js'); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/fundraisers/form.blade.php ENDPATH**/ ?>
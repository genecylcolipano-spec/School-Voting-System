<?php
    $election = $election ?? null;
    $isEdit = $election !== null;
    $formAction = $isEdit ? route('admin.elections.update', $election) : route('admin.elections.store');
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => $isEdit ? 'Manage Election' : 'Create Election','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isEdit ? 'Manage Election' : 'Create Election'),'user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <form
            id="election-setup-form"
            method="POST"
            action="<?php echo e($formAction); ?>"
            enctype="multipart/form-data"
            class="space-y-6"
        >
            <?php echo csrf_field(); ?>
            <?php if($isEdit): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

            
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-6">
                <div class="mb-5 flex items-center gap-3">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-violet-600 text-sm font-bold text-white">1</span>
                    <div>
                        <h2 class="text-lg font-semibold text-white">Election Details</h2>
                        <p class="text-sm text-slate-400">Title, schedule, and status</p>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <?php echo $__env->make('admin.partials.form-input', ['label' => 'Title', 'name' => 'title', 'value' => optional($election)->title, 'required' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300">Description</label>
                        <textarea name="description" rows="3" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100"><?php echo e(old('description', optional($election)->description)); ?></textarea>
                    </div>
                    <?php echo $__env->make('admin.partials.form-input', ['label' => 'Voting starts', 'name' => 'voting_starts_at', 'type' => 'datetime-local', 'value' => optional(optional($election)->voting_starts_at)->format('Y-m-d\TH:i')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php echo $__env->make('admin.partials.form-input', ['label' => 'Voting ends', 'name' => 'voting_ends_at', 'type' => 'datetime-local', 'value' => optional(optional($election)->voting_ends_at)->format('Y-m-d\TH:i')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Status</label>
                        <select name="status" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                            <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($status->value); ?>" <?php if(old('status', optional($election)->status?->value) === $status->value): echo 'selected'; endif; ?>><?php echo e(ucfirst($status->value)); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
            </section>

            
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-6">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-violet-600 text-sm font-bold text-white">2</span>
                        <div>
                            <h2 class="text-lg font-semibold text-white">Positions</h2>
                            <p class="text-sm text-slate-400">Define offices students will vote for</p>
                        </div>
                    </div>
                    <button type="button" data-add-position class="rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-200 hover:bg-violet-500/10">+ Add position</button>
                </div>

                <?php if($isEdit && $election->categories->isNotEmpty()): ?>
                    <div class="mb-4 rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Current positions</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <?php $__currentLoopData = $election->categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="rounded-full bg-violet-500/15 px-3 py-1 text-xs font-medium text-violet-200"><?php echo e($category->name); ?></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div id="positions-list" class="space-y-3" data-prefix="<?php echo e($isEdit ? 'new_positions' : 'positions'); ?>">
                    <?php if(! $isEdit): ?>
                        <div class="position-row grid gap-3 rounded-xl border border-slate-800 bg-slate-950/40 p-4 md:grid-cols-[1fr_auto]" data-index="0">
                            <input type="text" name="positions[0][name]" placeholder="e.g. President" class="rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100" />
                            <button type="button" data-remove-row class="rounded-lg border border-rose-500/30 px-3 py-2 text-xs font-semibold text-rose-300 hover:bg-rose-500/10">Remove</button>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-6">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-violet-600 text-sm font-bold text-white">3</span>
                        <div>
                            <h2 class="text-lg font-semibold text-white">Participating Campaigns</h2>
                            <p class="text-sm text-slate-400">Select which Active campaigns take part in this election</p>
                        </div>
                    </div>
                    <a href="<?php echo e(route('admin.campaigns.index')); ?>" class="rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-200 hover:bg-violet-500/10">Manage campaigns</a>
                </div>

                <?php if($campaigns->isEmpty()): ?>
                    <p class="rounded-xl border border-amber-500/20 bg-amber-950/20 px-4 py-3 text-sm text-amber-100">
                        No Active campaigns available.
                        <a href="<?php echo e(route('admin.campaigns.create')); ?>" class="font-semibold text-violet-300 hover:text-violet-200">Create a campaign</a>
                        and set it to Active first.
                    </p>
                <?php else: ?>
                    <div id="participating-campaigns" class="grid gap-2 sm:grid-cols-2">
                        <?php $__currentLoopData = $campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campaign): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="flex items-center gap-3 rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3 text-sm text-slate-200 hover:border-violet-500/40">
                                <input
                                    type="checkbox"
                                    name="partylists[]"
                                    value="<?php echo e($campaign->id); ?>"
                                    data-campaign-id="<?php echo e($campaign->id); ?>"
                                    data-campaign-name="<?php echo e($campaign->name); ?>"
                                    data-campaign-acronym="<?php echo e($campaign->acronym); ?>"
                                    <?php if(in_array($campaign->id, $selectedPartylistIds ?? [], true)): echo 'checked'; endif; ?>
                                    class="campaign-checkbox rounded border-slate-700 bg-slate-950/50 text-violet-500"
                                />
                                <span class="flex items-center gap-2">
                                    <?php if($campaign->color): ?>
                                        <span class="inline-block h-3 w-3 rounded-full" style="background: <?php echo e($campaign->color); ?>"></span>
                                    <?php endif; ?>
                                    <span class="font-medium text-white"><?php echo e($campaign->name); ?></span>
                                    <?php if($campaign->acronym): ?>
                                        <span class="text-xs text-violet-300"><?php echo e($campaign->acronym); ?></span>
                                    <?php endif; ?>
                                </span>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php $__errorArgs = ['partylists'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-2 text-sm text-rose-400"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <?php $__errorArgs = ['partylists.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-2 text-sm text-rose-400"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                <?php endif; ?>
            </section>

            
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-6">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-violet-600 text-sm font-bold text-white">4</span>
                        <div>
                            <h2 class="text-lg font-semibold text-white">Candidates</h2>
                            <p class="text-sm text-slate-400">Assign candidates to positions</p>
                        </div>
                    </div>
                    <button type="button" data-add-candidate class="rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-200 hover:bg-violet-500/10">+ Add candidate</button>
                </div>

                <?php if($isEdit && $election->candidates->isNotEmpty()): ?>
                    <div id="existing-candidates" class="mb-4 space-y-3">
                        <?php $__currentLoopData = $election->candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $existingPhotoUrl = \App\Support\EventImageUrl::hasUploadedImage($candidate->photo_path)
                                    ? \App\Support\EventImageUrl::resolve($candidate->photo_path)
                                    : null;
                            ?>
                            <div class="candidate-row grid gap-3 rounded-xl border border-slate-800 bg-slate-950/40 p-4 md:grid-cols-2">
                                <?php echo $__env->make('admin.elections.partials.candidate-photo-field', [
                                    'inputName' => "existing_candidates[{$candidate->id}][photo]",
                                    'removeName' => "existing_candidates[{$candidate->id}][remove_photo]",
                                    'photoUrl' => $existingPhotoUrl,
                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                <input type="text" name="existing_candidates[<?php echo e($candidate->id); ?>][display_name]" value="<?php echo e(old("existing_candidates.{$candidate->id}.display_name", $candidate->display_name)); ?>" placeholder="Display name" class="rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100" />
                                <select name="existing_candidates[<?php echo e($candidate->id); ?>][election_category_id]" class="rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                                    <?php $__currentLoopData = $election->categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($category->id); ?>" <?php if(old("existing_candidates.{$candidate->id}.election_category_id", $candidate->election_category_id) == $category->id): echo 'selected'; endif; ?>><?php echo e($category->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <select name="existing_candidates[<?php echo e($candidate->id); ?>][partylist_id]" class="rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                                    <option value="">— Independent (no campaign) —</option>
                                    <?php $__currentLoopData = $election->partylists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campaign): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($campaign->id); ?>" <?php if(old("existing_candidates.{$candidate->id}.partylist_id", $candidate->partylist_id) == $campaign->id): echo 'selected'; endif; ?>><?php echo e($campaign->name); ?><?php if($campaign->acronym): ?> (<?php echo e($campaign->acronym); ?>)<?php endif; ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <label class="flex items-center gap-2 text-sm text-slate-300">
                                    <input type="checkbox" name="existing_candidates[<?php echo e($candidate->id); ?>][is_active]" value="1" <?php if(old("existing_candidates.{$candidate->id}.is_active", $candidate->is_active)): echo 'checked'; endif; ?> class="rounded border-slate-700 bg-slate-950/50 text-violet-500" />
                                    Active
                                </label>
                                <textarea name="existing_candidates[<?php echo e($candidate->id); ?>][platform]" rows="2" placeholder="Platform" class="md:col-span-2 rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100"><?php echo e(old("existing_candidates.{$candidate->id}.platform", $candidate->platform)); ?></textarea>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>

                <div
                    id="candidates-list"
                    class="space-y-3"
                    data-prefix="<?php echo e($isEdit ? 'new_candidates' : 'candidates'); ?>"
                    data-is-edit="<?php echo e($isEdit ? '1' : '0'); ?>"
                    data-categories="<?php echo e($isEdit ? $election->categories->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->toJson() : '[]'); ?>"
                ></div>
            </section>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-6 py-2.5 text-sm font-semibold text-white hover:opacity-90">
                    <?php echo e($isEdit ? 'Save changes' : 'Create election'); ?>

                </button>
                <a href="<?php echo e(route('admin.elections.index')); ?>" class="rounded-xl border border-slate-700 px-6 py-2.5 text-sm text-slate-300 hover:bg-slate-800">Cancel</a>
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

    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/election-form.js']); ?>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/elections/form.blade.php ENDPATH**/ ?>
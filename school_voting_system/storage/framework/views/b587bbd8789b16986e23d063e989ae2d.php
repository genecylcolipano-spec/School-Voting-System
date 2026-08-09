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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => 'Campaigns & Partylists','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Campaigns & Partylists','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <?php echo $__env->make('admin.partials.page-header', [
            'title' => 'Campaigns & Partylists',
            'description' => 'Create reusable campaigns and attach them to elections during setup.',
            'action' => route('admin.campaigns.create'),
            'actionLabel' => 'Add campaign',
            'showAction' => $canManage,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="grid gap-4 lg:grid-cols-2">
            <?php $__empty_1 = true; $__currentLoopData = $partylists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $partylist): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <article class="overflow-hidden rounded-2xl border border-violet-500/15 bg-slate-900/70">
                    <?php if($partylist->bannerUrl()): ?>
                        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'relative overflow-hidden bg-slate-950',
                            'aspect-[16/5] w-full' => ! $partylist->isPortraitBanner(),
                            'aspect-[3/4] max-h-40 w-full' => $partylist->isPortraitBanner(),
                        ]); ?>">
                            <?php echo $__env->make('student.campaigns._banner-media', [
                                'url' => $partylist->bannerUrl(),
                                'alt' => $partylist->name.' banner',
                                'portrait' => $partylist->isPortraitBanner(),
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </div>
                    <?php else: ?>
                        <div class="aspect-[16/5] w-full bg-gradient-to-br from-slate-900 via-slate-950 to-violet-950/40"></div>
                    <?php endif; ?>
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <?php if($partylist->logo_path): ?>
                                    <img src="<?php echo e(\Illuminate\Support\Facades\Storage::disk('public')->url($partylist->logo_path)); ?>" alt="<?php echo e($partylist->name); ?> logo" class="h-12 w-12 rounded-lg border border-slate-700 object-cover">
                                <?php endif; ?>
                                <div>
                                    <h3 class="text-lg font-semibold text-white"><?php echo e($partylist->name); ?></h3>
                                    <?php if($partylist->acronym): ?>
                                        <p class="text-sm text-violet-300"><?php echo e($partylist->acronym); ?></p>
                                    <?php endif; ?>
                                    <?php if($partylist->motto): ?>
                                        <p class="mt-1 text-xs italic text-slate-500">"<?php echo e($partylist->motto); ?>"</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (isset($component)) { $__componentOriginal8f4964f6c5a17b269675c114ea0c864c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f4964f6c5a17b269675c114ea0c864c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-status-badge','data' => ['status' => $partylist->status->value]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($partylist->status->value)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8f4964f6c5a17b269675c114ea0c864c)): ?>
<?php $attributes = $__attributesOriginal8f4964f6c5a17b269675c114ea0c864c; ?>
<?php unset($__attributesOriginal8f4964f6c5a17b269675c114ea0c864c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8f4964f6c5a17b269675c114ea0c864c)): ?>
<?php $component = $__componentOriginal8f4964f6c5a17b269675c114ea0c864c; ?>
<?php unset($__componentOriginal8f4964f6c5a17b269675c114ea0c864c); ?>
<?php endif; ?>
                        </div>

                        <?php if($partylist->platform): ?>
                            <p class="mt-3 text-sm text-slate-400"><?php echo e(\Illuminate\Support\Str::limit($partylist->platform, 160)); ?></p>
                        <?php endif; ?>

                        <div class="mt-3 flex flex-wrap gap-3 text-xs text-slate-500">
                            <span><?php echo e($partylist->elections_count); ?> election(s)</span>
                            <span><?php echo e($partylist->candidates_count); ?> candidate(s)</span>
                            <span><?php echo e($partylist->posters_count); ?> poster(s)</span>
                            <?php if($partylist->leader): ?>
                                <span>Leader: <?php echo e($partylist->leader); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="mt-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Posters</p>

                            <?php if($partylist->posters->isNotEmpty()): ?>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <?php $__currentLoopData = $partylist->posters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $poster): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <a
                                            href="<?php echo e($poster->hasUploadedFile() ? $poster->file_url : '#'); ?>"
                                            target="_blank"
                                            rel="noopener"
                                            class="group relative overflow-hidden rounded-lg border border-slate-800"
                                            title="<?php echo e(ucfirst($poster->status)); ?>"
                                        >
                                            <?php if($poster->hasUploadedFile()): ?>
                                                <img src="<?php echo e($poster->file_url); ?>" alt="<?php echo e($partylist->name); ?> poster" class="h-24 w-auto max-w-[6rem] rounded object-contain transition group-hover:opacity-90">
                                            <?php else: ?>
                                                <div class="flex h-24 w-24 items-center justify-center bg-slate-950 text-xs text-slate-500">No file</div>
                                            <?php endif; ?>
                                            <span class="absolute bottom-1 right-1 rounded bg-black/70 px-1.5 py-0.5 text-[10px] text-white"><?php echo e($poster->status); ?></span>
                                        </a>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php else: ?>
                                <p class="mt-2 text-sm text-slate-500">No poster uploaded yet.</p>
                            <?php endif; ?>

                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $partylist)): ?>
                                <form
                                    method="POST"
                                    action="<?php echo e(route('admin.campaigns.poster.store', $partylist)); ?>"
                                    enctype="multipart/form-data"
                                    class="mt-3 flex flex-wrap items-center gap-2"
                                >
                                    <?php echo csrf_field(); ?>
                                    <label class="flex-1 min-w-[12rem]">
                                        <span class="sr-only">Poster image for <?php echo e($partylist->name); ?></span>
                                        <input
                                            type="file"
                                            name="poster_image"
                                            accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                                            required
                                            class="w-full rounded-xl border border-slate-700 bg-slate-950/50 px-3 py-2 text-xs text-slate-100 file:mr-2 file:rounded-lg file:border-0 file:bg-violet-500/20 file:px-2 file:py-1 file:text-xs file:text-violet-300"
                                        >
                                    </label>
                                    <button type="submit" class="rounded-lg bg-violet-600 px-3 py-2 text-xs font-semibold text-white hover:bg-violet-500">
                                        Upload poster
                                    </button>
                                </form>
                                <p class="mt-1 text-[10px] text-slate-500">Portrait or landscape JPG/PNG, max 2MB. Available once attached to an election.</p>
                            <?php endif; ?>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-3 text-sm">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $partylist)): ?>
                                <a href="<?php echo e(route('admin.campaigns.edit', $partylist)); ?>" class="text-violet-300 hover:text-violet-200">Edit details</a>
                            <?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $partylist)): ?>
                                <form method="POST" action="<?php echo e(route('admin.campaigns.destroy', $partylist)); ?>" class="inline" onsubmit="return confirm('Delete this campaign?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="text-rose-300 hover:text-rose-200">Delete</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="lg:col-span-2 rounded-2xl border border-slate-800 bg-slate-900/70 px-6 py-8 text-center text-slate-400">
                    No campaigns yet.
                    <?php if($canManage): ?>
                        <a href="<?php echo e(route('admin.campaigns.create')); ?>" class="ml-1 text-violet-300 hover:text-violet-200">Add your first campaign</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <p class="mt-6 text-xs text-slate-500">Active campaigns can be attached to elections during election setup and appear on the student portal automatically.</p>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/campaigns/index.blade.php ENDPATH**/ ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => 'Participant Details','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Participant Details','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <?php if(session('success')): ?>
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <div class="mb-4 flex flex-wrap items-center gap-2">
            <a href="<?php echo e(route('admin.talent-participants.index', ['event' => $entry->talent_event_id])); ?>" class="text-sm font-semibold text-violet-300 hover:text-violet-200">← Participants</a>
            <span class="text-slate-600">·</span>
            <a href="<?php echo e(route('admin.talent-competition.show', $entry->talentEvent)); ?>" class="text-sm font-semibold text-slate-400 hover:text-white"><?php echo e($entry->talentEvent?->title); ?></a>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-1">
                <div class="overflow-hidden rounded-2xl border border-violet-500/15 bg-slate-900/70">
                    <?php if($entry->photoUrl()): ?>
                        <img src="<?php echo e($entry->photoUrl()); ?>" alt="" class="aspect-square w-full object-cover">
                    <?php else: ?>
                        <div class="flex aspect-square items-center justify-center bg-violet-500/10 text-5xl font-bold text-violet-200">
                            <?php echo e(strtoupper(substr($entry->display_name, 0, 1))); ?>

                        </div>
                    <?php endif; ?>
                    <div class="p-5">
                        <h1 class="text-xl font-bold text-white"><?php echo e($entry->display_name); ?></h1>
                        <p class="mt-1 text-sm text-slate-400"><?php echo e($entry->student_id_number ?: 'No Student ID'); ?></p>
                        <div class="mt-3">
                            <?php if (isset($component)) { $__componentOriginal8f4964f6c5a17b269675c114ea0c864c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f4964f6c5a17b269675c114ea0c864c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-status-badge','data' => ['status' => $entry->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($entry->status)]); ?>
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
                        <p class="mt-2 text-[11px] uppercase tracking-wide text-slate-500">
                            <?php echo e($entry->source === 'self' ? 'Self-registered' : 'Admin-added'); ?>

                        </p>
                    </div>
                </div>

                <?php if($canManage): ?>
                    <div class="flex flex-wrap gap-2">
                        <a href="<?php echo e(route('admin.talent-participants.edit', $entry)); ?>" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white hover:opacity-90">Edit</a>

                        <?php if($entry->status !== \App\Models\TalentEventEntry::STATUS_APPROVED): ?>
                            <form method="POST" action="<?php echo e(route('admin.talent.entries.approve', $entry)); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="rounded-xl border border-emerald-500/40 px-4 py-2 text-sm font-semibold text-emerald-200 hover:bg-emerald-500/10">Approve</button>
                            </form>
                        <?php endif; ?>

                        <?php if($entry->status !== \App\Models\TalentEventEntry::STATUS_REJECTED): ?>
                            <form method="POST" action="<?php echo e(route('admin.talent.entries.reject', $entry)); ?>" onsubmit="this.querySelector('[name=reason]').value = prompt('Reason for rejection:') || ''; return this.querySelector('[name=reason]').value !== '';">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="reason" value="">
                                <button type="submit" class="rounded-xl border border-rose-500/40 px-4 py-2 text-sm font-semibold text-rose-200 hover:bg-rose-500/10">Reject</button>
                            </form>
                        <?php endif; ?>

                        <form method="POST" action="<?php echo e(route('admin.talent.entries.status', $entry)); ?>">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="status" value="withdrawn">
                            <button type="submit" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Withdraw</button>
                        </form>
                        <form method="POST" action="<?php echo e(route('admin.talent.entries.status', $entry)); ?>">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="status" value="disqualified">
                            <button type="submit" class="rounded-xl border border-amber-500/40 px-4 py-2 text-sm font-semibold text-amber-200 hover:bg-amber-500/10">Disqualify</button>
                        </form>
                        <form method="POST" action="<?php echo e(route('admin.talent.entries.status', $entry)); ?>">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="status" value="archived">
                            <button type="submit" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Archive</button>
                        </form>

                        <form method="POST" action="<?php echo e(route('admin.talent-participants.destroy', $entry)); ?>" onsubmit="return confirm('Delete this participant permanently?');">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="rounded-xl border border-rose-500/40 px-4 py-2 text-sm font-semibold text-rose-200 hover:bg-rose-500/10">Delete</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <div class="space-y-6 lg:col-span-2">
                <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                    <h2 class="text-base font-semibold text-white">Student Information</h2>
                    <dl class="mt-4 grid gap-3 sm:grid-cols-2 text-sm">
                        <div><dt class="text-slate-500">Grade / Year</dt><dd class="text-slate-200"><?php echo e($entry->grade_level ?: '—'); ?></dd></div>
                        <div><dt class="text-slate-500">Section</dt><dd class="text-slate-200"><?php echo e($entry->section ?: '—'); ?></dd></div>
                        <div><dt class="text-slate-500">Course / Strand</dt><dd class="text-slate-200"><?php echo e($entry->course_strand ?: '—'); ?></dd></div>
                        <div><dt class="text-slate-500">Linked Account</dt><dd class="text-slate-200"><?php echo e($entry->student?->email ?: '—'); ?></dd></div>
                        <div><dt class="text-slate-500">Submitted</dt><dd class="text-slate-200"><?php echo e(optional($entry->submitted_at ?? $entry->created_at)->format('M d, Y g:i A')); ?></dd></div>
                        <div><dt class="text-slate-500">Reviewed</dt><dd class="text-slate-200"><?php echo e(optional($entry->reviewed_at)->format('M d, Y g:i A') ?: '—'); ?><?php if($entry->reviewer): ?> · <?php echo e($entry->reviewer->name); ?><?php endif; ?></dd></div>
                    </dl>
                    <?php if($entry->review_reason): ?>
                        <p class="mt-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">Reason: <?php echo e($entry->review_reason); ?></p>
                    <?php endif; ?>
                </section>

                <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                    <h2 class="text-base font-semibold text-white">Performance</h2>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <?php if($entry->talentCategoryLabel()): ?>
                            <span class="rounded-full border border-violet-400/40 bg-violet-500/10 px-3 py-0.5 text-xs font-semibold text-violet-200"><?php echo e($entry->talentCategoryLabel()); ?></span>
                        <?php endif; ?>
                        <span class="text-sm font-semibold text-white"><?php echo e($entry->performance_title ?: 'Untitled performance'); ?></span>
                    </div>
                    <?php if($entry->profile_summary): ?>
                        <p class="mt-3 text-sm text-slate-400"><?php echo e($entry->profile_summary); ?></p>
                    <?php endif; ?>
                    <?php if($entry->performance_description): ?>
                        <p class="mt-2 text-sm text-slate-300"><?php echo e($entry->performance_description); ?></p>
                    <?php endif; ?>
                    <?php if($entry->social_media): ?>
                        <p class="mt-2 text-sm text-violet-300"><?php echo e($entry->social_media); ?></p>
                    <?php endif; ?>
                </section>

                <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-base font-semibold text-white">Performance Video</h2>
                        <div class="flex gap-2">
                            <?php if($entry->videoFileUrl()): ?>
                                <a href="<?php echo e($entry->videoFileUrl()); ?>" target="_blank" class="rounded-lg border border-violet-500/40 px-3 py-1.5 text-xs font-semibold text-violet-200 hover:bg-violet-500/10">Watch</a>
                                <?php if($entry->videoDownloadUrl()): ?>
                                    <a href="<?php echo e($entry->videoDownloadUrl()); ?>" class="rounded-lg border border-cyan-500/40 px-3 py-1.5 text-xs font-semibold text-cyan-200 hover:bg-cyan-500/10">Download</a>
                                <?php endif; ?>
                            <?php elseif($entry->video_url): ?>
                                <a href="<?php echo e($entry->video_url); ?>" target="_blank" rel="noopener" class="rounded-lg border border-violet-500/40 px-3 py-1.5 text-xs font-semibold text-violet-200 hover:bg-violet-500/10">Open URL</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-4 overflow-hidden rounded-xl border border-slate-800 bg-slate-950">
                        <?php if($entry->videoEmbedUrl()): ?>
                            <div class="aspect-video">
                                <iframe src="<?php echo e($entry->videoEmbedUrl()); ?>" class="h-full w-full" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                            </div>
                        <?php elseif($entry->videoFileUrl()): ?>
                            <video controls class="aspect-video w-full bg-black" poster="<?php echo e($entry->thumbnailUrl()); ?>">
                                <source src="<?php echo e($entry->videoFileUrl()); ?>">
                            </video>
                        <?php else: ?>
                            <div class="flex aspect-video items-center justify-center text-sm text-slate-500">No video submitted.</div>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                    <h2 class="text-base font-semibold text-white">Competition</h2>
                    <p class="mt-2 text-sm text-slate-300"><?php echo e($entry->talentEvent?->title); ?></p>
                    <p class="mt-1 text-xs text-slate-500"><?php echo e($entry->talentEvent?->displayStatusLabel()); ?> · <?php echo e($entry->talentEvent?->talent_category?->label()); ?></p>
                </section>
            </div>
        </div>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/talent-participants/show.blade.php ENDPATH**/ ?>
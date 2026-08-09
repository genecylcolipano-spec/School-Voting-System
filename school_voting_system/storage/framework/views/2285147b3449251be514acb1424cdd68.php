<?php
    $partylist = $partylist ?? null;
    $isEdit = $partylist !== null;
    $formAction = $isEdit ? route('admin.campaigns.update', $partylist) : route('admin.campaigns.store');
    $currentStatus = old('status', $isEdit ? ($partylist->status?->value ?? 'draft') : 'draft');
    $currentColor = old('color', optional($partylist)->color ?? '#7c3aed');
    $logoUrl = optional($partylist)->logo_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($partylist->logo_path) : null;
    $bannerUrl = optional($partylist)->banner_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($partylist->banner_path) : null;
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => $isEdit ? 'Edit Campaign' : 'Create Campaign','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isEdit ? 'Edit Campaign' : 'Create Campaign'),'user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <div class="mb-4 rounded-xl border border-violet-500/15 bg-violet-950/20 px-4 py-3 text-sm text-violet-100">
            Campaigns are reusable. Create them once, then attach them to elections during election setup.
        </div>

        <form method="POST" action="<?php echo e($formAction); ?>" enctype="multipart/form-data" class="max-w-2xl space-y-4 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-6">
            <?php echo csrf_field(); ?>
            <?php if($isEdit): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

            <?php echo $__env->make('admin.partials.form-input', ['label' => 'Party name', 'name' => 'name', 'value' => optional($partylist)->name, 'required' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('admin.partials.form-input', ['label' => 'Acronym', 'name' => 'acronym', 'value' => optional($partylist)->acronym, 'maxlength' => 50], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <p class="-mt-2 text-xs text-slate-500">Short party code (e.g. PDP). Max 50 characters — use Party name for the full title.</p>

            <div>
                <label class="block text-sm font-medium text-slate-300">Party color</label>
                <div class="mt-1 flex items-center gap-3">
                    <input type="color" name="color" value="<?php echo e($currentColor); ?>" class="h-10 w-16 cursor-pointer rounded-lg border border-slate-700 bg-slate-950/50">
                    <span class="text-xs text-slate-500">Used as an accent on campaign cards and results.</span>
                </div>
                <?php $__errorArgs = ['color'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-400"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <?php echo $__env->make('admin.partials.form-input', ['label' => 'Party leader (optional)', 'name' => 'leader', 'value' => optional($partylist)->leader], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <?php echo $__env->make('admin.partials.form-input', ['label' => 'Motto', 'name' => 'motto', 'value' => optional($partylist)->motto], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div>
                <label class="block text-sm font-medium text-slate-300">Platform</label>
                <textarea name="platform" rows="4" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100"><?php echo e(old('platform', optional($partylist)->platform)); ?></textarea>
                <?php $__errorArgs = ['platform'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-400"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300">Description</label>
                <textarea name="description" rows="3" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100"><?php echo e(old('description', optional($partylist)->description)); ?></textarea>
                <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-400"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div x-data="campaignLogoPreview(<?php echo \Illuminate\Support\Js::from($logoUrl)->toHtml() ?>)">
                <label class="block text-sm font-medium text-slate-300">Campaign logo</label>
                <div class="mt-2 h-24 w-24 overflow-hidden rounded-xl border border-slate-700 bg-slate-950">
                    <template x-if="preview">
                        <img :src="preview" alt="Logo preview" class="h-full w-full object-cover object-center">
                    </template>
                    <template x-if="!preview">
                        <div class="flex h-full w-full items-center justify-center text-[10px] text-slate-600">1:1</div>
                    </template>
                </div>
                <input type="file" name="logo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    class="mt-2 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100 file:mr-4 file:rounded-lg file:border-0 file:bg-violet-500/20 file:px-3 file:py-1.5 file:text-sm file:text-violet-300"
                    @change="onFile($event)">
                <p class="mt-1 text-xs text-slate-500">
                    Recommended: <span class="text-slate-300">512 × 512 px</span> · Square (1:1) · JPG, PNG or WEBP · Max 2MB
                </p>
                <?php $__errorArgs = ['logo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-400"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div x-data="campaignBannerPreview(<?php echo \Illuminate\Support\Js::from($bannerUrl)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from(optional($partylist)->bannerNeedsContainLayout() ?? false)->toHtml() ?>)">
                <label class="block text-sm font-medium text-slate-300">Campaign banner (optional)</label>
                <p class="mt-1 text-xs text-slate-500">Used on campaign pages. Dedicated campaign posters can also be uploaded in <strong>Official Posters</strong>.</p>
                <div class="relative mt-2 aspect-video max-w-xl overflow-hidden rounded-xl border border-slate-700 bg-slate-950">
                    <template x-if="preview">
                        <div class="absolute inset-0">
                            <img x-show="contain" :src="preview" alt="" aria-hidden="true" class="absolute inset-0 h-full w-full scale-110 object-cover object-center blur-2xl brightness-[0.4] saturate-125">
                            <img :src="preview" alt="Banner preview" class="absolute inset-0 z-[1] h-full w-full object-center" :class="contain ? 'object-contain' : 'object-cover'">
                        </div>
                    </template>
                    <template x-if="!preview">
                        <div class="flex h-full w-full items-center justify-center text-xs text-slate-600">16:9 banner preview</div>
                    </template>
                </div>
                <input type="file" name="banner" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    class="mt-2 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100 file:mr-4 file:rounded-lg file:border-0 file:bg-violet-500/20 file:px-3 file:py-1.5 file:text-sm file:text-violet-300"
                    @change="onFile($event)">
                <p class="mt-1 text-xs text-slate-500">
                    Recommended: <span class="text-slate-300">1600 × 900 px</span> · Landscape (16:9) · JPG, PNG or WEBP · Max 2MB
                </p>
                <p class="mt-0.5 text-[11px] text-slate-600" x-text="hint"></p>
                <?php $__errorArgs = ['banner'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-400"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300">Status</label>
                <select name="status" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                    <?php $__currentLoopData = \App\Enums\CampaignStatus::options(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($option['value']); ?>" <?php if($currentStatus === $option['value']): echo 'selected'; endif; ?>><?php echo e($option['label']); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <p class="mt-1 text-xs text-slate-500">Only <strong>Active</strong> campaigns can be selected during election creation.</p>
                <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-rose-400"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90">Save</button>
                <a href="<?php echo e(route('admin.campaigns.index')); ?>" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm text-slate-300 hover:bg-slate-800">Cancel</a>
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

    <?php $__env->startPush('scripts'); ?>
        <script>
            function campaignLogoPreview(initial) {
                return {
                    preview: initial || null,
                    onFile(event) {
                        const file = event.target.files?.[0];
                        if (!file) return;
                        this.preview = URL.createObjectURL(file);
                    },
                };
            }

            function campaignBannerPreview(initial, initialContain) {
                return {
                    preview: initial || null,
                    contain: !!initialContain,
                    hint: 'Preview matches live campaign cards: landscape fills 16:9; portrait/square stay fully visible over a blurred backdrop.',
                    onFile(event) {
                        const file = event.target.files?.[0];
                        if (!file) return;
                        const url = URL.createObjectURL(file);
                        const img = new Image();
                        img.onload = () => {
                            this.contain = img.naturalWidth > 0 && img.naturalHeight > 0 && img.naturalWidth <= img.naturalHeight;
                            const orientation = this.contain
                                ? (img.naturalWidth === img.naturalHeight ? 'square' : 'portrait')
                                : 'landscape';
                            this.hint = `Selected ${img.naturalWidth}×${img.naturalHeight}px (${orientation}). Save to upload.`;
                            this.preview = url;
                        };
                        img.src = url;
                    },
                };
            }
        </script>
    <?php $__env->stopPush(); ?>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/campaigns/form.blade.php ENDPATH**/ ?>
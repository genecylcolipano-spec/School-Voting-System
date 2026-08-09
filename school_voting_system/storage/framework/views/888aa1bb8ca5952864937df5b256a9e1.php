<?php
    $event = $event ?? null;
    $isEdit = $event !== null;
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => $isEdit ? 'Edit Event' : 'Create Event','user' => $user,'notificationsCount' => $notificationsCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isEdit ? 'Edit Event' : 'Create Event'),'user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount)]); ?>
        <form method="POST" action="<?php echo e($isEdit ? route('admin.events.update', $event) : route('admin.events.store')); ?>" enctype="multipart/form-data" class="max-w-2xl space-y-4 rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-6">
            <?php echo csrf_field(); ?> <?php if($isEdit): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

            <?php echo $__env->make('admin.partials.form-input', ['label' => 'Title', 'name' => 'title', 'value' => optional($event)->title, 'required' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <div>
                <label class="block text-sm font-medium text-slate-300">Description</label>
                <textarea name="description" rows="4" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100"><?php echo e(old('description', optional($event)->description)); ?></textarea>
            </div>
            <?php if (isset($component)) { $__componentOriginal3da75ac8d1b06da7c4908acd9e717d9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3da75ac8d1b06da7c4908acd9e717d9d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.event-image-field','data' => ['src' => $isEdit ? $event->image_url : \App\Support\EventImageUrl::placeholder(),'hasUploaded' => $isEdit && $event->has_uploaded_image,'contain' => $isEdit && $event->bannerNeedsContainLayout(),'orientation' => $isEdit ? $event->imageOrientation() : null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('event-image-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isEdit ? $event->image_url : \App\Support\EventImageUrl::placeholder()),'has-uploaded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isEdit && $event->has_uploaded_image),'contain' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isEdit && $event->bannerNeedsContainLayout()),'orientation' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isEdit ? $event->imageOrientation() : null)]); ?>
                <input id="event-image-input" type="file" name="image" accept=".jpg,.jpeg,.png,image/jpeg,image/png" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100 file:mr-4 file:rounded-lg file:border-0 file:bg-cyan-500/20 file:px-3 file:py-1.5 file:text-sm file:text-cyan-300">
                <p class="mt-1 text-xs text-slate-500">
                    Recommended: <span class="text-slate-300">1600 × 900 px</span> · Landscape (16:9) · JPG or PNG · Max 2MB
                </p>
                <?php $__errorArgs = ['image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-1 text-sm text-rose-400"><?php echo e($message); ?></p>
                <?php unset($message);
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
            <?php echo $__env->make('admin.partials.form-input', ['label' => 'Event date', 'name' => 'event_date', 'type' => 'datetime-local', 'value' => optional(optional($event)->event_date)->format('Y-m-d\TH:i'), 'required' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('admin.partials.form-input', ['label' => 'Venue', 'name' => 'venue', 'value' => optional($event)->venue, 'required' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div>
                <label class="block text-sm font-medium text-slate-300">Status</label>
                <select name="status" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                    <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($status->value); ?>" <?php if(old('status', optional($event)->status?->value) === $status->value): echo 'selected'; endif; ?>><?php echo e(ucfirst($status->value)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-5 py-2.5 text-sm font-semibold text-slate-950">Save</button>
                <a href="<?php echo e(route('admin.events.index')); ?>" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm text-slate-300">Cancel</a>
            </div>
        </form>

        <?php if($isEdit): ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $event)): ?>
                <div class="mt-3 max-w-2xl">
                    <?php if (isset($component)) { $__componentOriginal469a4ba3cbb96eb4bd9792641d671d57 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal469a4ba3cbb96eb4bd9792641d671d57 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.delete-action','data' => ['action' => route('admin.events.destroy', $event),'buttonClass' => 'rounded-xl border border-rose-500/40 px-5 py-2.5 text-sm font-semibold text-rose-300 hover:bg-rose-500/10','label' => 'Delete event']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.delete-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.events.destroy', $event)),'button-class' => 'rounded-xl border border-rose-500/40 px-5 py-2.5 text-sm font-semibold text-rose-300 hover:bg-rose-500/10','label' => 'Delete event']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal469a4ba3cbb96eb4bd9792641d671d57)): ?>
<?php $attributes = $__attributesOriginal469a4ba3cbb96eb4bd9792641d671d57; ?>
<?php unset($__attributesOriginal469a4ba3cbb96eb4bd9792641d671d57); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal469a4ba3cbb96eb4bd9792641d671d57)): ?>
<?php $component = $__componentOriginal469a4ba3cbb96eb4bd9792641d671d57; ?>
<?php unset($__componentOriginal469a4ba3cbb96eb4bd9792641d671d57); ?>
<?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/events/form.blade.php ENDPATH**/ ?>
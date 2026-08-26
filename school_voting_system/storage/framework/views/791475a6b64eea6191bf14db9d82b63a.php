<?php if($paginator->hasPages()): ?>
    <nav role="navigation" aria-label="<?php echo e(__('Pagination Navigation')); ?>" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-xs text-slate-500">
            <?php if($paginator->firstItem()): ?>
                Showing
                <span class="font-medium text-slate-300"><?php echo e($paginator->firstItem()); ?></span>
                to
                <span class="font-medium text-slate-300"><?php echo e($paginator->lastItem()); ?></span>
                of
                <span class="font-medium text-slate-300"><?php echo e($paginator->total()); ?></span>
            <?php else: ?>
                <?php echo e($paginator->count()); ?>

                <?php echo e(__('results')); ?>

            <?php endif; ?>
        </p>

        <div class="flex flex-wrap items-center gap-1">
            <?php if($paginator->onFirstPage()): ?>
                <span class="inline-flex min-h-9 items-center rounded-lg border border-slate-700 px-3 py-1.5 text-xs text-slate-500">Previous</span>
            <?php else: ?>
                <a href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev" class="inline-flex min-h-9 items-center rounded-lg border border-slate-700 px-3 py-1.5 text-xs font-semibold text-slate-200 hover:border-violet-500/40 hover:text-white">Previous</a>
            <?php endif; ?>

            <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(is_string($element)): ?>
                    <span class="inline-flex min-h-9 min-w-9 items-center justify-center px-2 text-xs text-slate-500"><?php echo e($element); ?></span>
                <?php endif; ?>

                <?php if(is_array($element)): ?>
                    <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($page == $paginator->currentPage()): ?>
                            <span aria-current="page" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-lg bg-violet-600 px-2 text-xs font-semibold text-white"><?php echo e($page); ?></span>
                        <?php else: ?>
                            <a href="<?php echo e($url); ?>" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-lg border border-slate-700 px-2 text-xs text-slate-300 hover:border-violet-500/40 hover:text-white"><?php echo e($page); ?></a>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if($paginator->hasMorePages()): ?>
                <a href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next" class="inline-flex min-h-9 items-center rounded-lg border border-slate-700 px-3 py-1.5 text-xs font-semibold text-slate-200 hover:border-violet-500/40 hover:text-white">Next</a>
            <?php else: ?>
                <span class="inline-flex min-h-9 items-center rounded-lg border border-slate-700 px-3 py-1.5 text-xs text-slate-500">Next</span>
            <?php endif; ?>
        </div>
    </nav>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/partials/pagination.blade.php ENDPATH**/ ?>
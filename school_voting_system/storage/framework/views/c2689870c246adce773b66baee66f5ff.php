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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-portal','data' => ['title' => 'Super Admin Dashboard','user' => $user,'notificationsCount' => $notificationsCount,'assignedRole' => $user->staffRole?->name ?? 'Chief Super Admin']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-portal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Super Admin Dashboard'),'user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'notifications-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notificationsCount),'assigned-role' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user->staffRole?->name ?? 'Chief Super Admin')]); ?>

        
        <?php
            $maintenanceOn = app(\App\Services\SuperAdmin\MaintenanceModeService::class)->isEnabled();
            $servicesHealthy = ($systemHealth['overall'] ?? '') === 'Healthy';
        ?>
        <section class="overflow-hidden rounded-2xl border border-violet-500/20 bg-gradient-to-br from-violet-900/80 via-slate-900 to-indigo-900/40 p-6 sm:p-8">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0 max-w-3xl">
                    <span class="inline-flex max-w-full rounded-full bg-white/10 px-3 py-1 text-[10px] font-semibold uppercase tracking-wider text-violet-200 sm:text-xs">
                        Chief Super Admin Console
                    </span>
                    <h2 class="mt-4 text-2xl font-bold leading-tight tracking-tight text-white sm:text-3xl lg:text-4xl">
                        Chief Super Administrator
                    </h2>
                    <p class="mt-3 max-w-2xl text-sm leading-relaxed text-slate-400 sm:text-base">
                        Manage users, elections, competitions, fundraising, announcements, reports, security, backups, and overall system governance.
                    </p>

                    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                        <a
                            href="<?php echo e(route('super-admin.administrators.index')); ?>"
                            class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-900/30 transition hover:opacity-90 focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-400 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900 sm:w-auto"
                            aria-label="Manage users"
                        >
                            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Manage Users
                        </a>

                        <a
                            href="<?php echo e(route('admin.reports.index')); ?>"
                            class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border border-violet-400/40 bg-transparent px-5 py-2.5 text-sm font-semibold text-violet-100 transition hover:border-violet-300/60 hover:bg-violet-500/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-400 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900 sm:w-auto"
                            aria-label="View reports"
                        >
                            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Reports
                        </a>
                    </div>
                </div>
                <div class="shrink-0">
                    <?php if($maintenanceOn): ?>
                        <span class="inline-flex items-center gap-2 rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-1.5 text-xs font-semibold text-amber-200">
                            <span class="h-2 w-2 rounded-full bg-amber-400" aria-hidden="true"></span>
                            Maintenance Mode
                        </span>
                    <?php elseif($servicesHealthy): ?>
                        <span class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold text-emerald-200">
                            <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-400" aria-hidden="true"></span>
                            All Services Operational
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-2 rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-1.5 text-xs font-semibold text-amber-200">
                            <span class="h-2 w-2 rounded-full bg-amber-400" aria-hidden="true"></span>
                            System Attention Needed
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <?php if(session('success')): ?>
            <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <?php if(session('warning')): ?>
            <div class="rounded-xl border border-amber-500/20 bg-amber-500/10 px-4 py-3 text-sm text-amber-100"><?php echo e(session('warning')); ?></div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <?php if(session('enrollment_url')): ?>
            <div class="rounded-xl border border-violet-500/20 bg-slate-900/70 p-4">
                <p class="text-sm text-slate-300">Enrollment link (valid 2 hours):</p>
                <a href="<?php echo e(session('enrollment_url')); ?>" class="mt-2 block break-all text-sm text-violet-300 hover:text-violet-200"><?php echo e(session('enrollment_url')); ?></a>
            </div>
        <?php endif; ?>

        <?php if(session('enrollment_links')): ?>
            <div class="rounded-xl border border-violet-500/20 bg-slate-900/70 p-4">
                <p class="text-sm text-slate-300">Manual enrollment links (valid 2 hours):</p>
                <ul class="mt-2 space-y-2 text-sm">
                    <?php $__currentLoopData = session('enrollment_links'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li>
                            <span class="font-mono text-violet-200"><?php echo e($link['account_id'] ?? 'Account'); ?></span>
                            <a href="<?php echo e($link['url']); ?>" class="mt-1 block break-all text-violet-300 hover:text-violet-200"><?php echo e($link['url']); ?></a>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        
        <section class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4 xl:grid-cols-9">
            <?php $__currentLoopData = [
                ['label' => 'Students', 'value' => $statistics['students'], 'color' => 'emerald'],
                ['label' => 'Staff Admins', 'value' => $statistics['admins'], 'color' => 'cyan'],
                ['label' => 'Super Admins', 'value' => $statistics['super_admins'], 'color' => 'violet'],
                ['label' => 'Passkeys', 'value' => $statistics['passkeys'], 'color' => 'sky'],
                ['label' => 'Pending Recovery', 'value' => $statistics['pending_recoveries'], 'color' => 'rose'],
                ['label' => 'Active Elections', 'value' => $statistics['active_elections'], 'color' => 'amber'],
                ['label' => 'Votes Cast', 'value' => number_format($statistics['total_votes']), 'color' => 'indigo'],
                ['label' => 'Turnout', 'value' => $statistics['voter_turnout'].'%', 'color' => 'fuchsia'],
                ['label' => 'System', 'value' => $systemHealth['overall'], 'color' => $systemHealth['overall'] === 'Healthy' ? 'emerald' : 'amber'],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="rounded-2xl border border-violet-500/10 bg-slate-900/70 p-3 sm:p-4">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400"><?php echo e($stat['label']); ?></p>
                    <p class="mt-1 text-lg font-bold text-white sm:text-xl"><?php echo e($stat['value']); ?></p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </section>

        
        <section class="grid gap-4 lg:grid-cols-4">
            <?php $__currentLoopData = $systemHealth; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($key !== 'overall' && is_array($item)): ?>
                    <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                        <p class="text-xs font-semibold uppercase text-slate-400"><?php echo e(str($key)->replace('_', ' ')->title()); ?></p>
                        <p class="mt-2 flex items-center gap-2 text-sm text-white">
                            <span class="h-2 w-2 rounded-full <?php echo e($item['status'] === 'ok' ? 'bg-emerald-400' : ($item['status'] === 'warning' ? 'bg-amber-400' : 'bg-rose-400')); ?>"></span>
                            <?php echo e($item['message']); ?>

                        </p>
                    </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </section>

        <div class="grid gap-6 xl:grid-cols-2">
            
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 xl:col-span-2">
                <h3 class="text-lg font-semibold text-white">Granular Role & Permission Matrix</h3>
                <p class="mt-1 text-sm text-slate-400">Chief Super Admin, Operations Admin, Student Records Admin, Auditor, Read-Only Admin</p>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-left text-xs sm:text-sm">
                        <thead class="border-b border-slate-800 text-slate-400">
                            <tr>
                                <th class="px-3 py-2">Role</th>
                                <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <th class="px-2 py-2 text-center"><?php echo e($permission->label); ?></th>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            <?php $__currentLoopData = $staffRoles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="text-slate-200">
                                    <td class="px-3 py-3 font-medium"><?php echo e($role->name); ?></td>
                                    <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <td class="px-2 py-3 text-center">
                                            <?php if($role->permissions->contains('id', $permission->id)): ?>
                                                <span class="text-emerald-400">✓</span>
                                            <?php else: ?>
                                                <span class="text-slate-600">—</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </section>

            
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 xl:col-span-2">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h3 class="text-lg font-semibold text-white">Audit Log / Activity History</h3>
                    <div class="flex flex-wrap gap-2">
                        <form id="audit-filter-form" class="flex flex-wrap gap-2">
                            <select name="action_type" class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-1 text-xs text-white">
                                <option value="">All types</option>
                                <?php $__currentLoopData = ['auth','election','passkey','user','backup','security','report','system']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($type); ?>"><?php echo e(ucfirst($type)); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <select name="status" class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-1 text-xs text-white">
                                <option value="">All status</option>
                                <option value="success">Success</option>
                                <option value="failed">Failed</option>
                            </select>
                        </form>
                        <a href="<?php echo e(route('super-admin.audit.export')); ?>" class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-violet-500">Export CSV</a>
                    </div>
                </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-left text-xs sm:text-sm">
                        <thead class="border-b border-slate-800 text-slate-400">
                            <tr>
                                <th class="px-3 py-2">Timestamp</th>
                                <th class="px-3 py-2">Admin</th>
                                <th class="px-3 py-2">Action</th>
                                <th class="px-3 py-2">IP</th>
                                <th class="px-3 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            <?php $__empty_1 = true; $__currentLoopData = $auditLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="text-slate-200" data-audit-row data-type="<?php echo e($log->action_type?->value); ?>" data-status="<?php echo e($log->status); ?>">
                                    <td class="px-3 py-2 whitespace-nowrap"><?php echo e($log->created_at?->format('M d, H:i')); ?></td>
                                    <td class="px-3 py-2"><?php echo e($log->admin_name); ?></td>
                                    <td class="px-3 py-2"><?php echo e($log->action); ?></td>
                                    <td class="px-3 py-2 font-mono text-xs"><?php echo e($log->ip_address); ?></td>
                                    <td class="px-3 py-2">
                                        <span class="rounded-full px-2 py-0.5 text-xs <?php echo e($log->status === 'success' ? 'bg-emerald-500/15 text-emerald-300' : 'bg-rose-500/15 text-rose-300'); ?>"><?php echo e(ucfirst($log->status)); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="5" class="px-3 py-6 text-center text-slate-500">No audit entries yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 xl:col-span-2">
                <h3 class="text-lg font-semibold text-white">Advanced Passkey Management</h3>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-left text-xs sm:text-sm">
                        <thead class="border-b border-slate-800 text-slate-400">
                            <tr>
                                <th class="px-3 py-2">Account</th>
                                <th class="px-3 py-2">Credential ID</th>
                                <th class="px-3 py-2">Device</th>
                                <th class="px-3 py-2">Added</th>
                                <th class="px-3 py-2">Last Used</th>
                                <th class="px-3 py-2">Status</th>
                                <th class="px-3 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            <?php $__currentLoopData = $passkeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $passkey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="text-slate-200">
                                    <td class="px-3 py-2"><?php echo e($passkey->user?->name); ?></td>
                                    <td class="px-3 py-2 font-mono text-[10px]"><?php echo e(Str::limit($passkey->credential_id, 18)); ?></td>
                                    <td class="px-3 py-2"><?php echo e($passkey->device_name ?? $passkey->name); ?></td>
                                    <td class="px-3 py-2"><?php echo e($passkey->created_at?->format('M d, Y')); ?></td>
                                    <td class="px-3 py-2"><?php echo e($passkey->last_used_at?->diffForHumans() ?? 'Never'); ?></td>
                                    <td class="px-3 py-2"><?php echo e($passkey->status?->label() ?? 'Active'); ?></td>
                                    <td class="px-3 py-2">
                                        <div class="flex flex-wrap gap-1">
                                            <form method="POST" action="<?php echo e(route('super-admin.passkeys.action', $passkey)); ?>"><?php echo csrf_field(); ?><input type="hidden" name="action" value="revoke"><button class="text-rose-300 hover:text-rose-200 text-xs">Revoke</button></form>
                                            <form method="POST" action="<?php echo e(route('super-admin.passkeys.action', $passkey)); ?>"><?php echo csrf_field(); ?><input type="hidden" name="action" value="lost"><button class="text-amber-300 hover:text-amber-200 text-xs">Lost</button></form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </section>

            
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:col-span-2">
                <h3 class="text-lg font-semibold text-white">System Management</h3>
                <p class="mt-1 text-sm text-slate-400">Application-wide administration — settings, maintenance, backups, and audit logs.</p>
                <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <a href="<?php echo e(route('super-admin.system.settings.edit')); ?>" class="rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3 text-sm font-semibold text-violet-200 hover:border-violet-500/30 hover:bg-violet-500/10">System Settings</a>
                    <a href="<?php echo e(route('super-admin.system.maintenance.edit')); ?>" class="rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3 text-sm font-semibold text-violet-200 hover:border-violet-500/30 hover:bg-violet-500/10">Maintenance Mode</a>
                    <a href="<?php echo e(route('super-admin.system.backups.index')); ?>" class="rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3 text-sm font-semibold text-violet-200 hover:border-violet-500/30 hover:bg-violet-500/10">Backup & Restore</a>
                    <a href="<?php echo e(route('super-admin.system.audit.index')); ?>" class="rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3 text-sm font-semibold text-violet-200 hover:border-violet-500/30 hover:bg-violet-500/10">Audit Logs</a>
                </div>
            </section>
        </div>

        
        <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
            <h3 class="text-lg font-semibold text-white">Election Lifecycle Controls</h3>
            <div class="mt-4 space-y-4">
                <?php $__currentLoopData = $elections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $election): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-white"><?php echo e($election->title); ?></p>
                                <p class="text-xs text-slate-400"><?php echo e($election->status?->value); ?> · <?php echo e($election->votes_count); ?> votes · <?php echo e($election->candidates_count); ?> candidates
                                    <?php if($election->results_locked): ?> · <span class="text-amber-300">Results Locked</span> <?php endif; ?>
                                    <?php if($election->public_results_published): ?> · <span class="text-emerald-300">Results Published</span> <?php endif; ?>
                                    <?php if($election->is_paused): ?> · <span class="text-rose-300">Paused</span> <?php endif; ?>
                                </p>
                                <?php if($election->integrity_hash): ?>
                                    <p class="mt-1 font-mono text-[10px] text-slate-500">Hash: <?php echo e(Str::limit($election->integrity_hash, 32)); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="flex flex-wrap gap-1">
                                <?php $__currentLoopData = ['open' => 'Open', 'pause' => 'Pause', 'resume' => 'Resume', 'close' => 'Close', 'annul' => 'Annul', 'rerun' => 'Re-run', 'lock' => 'Lock Results', 'publish_results' => 'Publish Results', 'unpublish_results' => 'Unpublish']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <form method="POST" action="<?php echo e(route('super-admin.elections.action', $election)); ?>"><?php echo csrf_field(); ?><input type="hidden" name="action" value="<?php echo e($action); ?>"><button class="rounded-lg border border-slate-700 px-2 py-1 text-xs text-slate-300 hover:border-violet-500/40 hover:text-white"><?php echo e($label); ?></button></form>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                        <form method="POST" action="<?php echo e(route('super-admin.elections.action', $election)); ?>" class="mt-3 flex flex-wrap items-end gap-2">
                            <?php echo csrf_field(); ?><input type="hidden" name="action" value="schedule">
                            <input type="datetime-local" name="scheduled_open_at" class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-1 text-xs text-white">
                            <input type="datetime-local" name="scheduled_close_at" class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-1 text-xs text-white">
                            <button class="rounded-lg bg-indigo-600 px-3 py-1 text-xs font-semibold text-white">Schedule</button>
                        </form>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>

        
        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                <h3 class="text-lg font-semibold text-white">Vote Integrity & Verification</h3>
                <ul class="mt-4 space-y-3 text-sm text-slate-300">
                    <li class="flex justify-between"><span>Anonymization</span><span class="text-emerald-300">Enabled (no voter-candidate public link)</span></li>
                    <li class="flex justify-between"><span>Duplicate Vote Checker</span><span class="text-emerald-300">DB unique constraint per category</span></li>
                    <li class="flex justify-between"><span>Eligible Voters</span><span class="text-white"><?php echo e($statistics['eligible_students']); ?></span></li>
                    <li class="flex justify-between"><span>Total Voted</span><span class="text-white"><?php echo e($statistics['voted_students']); ?></span></li>
                    <li class="flex justify-between"><span>Turnout</span><span class="text-white"><?php echo e($statistics['voter_turnout']); ?>%</span></li>
                </ul>
            </section>
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                <h3 class="text-lg font-semibold text-white">Voter Eligibility</h3>
                <ul class="mt-4 space-y-3 text-sm">
                    <li class="flex justify-between text-emerald-300"><span>Enrolled</span><span><?php echo e($voterEligibility['enrolled']); ?></span></li>
                    <li class="flex justify-between text-amber-300"><span>Probation</span><span><?php echo e($voterEligibility['probation']); ?></span></li>
                    <li class="flex justify-between text-rose-300"><span>Withdrawn</span><span><?php echo e($voterEligibility['withdrawn']); ?></span></li>
                </ul>
                <div class="mt-4 flex flex-wrap gap-x-4 gap-y-2">
                    <a href="<?php echo e(route('admin.students.index')); ?>" class="text-sm font-semibold text-violet-300 hover:text-violet-200">Manage student records →</a>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('importStudentRecords')): ?>
                        <a href="<?php echo e(route('super-admin.roster.students.import')); ?>" class="text-sm font-semibold text-violet-300 hover:text-violet-200">Import student roster →</a>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        
        <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
            <form method="GET" class="mb-4 flex flex-wrap gap-3">
                <input
                    name="portal_q"
                    type="search"
                    value="<?php echo e(request('portal_q')); ?>"
                    placeholder="Search account ID, name, or email"
                    class="min-w-[16rem] flex-1 rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-sm text-slate-100"
                />
                <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white">Search Accounts</button>
                <?php if(request()->filled('portal_q')): ?>
                    <a href="<?php echo e(route('super-admin.dashboard')); ?>" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Clear</a>
                <?php endif; ?>
            </form>

            <form method="POST" action="<?php echo e(route('super-admin.users.bulk')); ?>" data-portal-bulk-form>
                <?php echo csrf_field(); ?>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-white">Portal Accounts & Bulk Actions</h3>
                        <p class="mt-1 text-xs text-slate-500"><?php echo e($portalUsers->total()); ?> account(s) · Deactivated users cannot sign in</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('importStudentRecords')): ?>
                            <a href="<?php echo e(route('super-admin.roster.students.index')); ?>" class="rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-300 hover:bg-violet-500/10">
                                Student Roster
                            </a>
                        <?php endif; ?>
                        <select name="action" data-portal-bulk-action class="rounded-lg border border-slate-700 bg-slate-950 px-3 py-1.5 text-xs text-white" required>
                            <option value="">Bulk action…</option>
                            <option value="activate">Activate</option>
                            <option value="deactivate">Deactivate</option>
                            <option value="resend_access">Resend Access</option>
                            <option value="export">Export CSV</option>
                            <option value="delete">Delete</option>
                        </select>
                        <button type="submit" class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white">Apply</button>
                    </div>
                </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b border-slate-800 text-xs uppercase text-slate-400">
                            <tr>
                                <th class="px-3 py-2"><input type="checkbox" id="bulk-select-all" class="rounded border-slate-600"></th>
                                <th class="px-3 py-2">Account ID</th>
                                <th class="px-3 py-2">Name</th>
                                <th class="px-3 py-2">Email</th>
                                <th class="px-3 py-2">Role</th>
                                <th class="px-3 py-2">Passkeys</th>
                                <th class="px-3 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 text-slate-200">
                            <?php $__empty_1 = true; $__currentLoopData = $portalUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $portalUser): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="px-3 py-3"><input type="checkbox" name="user_ids[]" value="<?php echo e($portalUser->id); ?>" data-bulk-user class="rounded border-slate-600"></td>
                                    <td class="px-3 py-3 font-mono text-xs"><?php echo e($portalUser->account_id); ?></td>
                                    <td class="px-3 py-3"><?php echo e($portalUser->name); ?></td>
                                    <td class="px-3 py-3 text-xs text-slate-400"><?php echo e($portalUser->email ?: '—'); ?></td>
                                    <td class="px-3 py-3 text-xs"><?php echo e($portalUser->staffRole?->name ?? str($portalUser->role?->value)->replace('_',' ')->title()); ?></td>
                                    <td class="px-3 py-3"><?php echo e($portalUser->passkeys_count); ?></td>
                                    <td class="px-3 py-3"><span class="<?php echo e($portalUser->is_active ? 'text-emerald-300' : 'text-rose-300'); ?>"><?php echo e($portalUser->is_active ? 'Active' : 'Inactive'); ?></span></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="px-3 py-6 text-center text-slate-400">No portal accounts match your search.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4"><?php echo e($portalUsers->links()); ?></div>
            </form>
        </section>

        
        <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
            <h3 class="text-lg font-semibold text-white">Compliance & Official Reports</h3>
            <div class="mt-4 flex flex-wrap gap-2">
                <?php $__currentLoopData = ['election_summary' => 'Election Summary', 'voter_turnout' => 'Voter Turnout', 'audit_trail' => 'Audit Trail', 'passkey_inventory' => 'Passkey Inventory']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('super-admin.reports.generate', ['report' => $key])); ?>" class="rounded-xl border border-violet-500/30 px-4 py-2 text-sm font-semibold text-violet-200 hover:bg-violet-500/10"><?php echo e($label); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <p class="mt-4 text-sm text-slate-400">
                Public results transparency is managed in
                <a href="<?php echo e(route('super-admin.system.settings.edit')); ?>" class="font-semibold text-violet-300 hover:text-violet-200">System Settings</a>.
            </p>
        </section>

        <?php if (isset($component)) { $__componentOriginala3f81ce1d43088179ca40e3639861b3d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala3f81ce1d43088179ca40e3639861b3d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.passkey-recovery-queue-dark','data' => ['recoveryRequests' => $recoveryRequests]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('passkey-recovery-queue-dark'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['recovery-requests' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recoveryRequests)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala3f81ce1d43088179ca40e3639861b3d)): ?>
<?php $attributes = $__attributesOriginala3f81ce1d43088179ca40e3639861b3d; ?>
<?php unset($__attributesOriginala3f81ce1d43088179ca40e3639861b3d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala3f81ce1d43088179ca40e3639861b3d)): ?>
<?php $component = $__componentOriginala3f81ce1d43088179ca40e3639861b3d; ?>
<?php unset($__componentOriginala3f81ce1d43088179ca40e3639861b3d); ?>
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

    <script>
        window.superAdminPortal = { searchUrl: <?php echo json_encode(route('super-admin.search'), 15, 512) ?> };
    </script>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/passkey-admin-recovery.js', 'resources/js/super-admin-dashboard.js']); ?>
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
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/super-dashboard.blade.php ENDPATH**/ ?>
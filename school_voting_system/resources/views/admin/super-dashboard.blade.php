<x-app-layout>
    <x-admin-portal :title="'Super Admin Dashboard'" :user="$user" :notifications-count="$notificationsCount" :assigned-role="$user->staffRole?->name ?? 'Chief Super Admin'">

        {{-- Hero --}}
        @php
            $maintenanceOn = app(\App\Services\SuperAdmin\MaintenanceModeService::class)->isEnabled();
            $servicesHealthy = ($systemHealth['overall'] ?? '') === 'Healthy';
        @endphp
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
                            href="{{ route('super-admin.administrators.index') }}"
                            class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-900/30 transition hover:opacity-90 focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-400 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900 sm:w-auto"
                            aria-label="Manage users"
                        >
                            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Manage Users
                        </a>

                        <a
                            href="{{ route('admin.reports.index') }}"
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
                    @if ($maintenanceOn)
                        <span class="inline-flex items-center gap-2 rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-1.5 text-xs font-semibold text-amber-200">
                            <span class="h-2 w-2 rounded-full bg-amber-400" aria-hidden="true"></span>
                            Maintenance Mode
                        </span>
                    @elseif ($servicesHealthy)
                        <span class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold text-emerald-200">
                            <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-400" aria-hidden="true"></span>
                            All Services Operational
                        </span>
                    @else
                        <span class="inline-flex items-center gap-2 rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-1.5 text-xs font-semibold text-amber-200">
                            <span class="h-2 w-2 rounded-full bg-amber-400" aria-hidden="true"></span>
                            System Attention Needed
                        </span>
                    @endif
                </div>
            </div>
        </section>

        @if (session('enrollment_url'))
            <div class="rounded-xl border border-violet-500/20 bg-slate-900/70 p-4">
                <p class="text-sm text-slate-300">Enrollment link (valid 2 hours):</p>
                <a href="{{ session('enrollment_url') }}" class="mt-2 block break-all text-sm text-violet-300 hover:text-violet-200">{{ session('enrollment_url') }}</a>
            </div>
        @endif

        @if (session('enrollment_links'))
            <div class="rounded-xl border border-violet-500/20 bg-slate-900/70 p-4">
                <p class="text-sm text-slate-300">Manual enrollment links (valid 2 hours):</p>
                <ul class="mt-2 space-y-2 text-sm">
                    @foreach (session('enrollment_links') as $link)
                        <li>
                            <span class="font-mono text-violet-200">{{ $link['account_id'] ?? 'Account' }}</span>
                            <a href="{{ $link['url'] }}" class="mt-1 block break-all text-violet-300 hover:text-violet-200">{{ $link['url'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Overview cards --}}
        <section class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-5 2xl:grid-cols-9">
            @foreach ([
                ['label' => 'Students', 'value' => $statistics['students']],
                ['label' => 'Faculty', 'value' => $statistics['faculty']],
                ['label' => 'Staff Admins', 'value' => $statistics['admins']],
                ['label' => 'Super Admins', 'value' => $statistics['super_admins']],
                ['label' => 'Passkeys', 'value' => $statistics['passkeys']],
                ['label' => 'Pending Recovery', 'value' => $statistics['pending_recoveries']],
                ['label' => 'Active Elections', 'value' => $statistics['active_elections']],
                ['label' => 'Live Votes', 'value' => number_format($statistics['total_votes'])],
                ['label' => 'Live Turnout', 'value' => $statistics['voter_turnout'].'%'],
            ] as $stat)
                <div class="min-w-0 rounded-2xl border border-violet-500/10 bg-slate-900/70 p-3 sm:p-4">
                    <p class="text-[10px] font-semibold uppercase leading-tight tracking-wide text-slate-400 break-words">{{ $stat['label'] }}</p>
                    <p class="mt-1 truncate text-lg font-bold text-white sm:text-xl">{{ $stat['value'] }}</p>
                </div>
            @endforeach
        </section>

        {{-- System status --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($systemHealth as $key => $item)
                @if ($key !== 'overall' && is_array($item))
                    <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                        <p class="text-xs font-semibold uppercase text-slate-400">{{ str($key)->replace('_', ' ')->title() }}</p>
                        <p class="mt-2 flex min-w-0 items-start gap-2 text-sm text-white">
                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $item['status'] === 'ok' ? 'bg-emerald-400' : ($item['status'] === 'warning' ? 'bg-amber-400' : 'bg-rose-400') }}"></span>
                            <span class="min-w-0 break-words">{{ $item['message'] }}</span>
                        </p>
                    </div>
                @endif
            @endforeach
        </section>

        {{-- Current activity --}}
        <section class="grid gap-4 lg:grid-cols-3">
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-white">Live election</h3>
                        <p class="mt-1 break-words text-sm text-slate-300">{{ $statistics['election_scope'] }}</p>
                    </div>
                    <a href="{{ route('admin.elections.index') }}" class="shrink-0 text-xs font-semibold text-violet-300 hover:text-violet-200">View all</a>
                </div>
                <p class="mt-3 text-xs text-slate-500">Votes and turnout on this dashboard are for the live election only.</p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-white">Competitions</h3>
                        <p class="mt-1 text-sm text-slate-300">{{ $activitySnapshot['competitions_open'] }} open</p>
                    </div>
                    <a href="{{ route('admin.talent-competition.index') }}" class="shrink-0 text-xs font-semibold text-violet-300 hover:text-violet-200">View all</a>
                </div>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse ($activitySnapshot['competitions'] as $competition)
                        <li>
                            <a href="{{ route('admin.talent-competition.show', $competition) }}" class="block min-w-0 rounded-lg border border-slate-800 px-3 py-2 hover:border-violet-500/30">
                                <span class="block truncate font-medium text-white">{{ $competition->title }}</span>
                                <span class="text-xs text-slate-400">{{ $competition->status?->label() }} · {{ $competition->votes_count }} votes</span>
                            </a>
                        </li>
                    @empty
                        <li class="text-xs text-slate-500">No open competitions.</li>
                    @endforelse
                </ul>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-white">Fundraising</h3>
                        <p class="mt-1 text-sm text-slate-300">{{ $activitySnapshot['fundraisers_active'] }} active</p>
                    </div>
                    <a href="{{ route('admin.fundraisers.index') }}" class="shrink-0 text-xs font-semibold text-violet-300 hover:text-violet-200">View all</a>
                </div>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse ($activitySnapshot['fundraisers'] as $fundraiser)
                        <li>
                            <a href="{{ route('admin.fundraisers.edit', $fundraiser) }}" class="block min-w-0 rounded-lg border border-slate-800 px-3 py-2 hover:border-violet-500/30">
                                <span class="block truncate font-medium text-white">{{ $fundraiser->title }}</span>
                                <span class="text-xs text-slate-400">₱{{ number_format((float) $fundraiser->amount_raised, 0) }} / ₱{{ number_format((float) $fundraiser->goal_amount, 0) }}</span>
                            </a>
                        </li>
                    @empty
                        <li class="text-xs text-slate-500">No active fundraisers.</li>
                    @endforelse
                </ul>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-2">
            {{-- Security: Permission matrix --}}
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 xl:col-span-2">
                <h3 class="text-lg font-semibold text-white">Granular Role & Permission Matrix</h3>
                <p class="mt-1 text-sm text-slate-400">Chief Super Admin, Operations Admin, Student Records Admin, Auditor, Read-Only Admin</p>

                <div class="mt-4 space-y-3 xl:hidden" data-permission-matrix-cards>
                    @forelse ($staffRoles as $role)
                        <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-3">
                            <p class="font-semibold text-white">{{ $role->name }}</p>
                            <ul class="mt-2 flex flex-wrap gap-1.5">
                                @forelse ($role->permissions as $permission)
                                    <li class="rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2 py-0.5 text-[11px] text-emerald-200">{{ $permission->label }}</li>
                                @empty
                                    <li class="text-xs text-slate-500">No permissions assigned</li>
                                @endforelse
                            </ul>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No staff roles defined yet.</p>
                    @endforelse
                </div>

                <div class="mt-4 hidden overflow-x-auto xl:block" data-permission-matrix-table>
                    <table class="min-w-full text-left text-xs sm:text-sm">
                        <thead class="border-b border-slate-800 text-slate-400">
                            <tr>
                                <th class="px-3 py-2">Role</th>
                                @foreach ($permissions as $permission)
                                    <th class="px-2 py-2 text-center">{{ $permission->label }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @foreach ($staffRoles as $role)
                                <tr class="text-slate-200">
                                    <td class="px-3 py-3 font-medium">{{ $role->name }}</td>
                                    @foreach ($permissions as $permission)
                                        <td class="px-2 py-3 text-center">
                                            @if ($role->permissions->contains('id', $permission->id))
                                                <span class="text-emerald-400">✓</span>
                                            @else
                                                <span class="text-slate-600">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- Audit log --}}
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 xl:col-span-2">
                <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
                    <h3 class="text-lg font-semibold text-white">Audit Log / Activity History</h3>
                    <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:flex-wrap">
                        <form id="audit-filter-form" class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:flex-wrap">
                            <select name="action_type" class="w-full rounded-lg border border-slate-700 bg-slate-950 px-2 py-1.5 text-xs text-white sm:w-auto">
                                <option value="">All types</option>
                                @foreach (['auth','election','passkey','user','backup','security','report','system'] as $type)
                                    <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                                @endforeach
                            </select>
                            <select name="status" class="w-full rounded-lg border border-slate-700 bg-slate-950 px-2 py-1.5 text-xs text-white sm:w-auto">
                                <option value="">All status</option>
                                <option value="success">Success</option>
                                <option value="failed">Failed</option>
                            </select>
                        </form>
                        <a href="{{ route('super-admin.audit.export') }}" class="inline-flex w-full items-center justify-center rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-violet-500 sm:w-auto">Export CSV</a>
                    </div>
                </div>

                <div class="mt-4 space-y-3 lg:hidden" data-audit-cards>
                    @forelse ($auditLogs as $log)
                        <article class="rounded-xl border border-slate-800 bg-slate-950/50 p-3" data-audit-row data-type="{{ $log->action_type?->value }}" data-status="{{ $log->status }}">
                            <div class="flex items-start justify-between gap-3">
                                <p class="min-w-0 break-words text-sm font-medium text-white">{{ $log->action }}</p>
                                <span class="shrink-0 rounded-full px-2 py-0.5 text-xs {{ $log->status === 'success' ? 'bg-emerald-500/15 text-emerald-300' : 'bg-rose-500/15 text-rose-300' }}">{{ ucfirst($log->status) }}</span>
                            </div>
                            <dl class="mt-2 grid grid-cols-2 gap-2 text-xs text-slate-400">
                                <div>
                                    <dt class="uppercase tracking-wide text-slate-500">When</dt>
                                    <dd class="mt-0.5 text-slate-200">{{ $log->created_at?->format('M d, H:i') }}</dd>
                                </div>
                                <div>
                                    <dt class="uppercase tracking-wide text-slate-500">Admin</dt>
                                    <dd class="mt-0.5 break-words text-slate-200">{{ $log->admin_name }}</dd>
                                </div>
                                <div class="col-span-2">
                                    <dt class="uppercase tracking-wide text-slate-500">IP</dt>
                                    <dd class="mt-0.5 font-mono text-slate-200">{{ $log->ip_address }}</dd>
                                </div>
                            </dl>
                        </article>
                    @empty
                        <p class="px-1 py-4 text-center text-sm text-slate-500">No audit entries yet.</p>
                    @endforelse
                </div>

                <div class="mt-4 hidden overflow-x-auto lg:block">
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
                            @forelse ($auditLogs as $log)
                                <tr class="text-slate-200" data-audit-row data-type="{{ $log->action_type?->value }}" data-status="{{ $log->status }}">
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $log->created_at?->format('M d, H:i') }}</td>
                                    <td class="px-3 py-2">{{ $log->admin_name }}</td>
                                    <td class="px-3 py-2">{{ $log->action }}</td>
                                    <td class="px-3 py-2 font-mono text-xs">{{ $log->ip_address }}</td>
                                    <td class="px-3 py-2">
                                        <span class="rounded-full px-2 py-0.5 text-xs {{ $log->status === 'success' ? 'bg-emerald-500/15 text-emerald-300' : 'bg-rose-500/15 text-rose-300' }}">{{ ucfirst($log->status) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-3 py-6 text-center text-slate-500">No audit entries yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- Passkey management --}}
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 xl:col-span-2">
                <h3 class="text-lg font-semibold text-white">Advanced Passkey Management</h3>

                <div class="mt-4 space-y-3 lg:hidden" data-passkey-cards>
                    @forelse ($passkeys as $passkey)
                        <article class="rounded-xl border border-slate-800 bg-slate-950/50 p-3">
                            <p class="break-words font-medium text-white">{{ $passkey->user?->name }}</p>
                            <p class="mt-1 break-all font-mono text-[10px] text-slate-500">{{ Str::limit($passkey->credential_id, 18) }}</p>
                            <dl class="mt-3 grid grid-cols-2 gap-2 text-xs text-slate-400">
                                <div>
                                    <dt class="uppercase tracking-wide text-slate-500">Device</dt>
                                    <dd class="mt-0.5 break-words text-slate-200">{{ $passkey->device_name ?? $passkey->name }}</dd>
                                </div>
                                <div>
                                    <dt class="uppercase tracking-wide text-slate-500">Status</dt>
                                    <dd class="mt-0.5 text-slate-200">{{ $passkey->status?->label() ?? 'Active' }}</dd>
                                </div>
                                <div>
                                    <dt class="uppercase tracking-wide text-slate-500">Added</dt>
                                    <dd class="mt-0.5 text-slate-200">{{ $passkey->created_at?->format('M d, Y') }}</dd>
                                </div>
                                <div>
                                    <dt class="uppercase tracking-wide text-slate-500">Last used</dt>
                                    <dd class="mt-0.5 text-slate-200">{{ $passkey->last_used_at?->diffForHumans() ?? 'Never' }}</dd>
                                </div>
                            </dl>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('super-admin.passkeys.action', $passkey) }}">@csrf<input type="hidden" name="action" value="revoke"><button class="rounded-lg border border-rose-500/30 px-3 py-1.5 text-xs font-semibold text-rose-300 hover:bg-rose-500/10">Revoke</button></form>
                                <form method="POST" action="{{ route('super-admin.passkeys.action', $passkey) }}">@csrf<input type="hidden" name="action" value="lost"><button class="rounded-lg border border-amber-500/30 px-3 py-1.5 text-xs font-semibold text-amber-300 hover:bg-amber-500/10">Lost</button></form>
                            </div>
                        </article>
                    @empty
                        <p class="px-1 py-4 text-center text-sm text-slate-500">No passkeys registered yet.</p>
                    @endforelse
                </div>

                <div class="mt-4 hidden overflow-x-auto lg:block">
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
                            @forelse ($passkeys as $passkey)
                                <tr class="text-slate-200">
                                    <td class="px-3 py-2">{{ $passkey->user?->name }}</td>
                                    <td class="px-3 py-2 font-mono text-[10px]">{{ Str::limit($passkey->credential_id, 18) }}</td>
                                    <td class="px-3 py-2">{{ $passkey->device_name ?? $passkey->name }}</td>
                                    <td class="px-3 py-2">{{ $passkey->created_at?->format('M d, Y') }}</td>
                                    <td class="px-3 py-2">{{ $passkey->last_used_at?->diffForHumans() ?? 'Never' }}</td>
                                    <td class="px-3 py-2">{{ $passkey->status?->label() ?? 'Active' }}</td>
                                    <td class="px-3 py-2">
                                        <div class="flex flex-wrap gap-1">
                                            <form method="POST" action="{{ route('super-admin.passkeys.action', $passkey) }}">@csrf<input type="hidden" name="action" value="revoke"><button class="text-rose-300 hover:text-rose-200 text-xs">Revoke</button></form>
                                            <form method="POST" action="{{ route('super-admin.passkeys.action', $passkey) }}">@csrf<input type="hidden" name="action" value="lost"><button class="text-amber-300 hover:text-amber-200 text-xs">Lost</button></form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-3 py-6 text-center text-slate-500">No passkeys registered yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- System Management shortcuts (full tools live under System Management) --}}
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:col-span-2">
                <h3 class="text-lg font-semibold text-white">System Management</h3>
                <p class="mt-1 text-sm text-slate-400">Application-wide administration — settings, maintenance, backups, and audit logs.</p>
                <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <a href="{{ route('super-admin.system.settings.edit') }}" class="rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3 text-sm font-semibold text-violet-200 hover:border-violet-500/30 hover:bg-violet-500/10">System Settings</a>
                    <a href="{{ route('super-admin.system.maintenance.edit') }}" class="rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3 text-sm font-semibold text-violet-200 hover:border-violet-500/30 hover:bg-violet-500/10">Maintenance Mode</a>
                    <a href="{{ route('super-admin.system.backups.index') }}" class="rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3 text-sm font-semibold text-violet-200 hover:border-violet-500/30 hover:bg-violet-500/10">Backup & Restore</a>
                    <a href="{{ route('super-admin.system.audit.index') }}" class="rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3 text-sm font-semibold text-violet-200 hover:border-violet-500/30 hover:bg-violet-500/10">Audit Logs</a>
                </div>
            </section>
        </div>

        {{-- Election lifecycle --}}
        <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold text-white">Election Lifecycle Controls</h3>
                    <p class="mt-1 text-sm text-slate-400">Open, pause, and close voting here. Positions and candidates are in Voting Management.</p>
                </div>
                <a href="{{ route('admin.elections.index') }}" class="shrink-0 text-xs font-semibold text-violet-300 hover:text-violet-200">View all</a>
            </div>
            <div class="mt-4 space-y-4">
                @forelse ($elections as $election)
                    @php
                        $electionLifecycleActions = $election->dashboard_actions ?? [];
                    @endphp
                    <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-semibold text-white">{{ $election->title }}</p>
                                    <a href="{{ route('admin.elections.edit', $election) }}" class="text-xs font-semibold text-violet-300 hover:text-violet-200">Manage</a>
                                </div>
                                <p class="text-xs text-slate-400">{{ $election->status?->label() }} · {{ $election->votes_count }} votes · {{ $election->candidates_count }} candidates
                                    @if ($election->results_locked) · <span class="text-amber-300">Results Locked</span> @endif
                                    @if ($election->public_results_published) · <span class="text-emerald-300">Results Published</span> @endif
                                    @if ($election->is_paused) · <span class="text-rose-300">Paused</span> @endif
                                </p>
                                @if ($election->integrity_hash)
                                    <p class="mt-1 break-all font-mono text-[10px] text-slate-500">Hash: {{ Str::limit($election->integrity_hash, 32) }}</p>
                                @endif
                            </div>

                            @if ($electionLifecycleActions !== [])
                                <div class="hidden flex-wrap justify-end gap-1 lg:flex">
                                    @foreach ($electionLifecycleActions as $action => $label)
                                        <form method="POST" action="{{ route('super-admin.elections.action', $election) }}">@csrf<input type="hidden" name="action" value="{{ $action }}"><button class="rounded-lg border border-slate-700 px-2 py-1 text-xs text-slate-300 hover:border-violet-500/40 hover:text-white">{{ $label }}</button></form>
                                    @endforeach
                                </div>

                                <details class="relative lg:hidden">
                                    <summary class="flex cursor-pointer list-none items-center justify-between gap-2 rounded-lg border border-slate-700 px-3 py-2 text-xs font-semibold text-slate-200 [&::-webkit-details-marker]:hidden">
                                        Actions
                                        <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </summary>
                                    <div class="absolute left-0 right-0 z-20 mt-1 max-h-72 overflow-y-auto rounded-xl border border-slate-700 bg-slate-900 p-1 shadow-xl">
                                        @foreach ($electionLifecycleActions as $action => $label)
                                            <form method="POST" action="{{ route('super-admin.elections.action', $election) }}">
                                                @csrf
                                                <input type="hidden" name="action" value="{{ $action }}">
                                                <button type="submit" class="w-full rounded-lg px-3 py-2 text-left text-xs text-slate-200 hover:bg-slate-800 hover:text-white">{{ $label }}</button>
                                            </form>
                                        @endforeach
                                    </div>
                                </details>
                            @endif
                        </div>
                        @if ($election->can_schedule)
                            <form method="POST" action="{{ route('super-admin.elections.action', $election) }}" class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]">
                                @csrf<input type="hidden" name="action" value="schedule">
                                <input type="datetime-local" name="scheduled_open_at" class="w-full min-w-0 max-w-full rounded-lg border border-slate-700 bg-slate-950 px-2 py-1.5 text-xs text-white">
                                <input type="datetime-local" name="scheduled_close_at" class="w-full min-w-0 max-w-full rounded-lg border border-slate-700 bg-slate-950 px-2 py-1.5 text-xs text-white">
                                <button class="w-full rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white sm:col-span-2 lg:col-span-1 lg:w-auto">Schedule</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-6 text-center text-sm text-slate-500">
                        No elections yet.
                        <a href="{{ route('admin.elections.create') }}" class="font-semibold text-violet-300 hover:text-violet-200">Create an election</a>
                    </p>
                @endforelse
            </div>
        </section>

        {{-- Vote integrity & voter eligibility --}}
        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                <h3 class="text-lg font-semibold text-white">Vote Integrity & Verification</h3>
                <p class="mt-1 text-xs text-slate-500">Scope: {{ $statistics['election_scope'] }}</p>
                <ul class="mt-4 space-y-3 text-sm text-slate-300">
                    <li class="flex flex-col gap-0.5 sm:flex-row sm:items-start sm:justify-between sm:gap-4"><span class="shrink-0">Anonymization</span><span class="text-emerald-300 sm:text-right">Enabled (no voter-candidate public link)</span></li>
                    <li class="flex flex-col gap-0.5 sm:flex-row sm:items-start sm:justify-between sm:gap-4"><span class="shrink-0">Duplicate Vote Checker</span><span class="text-emerald-300 sm:text-right">DB unique constraint per category</span></li>
                    <li class="flex flex-col gap-0.5 sm:flex-row sm:items-start sm:justify-between sm:gap-4"><span class="shrink-0">Eligible Voters</span><span class="text-white sm:text-right">{{ $statistics['eligible_students'] }}</span></li>
                    <li class="flex flex-col gap-0.5 sm:flex-row sm:items-start sm:justify-between sm:gap-4"><span class="shrink-0">Total Voted</span><span class="text-white sm:text-right">{{ $statistics['voted_students'] }}</span></li>
                    <li class="flex flex-col gap-0.5 sm:flex-row sm:items-start sm:justify-between sm:gap-4"><span class="shrink-0">Turnout</span><span class="text-white sm:text-right">{{ $statistics['voter_turnout'] }}%</span></li>
                </ul>
            </section>
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                <h3 class="text-lg font-semibold text-white">Voter Eligibility</h3>
                <ul class="mt-4 space-y-3 text-sm">
                    <li class="flex flex-col gap-0.5 text-emerald-300 sm:flex-row sm:items-start sm:justify-between sm:gap-4"><span>Enrolled</span><span class="sm:text-right">{{ $voterEligibility['enrolled'] }}</span></li>
                    <li class="flex flex-col gap-0.5 text-amber-300 sm:flex-row sm:items-start sm:justify-between sm:gap-4"><span>Probation</span><span class="sm:text-right">{{ $voterEligibility['probation'] }}</span></li>
                    <li class="flex flex-col gap-0.5 text-rose-300 sm:flex-row sm:items-start sm:justify-between sm:gap-4"><span>Withdrawn</span><span class="sm:text-right">{{ $voterEligibility['withdrawn'] }}</span></li>
                </ul>
                <div class="mt-4 flex flex-wrap gap-x-4 gap-y-2">
                    <a href="{{ route('admin.students.index') }}" class="text-sm font-semibold text-violet-300 hover:text-violet-200">Manage student records →</a>
                    @can('importStudentRecords')
                        <a href="{{ route('super-admin.roster.students.import') }}" class="text-sm font-semibold text-violet-300 hover:text-violet-200">Import student roster →</a>
                    @endcan
                </div>
            </section>
        </div>

        {{-- Portal accounts with bulk actions --}}
        <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
            <form method="GET" class="mb-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                <input
                    name="portal_q"
                    type="search"
                    value="{{ request('portal_q') }}"
                    placeholder="Search account ID, name, or email"
                    class="w-full min-w-0 flex-1 rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-sm text-slate-100 sm:min-w-[16rem]"
                />
                <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white sm:w-auto">Search Accounts</button>
                @if (request()->filled('portal_q'))
                    <a href="{{ route('super-admin.dashboard') }}" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800 sm:w-auto">Clear</a>
                @endif
            </form>

            <form method="POST" action="{{ route('super-admin.users.bulk') }}" data-portal-bulk-form>
                @csrf
                <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-white">Portal Accounts & Bulk Actions</h3>
                        <p class="mt-1 text-xs text-slate-500">{{ $portalUsers->total() }} account(s) · Deactivated users cannot sign in</p>
                    </div>
                    <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center">
                        @can('importStudentRecords')
                            <a href="{{ route('super-admin.roster.students.index') }}" class="inline-flex w-full items-center justify-center rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-300 hover:bg-violet-500/10 sm:w-auto">
                                Student Roster
                            </a>
                        @endcan
                        <select name="action" data-portal-bulk-action class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-1.5 text-xs text-white sm:w-auto" required>
                            <option value="">Bulk action…</option>
                            <option value="activate">Activate</option>
                            <option value="deactivate">Deactivate</option>
                            <option value="resend_access">Resend Access</option>
                            <option value="export">Export CSV</option>
                            <option value="delete">Delete</option>
                        </select>
                        <button type="submit" class="w-full rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white sm:w-auto">Apply</button>
                    </div>
                </div>

                <label class="mt-4 flex items-center gap-2 text-xs text-slate-400 lg:hidden">
                    <input type="checkbox" id="bulk-select-all-mobile" class="rounded border-slate-600">
                    Select all on this page
                </label>

                <div class="mt-3 space-y-3 lg:hidden" data-portal-account-cards>
                    @forelse ($portalUsers as $portalUser)
                        <article class="rounded-xl border border-slate-800 bg-slate-950/50 p-3">
                            <div class="flex items-start gap-3">
                                <input type="checkbox" name="user_ids[]" value="{{ $portalUser->id }}" data-bulk-user data-bulk-layout="mobile" class="mt-1 rounded border-slate-600">
                                <div class="min-w-0 flex-1">
                                    <p class="break-words font-medium text-white">{{ $portalUser->name }}</p>
                                    <p class="mt-0.5 font-mono text-xs text-violet-300">{{ $portalUser->account_id }}</p>
                                    <p class="mt-1 break-all text-xs text-slate-400">{{ $portalUser->email ?: '—' }}</p>
                                    <dl class="mt-3 grid grid-cols-2 gap-2 text-xs">
                                        <div>
                                            <dt class="uppercase tracking-wide text-slate-500">Role</dt>
                                            <dd class="mt-0.5 text-slate-200">{{ $portalUser->staffRole?->name ?? str($portalUser->role?->value)->replace('_',' ')->title() }}</dd>
                                        </div>
                                        <div>
                                            <dt class="uppercase tracking-wide text-slate-500">Passkeys</dt>
                                            <dd class="mt-0.5 text-slate-200">{{ $portalUser->passkeys_count }}</dd>
                                        </div>
                                        <div class="col-span-2">
                                            <dt class="uppercase tracking-wide text-slate-500">Status</dt>
                                            <dd class="mt-0.5 {{ $portalUser->is_active ? 'text-emerald-300' : 'text-rose-300' }}">{{ $portalUser->is_active ? 'Active' : 'Inactive' }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </article>
                    @empty
                        <p class="px-1 py-4 text-center text-sm text-slate-400">No portal accounts match your search.</p>
                    @endforelse
                </div>

                <div class="mt-4 hidden overflow-x-auto lg:block">
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
                            @forelse ($portalUsers as $portalUser)
                                <tr>
                                    <td class="px-3 py-3"><input type="checkbox" name="user_ids[]" value="{{ $portalUser->id }}" data-bulk-user data-bulk-layout="desktop" class="rounded border-slate-600"></td>
                                    <td class="px-3 py-3 font-mono text-xs">{{ $portalUser->account_id }}</td>
                                    <td class="px-3 py-3">{{ $portalUser->name }}</td>
                                    <td class="px-3 py-3 text-xs text-slate-400">{{ $portalUser->email ?: '—' }}</td>
                                    <td class="px-3 py-3 text-xs">{{ $portalUser->staffRole?->name ?? str($portalUser->role?->value)->replace('_',' ')->title() }}</td>
                                    <td class="px-3 py-3">{{ $portalUser->passkeys_count }}</td>
                                    <td class="px-3 py-3"><span class="{{ $portalUser->is_active ? 'text-emerald-300' : 'text-rose-300' }}">{{ $portalUser->is_active ? 'Active' : 'Inactive' }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-3 py-6 text-center text-slate-400">No portal accounts match your search.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $portalUsers->links('admin.partials.pagination') }}</div>
            </form>
        </section>

        {{-- Compliance & reporting --}}
        <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
            <h3 class="text-lg font-semibold text-white">Compliance & Official Reports</h3>
            <p class="mt-1 text-sm text-slate-400">Download compliance files here. Live charts and election exports are in Reports & Analytics.</p>
            <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4">
                @foreach (['election_summary' => 'Election Summary', 'voter_turnout' => 'Voter Turnout', 'audit_trail' => 'Audit Trail', 'passkey_inventory' => 'Passkey Inventory'] as $key => $label)
                    <a href="{{ route('super-admin.reports.generate', ['report' => $key]) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-violet-500/30 px-4 py-2 text-center text-sm font-semibold text-violet-200 hover:bg-violet-500/10">{{ $label }}</a>
                @endforeach
            </div>
            <p class="mt-4 text-sm text-slate-400">
                Public results transparency is managed in
                <a href="{{ route('super-admin.system.settings.edit') }}" class="font-semibold text-violet-300 hover:text-violet-200">System Settings</a>.
            </p>
        </section>

        <x-passkey-recovery-queue-dark :recovery-requests="$recoveryRequests" />

    </x-admin-portal>

    @vite(['resources/js/passkey-admin-recovery.js'])
</x-app-layout>

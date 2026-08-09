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

        @if (session('success'))
            <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
        @endif

        @if (session('warning'))
            <div class="rounded-xl border border-amber-500/20 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">{{ session('warning') }}</div>
        @endif

        @if (session('error'))
            <div class="rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">{{ session('error') }}</div>
        @endif

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
        <section class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4 xl:grid-cols-9">
            @foreach ([
                ['label' => 'Students', 'value' => $statistics['students'], 'color' => 'emerald'],
                ['label' => 'Staff Admins', 'value' => $statistics['admins'], 'color' => 'cyan'],
                ['label' => 'Super Admins', 'value' => $statistics['super_admins'], 'color' => 'violet'],
                ['label' => 'Passkeys', 'value' => $statistics['passkeys'], 'color' => 'sky'],
                ['label' => 'Pending Recovery', 'value' => $statistics['pending_recoveries'], 'color' => 'rose'],
                ['label' => 'Active Elections', 'value' => $statistics['active_elections'], 'color' => 'amber'],
                ['label' => 'Votes Cast', 'value' => number_format($statistics['total_votes']), 'color' => 'indigo'],
                ['label' => 'Turnout', 'value' => $statistics['voter_turnout'].'%', 'color' => 'fuchsia'],
                ['label' => 'System', 'value' => $systemHealth['overall'], 'color' => $systemHealth['overall'] === 'Healthy' ? 'emerald' : 'amber'],
            ] as $stat)
                <div class="rounded-2xl border border-violet-500/10 bg-slate-900/70 p-3 sm:p-4">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ $stat['label'] }}</p>
                    <p class="mt-1 text-lg font-bold text-white sm:text-xl">{{ $stat['value'] }}</p>
                </div>
            @endforeach
        </section>

        {{-- System status --}}
        <section class="grid gap-4 lg:grid-cols-4">
            @foreach ($systemHealth as $key => $item)
                @if ($key !== 'overall' && is_array($item))
                    <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                        <p class="text-xs font-semibold uppercase text-slate-400">{{ str($key)->replace('_', ' ')->title() }}</p>
                        <p class="mt-2 flex items-center gap-2 text-sm text-white">
                            <span class="h-2 w-2 rounded-full {{ $item['status'] === 'ok' ? 'bg-emerald-400' : ($item['status'] === 'warning' ? 'bg-amber-400' : 'bg-rose-400') }}"></span>
                            {{ $item['message'] }}
                        </p>
                    </div>
                @endif
            @endforeach
        </section>

        <div class="grid gap-6 xl:grid-cols-2">
            {{-- Security: Permission matrix --}}
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 xl:col-span-2">
                <h3 class="text-lg font-semibold text-white">Granular Role & Permission Matrix</h3>
                <p class="mt-1 text-sm text-slate-400">Chief Super Admin, Operations Admin, Student Records Admin, Auditor, Read-Only Admin</p>
                <div class="mt-4 overflow-x-auto">
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
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h3 class="text-lg font-semibold text-white">Audit Log / Activity History</h3>
                    <div class="flex flex-wrap gap-2">
                        <form id="audit-filter-form" class="flex flex-wrap gap-2">
                            <select name="action_type" class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-1 text-xs text-white">
                                <option value="">All types</option>
                                @foreach (['auth','election','passkey','user','backup','security','report','system'] as $type)
                                    <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                                @endforeach
                            </select>
                            <select name="status" class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-1 text-xs text-white">
                                <option value="">All status</option>
                                <option value="success">Success</option>
                                <option value="failed">Failed</option>
                            </select>
                        </form>
                        <a href="{{ route('super-admin.audit.export') }}" class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-violet-500">Export CSV</a>
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
                            @foreach ($passkeys as $passkey)
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
                            @endforeach
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
            <h3 class="text-lg font-semibold text-white">Election Lifecycle Controls</h3>
            <div class="mt-4 space-y-4">
                @foreach ($elections as $election)
                    <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-white">{{ $election->title }}</p>
                                <p class="text-xs text-slate-400">{{ $election->status?->value }} · {{ $election->votes_count }} votes · {{ $election->candidates_count }} candidates
                                    @if ($election->results_locked) · <span class="text-amber-300">Results Locked</span> @endif
                                    @if ($election->public_results_published) · <span class="text-emerald-300">Results Published</span> @endif
                                    @if ($election->is_paused) · <span class="text-rose-300">Paused</span> @endif
                                </p>
                                @if ($election->integrity_hash)
                                    <p class="mt-1 font-mono text-[10px] text-slate-500">Hash: {{ Str::limit($election->integrity_hash, 32) }}</p>
                                @endif
                            </div>
                            <div class="flex flex-wrap gap-1">
                                @foreach (['open' => 'Open', 'pause' => 'Pause', 'resume' => 'Resume', 'close' => 'Close', 'annul' => 'Annul', 'rerun' => 'Re-run', 'lock' => 'Lock Results', 'publish_results' => 'Publish Results', 'unpublish_results' => 'Unpublish'] as $action => $label)
                                    <form method="POST" action="{{ route('super-admin.elections.action', $election) }}">@csrf<input type="hidden" name="action" value="{{ $action }}"><button class="rounded-lg border border-slate-700 px-2 py-1 text-xs text-slate-300 hover:border-violet-500/40 hover:text-white">{{ $label }}</button></form>
                                @endforeach
                            </div>
                        </div>
                        <form method="POST" action="{{ route('super-admin.elections.action', $election) }}" class="mt-3 flex flex-wrap items-end gap-2">
                            @csrf<input type="hidden" name="action" value="schedule">
                            <input type="datetime-local" name="scheduled_open_at" class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-1 text-xs text-white">
                            <input type="datetime-local" name="scheduled_close_at" class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-1 text-xs text-white">
                            <button class="rounded-lg bg-indigo-600 px-3 py-1 text-xs font-semibold text-white">Schedule</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Vote integrity & voter eligibility --}}
        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                <h3 class="text-lg font-semibold text-white">Vote Integrity & Verification</h3>
                <ul class="mt-4 space-y-3 text-sm text-slate-300">
                    <li class="flex justify-between"><span>Anonymization</span><span class="text-emerald-300">Enabled (no voter-candidate public link)</span></li>
                    <li class="flex justify-between"><span>Duplicate Vote Checker</span><span class="text-emerald-300">DB unique constraint per category</span></li>
                    <li class="flex justify-between"><span>Eligible Voters</span><span class="text-white">{{ $statistics['eligible_students'] }}</span></li>
                    <li class="flex justify-between"><span>Total Voted</span><span class="text-white">{{ $statistics['voted_students'] }}</span></li>
                    <li class="flex justify-between"><span>Turnout</span><span class="text-white">{{ $statistics['voter_turnout'] }}%</span></li>
                </ul>
            </section>
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                <h3 class="text-lg font-semibold text-white">Voter Eligibility</h3>
                <ul class="mt-4 space-y-3 text-sm">
                    <li class="flex justify-between text-emerald-300"><span>Enrolled</span><span>{{ $voterEligibility['enrolled'] }}</span></li>
                    <li class="flex justify-between text-amber-300"><span>Probation</span><span>{{ $voterEligibility['probation'] }}</span></li>
                    <li class="flex justify-between text-rose-300"><span>Withdrawn</span><span>{{ $voterEligibility['withdrawn'] }}</span></li>
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
            <form method="GET" class="mb-4 flex flex-wrap gap-3">
                <input
                    name="portal_q"
                    type="search"
                    value="{{ request('portal_q') }}"
                    placeholder="Search account ID, name, or email"
                    class="min-w-[16rem] flex-1 rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-sm text-slate-100"
                />
                <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white">Search Accounts</button>
                @if (request()->filled('portal_q'))
                    <a href="{{ route('super-admin.dashboard') }}" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Clear</a>
                @endif
            </form>

            <form method="POST" action="{{ route('super-admin.users.bulk') }}" data-portal-bulk-form>
                @csrf
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-white">Portal Accounts & Bulk Actions</h3>
                        <p class="mt-1 text-xs text-slate-500">{{ $portalUsers->total() }} account(s) · Deactivated users cannot sign in</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        @can('importStudentRecords')
                            <a href="{{ route('super-admin.roster.students.index') }}" class="rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-300 hover:bg-violet-500/10">
                                Student Roster
                            </a>
                        @endcan
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
                            @forelse ($portalUsers as $portalUser)
                                <tr>
                                    <td class="px-3 py-3"><input type="checkbox" name="user_ids[]" value="{{ $portalUser->id }}" data-bulk-user class="rounded border-slate-600"></td>
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
                <div class="mt-4">{{ $portalUsers->links() }}</div>
            </form>
        </section>

        {{-- Compliance & reporting --}}
        <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
            <h3 class="text-lg font-semibold text-white">Compliance & Official Reports</h3>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach (['election_summary' => 'Election Summary', 'voter_turnout' => 'Voter Turnout', 'audit_trail' => 'Audit Trail', 'passkey_inventory' => 'Passkey Inventory'] as $key => $label)
                    <a href="{{ route('super-admin.reports.generate', ['report' => $key]) }}" class="rounded-xl border border-violet-500/30 px-4 py-2 text-sm font-semibold text-violet-200 hover:bg-violet-500/10">{{ $label }}</a>
                @endforeach
            </div>
            <p class="mt-4 text-sm text-slate-400">
                Public results transparency is managed in
                <a href="{{ route('super-admin.system.settings.edit') }}" class="font-semibold text-violet-300 hover:text-violet-200">System Settings</a>.
            </p>
        </section>

        <x-passkey-recovery-queue-dark :recovery-requests="$recoveryRequests" />

    </x-admin-portal>

    <script>
        window.superAdminPortal = { searchUrl: @json(route('super-admin.search')) };
    </script>
    @vite(['resources/js/passkey-admin-recovery.js', 'resources/js/super-admin-dashboard.js'])
</x-app-layout>

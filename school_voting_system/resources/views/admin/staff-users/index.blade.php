<x-app-layout>
    <x-admin-portal :title="$pageTitle" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => $pageTitle,
            'description' => $role->value === 'faculty'
                ? 'Registered faculty system accounts only. Official faculty records live under Roster Management.'
                : 'Registered administrator system accounts only. Official administrator records live under Roster Management.',
            'showAction' => false,
        ])

        <div class="mb-6 flex flex-wrap justify-end gap-3">
            <a href="{{ $role->value === 'faculty' ? route('super-admin.roster.faculty.index') : route('super-admin.roster.administrators.index') }}"
               class="rounded-xl border border-violet-500/30 px-4 py-2 text-sm font-semibold text-violet-300 hover:bg-violet-500/10">
                Manage {{ $role->value === 'faculty' ? 'Faculty' : 'Administrator' }} Roster
            </a>
            <a href="{{ $createRoute }}" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
                {{ $role->value === 'faculty' ? 'Add Faculty' : 'Add Administrator' }}
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @include('admin.partials.enrollment-link-banner')

        <div class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['Total Records', $summary['total']],
                ['Active Accounts', $summary['active']],
                ['Inactive Accounts', $summary['inactive']],
                ['Registered Devices', $summary['devices']],
            ] as [$label, $value])
                <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</p>
                    <p class="mt-1 text-2xl font-bold text-white">{{ number_format($value) }}</p>
                </div>
            @endforeach
        </div>

        <form method="GET" action="{{ route($indexRouteName) }}" class="mb-6 flex flex-wrap gap-3">
            <input
                name="q"
                type="search"
                value="{{ request('q') }}"
                placeholder="{{ $role->value === 'faculty' ? 'Search by Faculty ID, Name, or Email...' : 'Search by Administrator ID, Name, or Email...' }}"
                class="min-w-[16rem] flex-1 rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100"
            />
            <select name="status" class="rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-sm text-slate-100">
                <option value="">All statuses</option>
                <option value="active" @selected($statusFilter === 'active')>Active</option>
                <option value="inactive" @selected($statusFilter === 'inactive')>Suspended</option>
                <option value="archived" @selected($statusFilter === 'archived')>Deactivated</option>
            </select>
            <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white">Search</button>
            @if (request()->filled('q') || request()->filled('status'))
                <a href="{{ route($indexRouteName) }}" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Clear</a>
            @endif
        </form>

        <div class="overflow-x-auto rounded-2xl border border-violet-500/15 bg-slate-900/70">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3">Account ID</th>
                        <th class="px-4 py-3">{{ $role->value === 'faculty' ? 'Faculty Name' : 'Administrator Name' }}</th>
                        <th class="hidden px-4 py-3 lg:table-cell">Email Address</th>
                        @if ($role->value === 'admin')
                            <th class="hidden px-4 py-3 md:table-cell">Role</th>
                        @else
                            <th class="hidden px-4 py-3 md:table-cell">Department</th>
                            <th class="hidden px-4 py-3 md:table-cell">Assigned Competitions</th>
                        @endif
                        <th class="px-4 py-3">Account Status</th>
                        <th class="hidden px-4 py-3 sm:table-cell">Registered Devices</th>
                        <th class="hidden px-4 py-3 xl:table-cell">Last Login</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($accounts as $account)
                        <tr class="border-b border-slate-800/80 text-slate-200 transition hover:bg-slate-950/40">
                            <td class="px-4 py-3 font-mono text-xs text-slate-300">{{ $account->account_id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-white">{{ $account->name }}</div>
                                <div class="mt-0.5 text-xs text-slate-500 lg:hidden">{{ $account->email }}</div>
                            </td>
                            <td class="hidden px-4 py-3 text-slate-400 lg:table-cell">{{ $account->email }}</td>
                            @if ($role->value === 'admin')
                                <td class="hidden px-4 py-3 text-slate-300 md:table-cell">{{ $account->staffRole?->name ?? $account->roleLabel() }}</td>
                            @else
                                <td class="hidden px-4 py-3 text-slate-300 md:table-cell">{{ $account->departmentLabel() }}</td>
                                <td class="hidden px-4 py-3 text-slate-300 md:table-cell">{{ number_format($account->judging_assignments_count ?? 0) }}</td>
                            @endif
                            <td class="px-4 py-3">
                                @php($accountStatus = $account->accountStatusLabel())
                                <span @class([
                                    'rounded-full border px-2 py-0.5 text-xs font-semibold',
                                    'border-emerald-500/30 bg-emerald-500/10 text-emerald-200' => $accountStatus === 'Active',
                                    'border-amber-500/30 bg-amber-500/10 text-amber-200' => $accountStatus === 'Suspended',
                                    'border-slate-600 bg-slate-800/80 text-slate-300' => $accountStatus === 'Deactivated',
                                ])>{{ $accountStatus }}</span>
                            </td>
                            <td class="hidden px-4 py-3 sm:table-cell">{{ $account->passkeys_count }}</td>
                            <td class="hidden px-4 py-3 text-slate-400 xl:table-cell">
                                @if ($account->last_login_at)
                                    {{ \Illuminate\Support\Carbon::parse($account->last_login_at)->format('M d, Y g:i A') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <x-admin.user-action-menu
                                    :account="$account"
                                    :variant="$role->value === 'faculty' ? 'faculty' : 'admin'"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $role->value === 'faculty' ? 9 : 8 }}" class="px-4 py-8 text-center text-slate-400">No registered {{ strtolower($pageTitle) }} accounts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $accounts->links() }}</div>
    </x-admin-portal>
</x-app-layout>

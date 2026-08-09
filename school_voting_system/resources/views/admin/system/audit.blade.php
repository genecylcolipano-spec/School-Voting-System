<x-app-layout>
    <x-admin-portal title="Audit Logs" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'Audit Logs',
            'description' => 'Critical system activity across users, security, backups, and administration.',
            'showAction' => false,
        ])

        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-slate-400">Newest events first. Export supports up to 5,000 rows.</p>
            <a href="{{ route('super-admin.audit.export', request()->only(['from', 'to', 'module'])) }}"
                class="rounded-xl border border-violet-500/30 px-4 py-2 text-sm font-semibold text-violet-300 hover:bg-violet-500/10">
                Export CSV
            </a>
        </div>

        <form method="GET" class="mb-6 grid gap-3 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:grid-cols-2 lg:grid-cols-6">
            <input name="search" type="search" value="{{ $filters['search'] }}" placeholder="Search action, user, IP"
                class="rounded-xl border border-slate-700 bg-slate-950/50 px-3 py-2 text-sm text-white lg:col-span-2">
            <input name="from" type="date" value="{{ $filters['from'] }}" class="rounded-xl border border-slate-700 bg-slate-950/50 px-3 py-2 text-sm text-white">
            <input name="to" type="date" value="{{ $filters['to'] }}" class="rounded-xl border border-slate-700 bg-slate-950/50 px-3 py-2 text-sm text-white">
            <select name="module" class="rounded-xl border border-slate-700 bg-slate-950/50 px-3 py-2 text-sm text-white">
                <option value="">All modules</option>
                @foreach ($modules as $module)
                    <option value="{{ $module->value }}" @selected($filters['module'] === $module->value)>{{ str($module->value)->title() }}</option>
                @endforeach
            </select>
            <select name="role" class="rounded-xl border border-slate-700 bg-slate-950/50 px-3 py-2 text-sm text-white">
                <option value="">All roles</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->value }}" @selected($filters['role'] === $role->value)>{{ str($role->value)->replace('_', ' ')->title() }}</option>
                @endforeach
            </select>
            <select name="user_id" class="rounded-xl border border-slate-700 bg-slate-950/50 px-3 py-2 text-sm text-white lg:col-span-2">
                <option value="">All users</option>
                @foreach ($actors as $actor)
                    <option value="{{ $actor->id }}" @selected($filters['user_id'] === (string) $actor->id)>{{ $actor->name }} ({{ $actor->account_id }})</option>
                @endforeach
            </select>
            <div class="flex gap-2 lg:col-span-4">
                <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white">Filter</button>
                <a href="{{ route('super-admin.system.audit.index') }}" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Reset</a>
            </div>
        </form>

        <div class="overflow-x-auto rounded-2xl border border-violet-500/15 bg-slate-900/70">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800 text-left text-slate-400">
                        <th class="px-4 py-3 font-medium">Timestamp</th>
                        <th class="px-4 py-3 font-medium">User</th>
                        <th class="px-4 py-3 font-medium">Role</th>
                        <th class="px-4 py-3 font-medium">Action</th>
                        <th class="px-4 py-3 font-medium">Module</th>
                        <th class="px-4 py-3 font-medium">IP Address</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr class="border-b border-slate-800/80 text-slate-200">
                            <td class="px-4 py-3 whitespace-nowrap">{{ optional($log->created_at)->format('M d, Y g:i A') }}</td>
                            <td class="px-4 py-3">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="px-4 py-3">{{ $log->user?->roleLabel() ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $log->action }}</td>
                            <td class="px-4 py-3">{{ str($log->action_type?->value ?? $log->action_type)->title() }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $log->ip_address ?: '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-xs font-semibold text-emerald-200">
                                    {{ ucfirst((string) ($log->status ?? 'success')) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-500">No audit events match your filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $logs->links() }}</div>
    </x-admin-portal>
</x-app-layout>

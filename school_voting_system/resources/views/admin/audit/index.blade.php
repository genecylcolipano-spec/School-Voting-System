<x-app-layout>
    <x-admin-portal title="Audit Log" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'Audit Log',
            'description' => 'Search and filter important system actions.',
        ])

        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="mb-6 grid gap-3 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 md:grid-cols-2 xl:grid-cols-6">
            <div class="xl:col-span-2">
                <label class="text-[10px] uppercase text-slate-500">Search</label>
                <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Action or user…" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white">
            </div>
            <div>
                <label class="text-[10px] uppercase text-slate-500">From</label>
                <input type="date" name="from" value="{{ $filters['from'] }}" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white">
            </div>
            <div>
                <label class="text-[10px] uppercase text-slate-500">To</label>
                <input type="date" name="to" value="{{ $filters['to'] }}" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white">
            </div>
            <div>
                <label class="text-[10px] uppercase text-slate-500">Module</label>
                <select name="module" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white">
                    <option value="">All modules</option>
                    @foreach ($modules as $module)
                        <option value="{{ $module->value }}" @selected($filters['module'] === $module->value)>{{ ucfirst($module->value) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-[10px] uppercase text-slate-500">Role</label>
                <input type="text" name="role" value="{{ $filters['role'] }}" placeholder="Admin role…" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white">
            </div>
            <div class="flex items-end gap-2 md:col-span-2 xl:col-span-6">
                <button type="submit" class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-500">Apply filters</button>
                @if ($filters['search'] || $filters['from'] || $filters['to'] || $filters['module'] || $filters['role'])
                    <a href="{{ route('admin.audit-logs.index') }}" class="rounded-lg border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:text-white">Clear</a>
                @endif
            </div>
        </form>

        <div class="overflow-hidden rounded-2xl border border-violet-500/15 bg-slate-900/70">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-slate-800 bg-slate-950/50 text-xs uppercase tracking-wide text-slate-400">
                        <tr>
                            <th class="px-4 py-3">User</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3">Action</th>
                            <th class="px-4 py-3">Module</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse ($logs as $log)
                            <tr class="text-slate-200 transition hover:bg-slate-950/40">
                                <td class="px-4 py-3 font-medium text-white">{{ $log->admin_name ?? $log->user?->name ?? 'System' }}</td>
                                <td class="px-4 py-3 text-slate-400">{{ $log->admin_role ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $log->action }}</td>
                                <td class="px-4 py-3 capitalize text-violet-300">{{ $log->action_type?->value ?? 'system' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $log->created_at?->format('M d, Y') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $log->created_at?->format('g:i A') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">No audit entries match your filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($logs->hasPages())
                <div class="border-t border-slate-800 px-4 py-3">{{ $logs->links() }}</div>
            @endif
        </div>
    </x-admin-portal>
</x-app-layout>

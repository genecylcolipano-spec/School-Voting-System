<section id="activity" class="scroll-mt-28 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
    <x-admin-section-header
        title="Your Activity Log"
        description="Only your actions — cannot delete entries."
    />

    <form method="GET" action="{{ route('admin.dashboard') }}" class="mt-4 flex flex-wrap items-end gap-2">
        <div>
            <label class="text-[10px] uppercase text-slate-500">From</label>
            <input type="date" name="from" value="{{ $activityFilter['from'] }}" class="mt-1 block rounded-lg border border-slate-700 bg-slate-950 px-2 py-1 text-xs text-white">
        </div>
        <div>
            <label class="text-[10px] uppercase text-slate-500">To</label>
            <input type="date" name="to" value="{{ $activityFilter['to'] }}" class="mt-1 block rounded-lg border border-slate-700 bg-slate-950 px-2 py-1 text-xs text-white">
        </div>
        <div>
            <label class="text-[10px] uppercase text-slate-500">Action type</label>
            <select name="action_type" class="mt-1 block rounded-lg border border-slate-700 bg-slate-950 px-2 py-1 text-xs text-white">
                <option value="">All types</option>
                @foreach ($actionTypes as $type)
                    <option value="{{ $type->value ?? $type }}" @selected($activityFilter['action_type'] === ($type->value ?? $type))>{{ ucfirst($type->value ?? $type) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-violet-500">Filter</button>
        @if ($activityFilter['from'] || $activityFilter['to'] || $activityFilter['action_type'])
            <a href="{{ route('admin.dashboard') }}" class="rounded-lg border border-slate-700 px-3 py-1.5 text-xs font-semibold text-slate-400 hover:text-white">Clear</a>
        @endif
    </form>

    <div class="mt-4 hidden overflow-x-auto md:block">
        <table class="min-w-full text-left text-xs sm:text-sm">
            <thead class="border-b border-slate-800 text-slate-400">
                <tr>
                    <th class="px-3 py-2">Timestamp</th>
                    <th class="px-3 py-2">Action</th>
                    <th class="px-3 py-2">IP</th>
                    <th class="px-3 py-2">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse ($activityLogs as $log)
                    <tr class="text-slate-200">
                        <td class="px-3 py-2 whitespace-nowrap">{{ $log->created_at?->format('M d, H:i') }}</td>
                        <td class="px-3 py-2">{{ $log->action }}</td>
                        <td class="px-3 py-2 font-mono text-xs">{{ $log->ip_address }}</td>
                        <td class="px-3 py-2">
                            <x-admin-status-badge :status="$log->status" :label="$log->status" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-3 py-6 text-center text-slate-400">No activity logged yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 space-y-2 md:hidden">
        @forelse ($activityLogs as $log)
            <article class="rounded-lg border border-slate-800 bg-slate-950/50 p-3 text-sm">
                <div class="flex items-start justify-between gap-2">
                    <p class="font-medium text-white">{{ $log->action }}</p>
                    <x-admin-status-badge :status="$log->status" :label="$log->status" />
                </div>
                <p class="mt-1 text-xs text-slate-400">{{ $log->created_at?->format('M d, H:i') }} · {{ $log->ip_address }}</p>
            </article>
        @empty
            <p class="text-center text-sm text-slate-400">No activity logged yet.</p>
        @endforelse
    </div>

    @if ($activityLogs->hasPages())
        <div class="mt-4">
            {{ $activityLogs->links() }}
        </div>
    @endif
</section>

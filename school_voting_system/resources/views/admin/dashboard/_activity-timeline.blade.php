<section id="activity-timeline" class="scroll-mt-28 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
    <x-admin-section-header
        title="Recent Activity"
        description="Latest election and administration events — newest first."
    />

    <div class="mt-5 space-y-0 divide-y divide-slate-800 rounded-xl border border-slate-800 bg-slate-950/40">
        @forelse ($recentActivityTimeline ?? [] as $entry)
            <article class="flex flex-wrap items-start gap-4 px-4 py-3.5 transition hover:bg-slate-900/50">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-500/10 text-lg" aria-hidden="true">{{ $entry['icon'] }}</span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-white">{{ $entry['activity'] }}</p>
                    <p class="mt-0.5 text-xs text-slate-400">{{ $entry['user'] }} · {{ $entry['module'] ?? 'System' }}</p>
                </div>
                <div class="text-right text-xs text-slate-500">
                    <p class="font-medium text-slate-300">{{ $entry['date'] }}</p>
                    <p>{{ $entry['time'] }}</p>
                </div>
            </article>
        @empty
            <p class="px-4 py-8 text-center text-sm text-slate-400">No recent activity recorded yet.</p>
        @endforelse
    </div>

    <div class="mt-4 text-right">
        <a href="{{ route('admin.audit-logs.index') }}" class="text-sm font-semibold text-violet-300 hover:text-violet-200">View full audit log →</a>
    </div>
</section>

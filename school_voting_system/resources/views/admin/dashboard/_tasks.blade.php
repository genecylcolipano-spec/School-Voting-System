<section id="tasks" class="scroll-mt-28 rounded-2xl border border-amber-500/20 bg-amber-950/20 p-5">
    <x-admin-section-header
        title="Pending Tasks"
        description="Verification requests and open complaints assigned to you."
        :badge="$pendingTasksCount > 0 ? $pendingTasksCount.' open' : null"
        badge-tone="amber"
    />

    @if ($pendingVerifications->isEmpty() && $openComplaints->isEmpty())
        <p class="mt-4 rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-6 text-center text-sm text-slate-400">No pending tasks — you're all caught up.</p>
    @else
        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            @if ($pendingVerifications->isNotEmpty())
                <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                    <h4 class="text-sm font-semibold text-white">Verification Requests</h4>
                    <ul class="mt-3 space-y-3">
                        @foreach ($pendingVerifications as $verification)
                            <li class="rounded-lg border border-slate-800 bg-slate-900/80 p-3 text-sm">
                                <p class="font-medium text-slate-200">{{ $verification->title }}</p>
                                @if ($verification->notes)
                                    <p class="mt-1 text-xs text-slate-400">{{ $verification->notes }}</p>
                                @endif
                                @if ($canVerifyCandidates && $verification->subject instanceof \App\Models\Candidate)
                                    <form method="POST" action="{{ route('admin.candidates.verify', $verification->subject) }}" data-confirm-sensitive data-confirm-title="Verify candidate?" class="mt-2 inline">
                                        @csrf
                                        <button type="submit" class="rounded bg-emerald-600 px-3 py-1 text-xs font-semibold text-white hover:bg-emerald-500">Verify Candidate</button>
                                    </form>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($openComplaints->isNotEmpty())
                <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                    <h4 class="text-sm font-semibold text-white">Open Complaints</h4>
                    <ul class="mt-3 space-y-3">
                        @foreach ($openComplaints as $complaint)
                            <li class="rounded-lg border border-slate-800 bg-slate-900/80 p-3 text-sm">
                                <p class="font-medium text-slate-200">{{ $complaint->title }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ $complaint->description }}</p>
                                <p class="mt-1 text-[10px] text-slate-500">
                                    {{ $complaint->election?->title ?? 'General' }} · {{ ucfirst($complaint->priority) }} priority
                                </p>
                                @if ($canResolveComplaints)
                                    <form method="POST" action="{{ route('admin.complaints.resolve', $complaint) }}" data-confirm-sensitive data-confirm-title="Mark complaint resolved?" class="mt-2 inline">
                                        @csrf
                                        <button type="submit" class="rounded bg-violet-600 px-3 py-1 text-xs font-semibold text-white hover:bg-violet-500">Mark Resolved</button>
                                    </form>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif
</section>

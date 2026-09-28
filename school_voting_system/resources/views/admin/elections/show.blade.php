<x-app-layout>
    <x-admin-portal :title="$election->title" :user="$user" :notifications-count="$notificationsCount">
        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">{{ session('error') }}</div>
        @endif

        <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-widest text-violet-300">Student Election</p>
                <h1 class="mt-1 text-2xl font-bold text-white sm:text-3xl">{{ $election->title }}</h1>
                <p class="mt-1 text-sm text-slate-400">Created by {{ $election->creator?->name ?? '—' }}</p>
            </div>
            <x-admin-status-badge :status="$election->status?->value ?? 'draft'" :label="$election->status?->label()" />
        </div>

        <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
            @foreach ([
                ['Positions', $election->categories_count],
                ['Candidates', $election->candidates_count],
                ['Campaigns', $election->partylists_count],
                ['Votes', $election->votes_count],
            ] as [$label, $value])
                <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-500">{{ $label }}</p>
                    <p class="mt-1 text-xl font-bold text-white">{{ number_format($value) }}</p>
                </div>
            @endforeach
        </div>

        @php
            $dependencyParts = collect([
                $election->categories_count > 0 ? 'positions' : null,
                $election->candidates_count > 0 ? 'candidates' : null,
                $election->partylists_count > 0 ? 'partylists' : null,
                $election->votes_count > 0 ? 'votes' : null,
            ])->filter()->values();
            $warningParts = collect();
            if ($election->results_locked) {
                $warningParts->push('Official results for this election are locked.');
            }
            if ($dependencyParts->isNotEmpty()) {
                $warningParts->push('This election contains related data: '.$dependencyParts->join(', ').'.');
            }
            $deleteWarning = $warningParts->isNotEmpty() ? $warningParts->implode(' ') : null;
        @endphp

        <div class="mb-6 flex flex-wrap gap-2">
            @can('update', $election)
                <a href="{{ route('admin.elections.edit', $election) }}" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white hover:opacity-90">Edit Election</a>
            @endcan
            @if ($canCreateElections)
                <form method="POST" action="{{ route('admin.elections.duplicate', $election) }}">
                    @csrf
                    <button type="submit" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Duplicate</button>
                </form>
            @endif
            <a href="{{ route('admin.live.election') }}" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Live Monitoring</a>
            <a href="{{ route('admin.results.election.show', $election) }}" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Results →</a>
            @can('update', $election)
                @unless ($election->status === \App\Enums\ElectionStatus::Archived)
                    <form method="POST" action="{{ route('admin.elections.archive', $election) }}" onsubmit="return confirm('Archive this election? Students will no longer see it as an open vote.');">
                        @csrf
                        <button type="submit" class="rounded-xl border border-amber-500/40 px-4 py-2 text-sm font-semibold text-amber-200 hover:bg-amber-500/10">Archive</button>
                    </form>
                @endunless
            @endcan
            @can('delete', $election)
                <x-admin.delete-action
                    :action="route('admin.elections.destroy', $election)"
                    :warning="$deleteWarning"
                    button-class="rounded-xl border border-rose-500/40 px-4 py-2 text-sm font-semibold text-rose-200 hover:bg-rose-500/10"
                />
            @endcan
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                <h2 class="text-base font-semibold text-white">Election Information</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Status</dt><dd class="text-slate-200">{{ $election->status?->label() }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Voting starts</dt><dd class="text-slate-200">{{ optional($election->voting_starts_at)->format('M d, Y g:i A') ?: '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Voting ends</dt><dd class="text-slate-200">{{ optional($election->voting_ends_at)->format('M d, Y g:i A') ?: '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Results published</dt><dd class="text-slate-200">{{ $election->public_results_published ? 'Yes' : 'No' }}</dd></div>
                </dl>
                @if ($election->description)
                    <p class="mt-4 text-sm text-slate-400">{{ $election->description }}</p>
                @endif
            </section>

            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                <h2 class="text-base font-semibold text-white">Campaigns</h2>
                <ul class="mt-4 space-y-2 text-sm text-slate-300">
                    @forelse ($election->partylists as $campaign)
                        <li>{{ $campaign->name }}</li>
                    @empty
                        <li class="text-slate-500">No campaigns attached.</li>
                    @endforelse
                </ul>
            </section>
        </div>

        <section class="mt-6 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
            <h2 class="text-base font-semibold text-white">Positions and candidates</h2>
            <div class="mt-4 space-y-4">
                @forelse ($election->categories as $category)
                    <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                        <p class="font-semibold text-white">{{ $category->name }}</p>
                        <ul class="mt-2 space-y-1 text-sm text-slate-300">
                            @forelse ($category->candidates as $candidate)
                                <li>{{ $candidate->display_name }}@if ($candidate->party_or_group) <span class="text-slate-500">· {{ $candidate->party_or_group }}</span>@endif</li>
                            @empty
                                <li class="text-slate-500">No candidates yet.</li>
                            @endforelse
                        </ul>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No positions yet.</p>
                @endforelse
            </div>
        </section>

        <div class="mt-6">
            <a href="{{ route('admin.elections.index') }}" class="text-sm font-semibold text-violet-300 hover:text-violet-200">← Back to Elections</a>
        </div>
    </x-admin-portal>
</x-app-layout>

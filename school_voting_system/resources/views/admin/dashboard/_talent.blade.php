<section id="talent" class="scroll-mt-28 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:p-5">
    <x-admin-section-header
        title="Talent Voting"
        :description="$canViewRealtimeTalentCounts ? 'Talent Competition, Debate, Quiz — live vote totals available to authorized administrators.' : 'Talent events in your scope. Live vote totals are restricted for your role.'"
    >
        <x-slot:actions>
            <a href="{{ route('admin.talent-competition.index') }}" class="rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-200 hover:bg-violet-500/10">View All</a>
            @if ($canCreateTalentEvents)
                <a href="{{ route('admin.talent-competition.create') }}" class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-violet-500">Create Event</a>
            @endif
        </x-slot:actions>
    </x-admin-section-header>

    <div class="mt-4 space-y-3">
        @forelse ($talentEvents as $index => $talentEvent)
            @php
                $showTalentVotes = $canViewRealtimeTalentCounts || $talentEvent->votingHasClosed() || $talentEvent->currentStatusKey() === 'results_published';
                $entriesCount = (int) ($talentEvent->entries_count ?? $talentEvent->entries->count());
                $votesCount = (int) ($talentEvent->votes_count ?? 0);
            @endphp
            <details class="overflow-hidden rounded-xl border border-slate-800 bg-slate-950/50" data-talent-accordion {{ $index === 0 ? 'open' : '' }}>
                <summary class="cursor-pointer list-none px-3 py-3 sm:px-4 sm:py-3.5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="flex min-w-0 flex-1 items-start gap-3">
                            <img src="{{ $talentEvent->thumbnailUrl() }}" alt="" class="hidden h-10 w-14 shrink-0 rounded-lg object-cover object-center sm:block">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="text-sm font-semibold text-white sm:text-base">{{ $talentEvent->title }}</h4>
                                    <span class="rounded-full bg-indigo-500/20 px-2 py-0.5 text-[10px] font-semibold uppercase text-indigo-300">
                                        {{ $talentEvent->type?->label() }}
                                    </span>
                                </div>
                                <p class="mt-0.5 text-xs text-slate-400 sm:text-sm">
                                    {{ $talentEvent->event_date?->format('M d, Y · g:i A') }}
                                    @if ($talentEvent->venue) · {{ $talentEvent->venue }} @endif
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <x-admin-status-badge :status="$talentEvent->currentStatusKey()" :label="$talentEvent->displayStatusLabel()" />
                            <p class="mt-1 text-xs text-slate-500">
                                {{ $entriesCount }} {{ \Illuminate\Support\Str::plural('entry', $entriesCount) }}
                                @if ($showTalentVotes)
                                    · {{ $votesCount }} {{ \Illuminate\Support\Str::plural('vote', $votesCount) }}
                                @else
                                    · votes hidden
                                @endif
                            </p>
                        </div>
                    </div>
                </summary>

                <div class="border-t border-slate-800 px-3 pb-3 pt-2.5 sm:px-4 sm:pb-3.5">
                    <x-competition-card-banner :event="$talentEvent" compact class="mb-2.5" />

                    <div class="mb-2.5 flex flex-wrap gap-2">
                        <a href="{{ route('admin.talent-competition.show', $talentEvent) }}" class="rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-200 hover:bg-violet-500/10">Manage</a>

                        @if ($canManageTalentVoting && ! in_array($talentEvent->currentStatusKey(), ['voting_open', 'results_published', 'voting_closed', 'archived'], true))
                            <form method="POST" action="{{ route('admin.talent.open-voting', $talentEvent) }}" data-confirm-sensitive data-confirm-title="Open student voting?" class="inline">
                                @csrf
                                <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">Open Student Voting</button>
                            </form>
                        @endif

                        @if ($canPublishTalentResults && in_array($talentEvent->currentStatusKey(), ['voting_open', 'voting_closed', 'voting_paused'], true))
                            <form method="POST" action="{{ route('admin.talent.publish-results', $talentEvent) }}" data-confirm-sensitive data-confirm-title="Publish results?" class="inline">
                                @csrf
                                <button type="submit" class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-violet-500">Publish Results</button>
                            </form>
                        @endif
                    </div>

                    {{-- Desktop table — primary content --}}
                    <div class="hidden overflow-x-auto md:block">
                        <table class="min-w-full text-left text-xs sm:text-sm">
                            <thead class="border-b border-slate-800 text-slate-400">
                                <tr>
                                    <th class="px-2 py-1.5 sm:px-3">Candidate</th>
                                    <th class="px-2 py-1.5 sm:px-3">Profile</th>
                                    <th class="px-2 py-1.5 sm:px-3">Performance</th>
                                    <th class="px-2 py-1.5 sm:px-3">Status</th>
                                    <th class="px-2 py-1.5 sm:px-3">Votes</th>
                                    <th class="px-2 py-1.5 sm:px-3">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800">
                                @forelse ($talentEvent->entries as $entry)
                                    @php $entryVotes = (int) ($entry->votes_count ?? 0); @endphp
                                    <tr class="text-slate-200">
                                        <td class="px-2 py-2 sm:px-3">
                                            <span class="font-medium">{{ $entry->display_name }}</span>
                                            @if ($entry->grade_level)
                                                <p class="text-[10px] text-slate-500">Grade {{ $entry->grade_level }}-{{ $entry->section }}</p>
                                            @endif
                                        </td>
                                        <td class="max-w-[10rem] px-2 py-2 text-slate-400 sm:px-3">{{ Str::limit($entry->profile_summary, 60) }}</td>
                                        <td class="max-w-xs px-2 py-2 text-slate-400 sm:px-3">{{ Str::limit($entry->performance_description, 80) }}</td>
                                        <td class="px-2 py-2 sm:px-3"><x-admin-status-badge :status="$entry->status" /></td>
                                        <td class="px-2 py-2 font-semibold text-violet-200 sm:px-3">{{ $showTalentVotes ? $entryVotes : '—' }}</td>
                                        <td class="px-2 py-2 sm:px-3">
                                            @if ($entry->isPending() && $canApproveTalentEntries)
                                                <div class="flex flex-wrap gap-1">
                                                    <form method="POST" action="{{ route('admin.talent.entries.approve', $entry) }}" data-confirm-sensitive class="inline">
                                                        @csrf
                                                        <button type="submit" class="rounded bg-emerald-600 px-2 py-1 text-[10px] font-semibold text-white">Approve</button>
                                                    </form>
                                                    <button type="button" data-entry-reject-toggle="{{ $entry->id }}" class="rounded bg-rose-600 px-2 py-1 text-[10px] font-semibold text-white">Reject</button>
                                                    <a href="{{ route('admin.talent-participants.show', $entry) }}" class="rounded border border-slate-700 px-2 py-1 text-[10px] font-semibold text-slate-300 hover:bg-slate-800">View</a>
                                                </div>
                                                <form id="entry-reject-form-{{ $entry->id }}" method="POST" action="{{ route('admin.talent.entries.reject', $entry) }}" data-confirm-sensitive class="mt-1.5 hidden space-y-1.5">
                                                    @csrf
                                                    <textarea name="reason" required rows="2" placeholder="Rejection reason…" class="w-full rounded border border-slate-700 bg-slate-950 px-2 py-1.5 text-xs text-white"></textarea>
                                                    <button type="submit" class="rounded bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white">Submit</button>
                                                </form>
                                            @elseif ($entry->status === 'rejected' && $entry->review_reason)
                                                <div class="space-y-1">
                                                    <p class="text-[10px] text-rose-300">{{ Str::limit($entry->review_reason, 40) }}</p>
                                                    <a href="{{ route('admin.talent-participants.show', $entry) }}" class="text-[10px] font-semibold text-violet-300 hover:text-violet-200">View</a>
                                                </div>
                                            @else
                                                <a href="{{ route('admin.talent-participants.show', $entry) }}" class="text-xs font-semibold text-violet-300 hover:text-violet-200">View</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-2 py-4 text-center text-slate-400 sm:px-3">No entries yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile cards --}}
                    <div class="space-y-2 md:hidden">
                        @forelse ($talentEvent->entries as $entry)
                            @php $entryVotes = (int) ($entry->votes_count ?? 0); @endphp
                            <article class="rounded-lg border border-slate-800 bg-slate-900/80 p-2.5 text-sm">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="font-medium text-white">{{ $entry->display_name }}</p>
                                        <p class="text-xs text-slate-400">{{ Str::limit($entry->performance_description, 80) }}</p>
                                    </div>
                                    <x-admin-status-badge :status="$entry->status" />
                                </div>
                                <p class="mt-1.5 text-xs text-violet-200">Votes: {{ $showTalentVotes ? $entryVotes : '—' }}</p>
                                <div class="mt-1.5 flex flex-wrap gap-2">
                                    @if ($entry->isPending() && $canApproveTalentEntries)
                                        <form method="POST" action="{{ route('admin.talent.entries.approve', $entry) }}" data-confirm-sensitive class="flex-1">
                                            @csrf
                                            <button type="submit" class="w-full rounded bg-emerald-600 px-2 py-1.5 text-xs font-semibold text-white">Approve</button>
                                        </form>
                                        <button type="button" data-entry-reject-toggle="{{ $entry->id }}" class="rounded bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white">Reject</button>
                                    @endif
                                    <a href="{{ route('admin.talent-participants.show', $entry) }}" class="rounded border border-slate-700 px-3 py-1.5 text-xs font-semibold text-violet-300 hover:bg-slate-800">View</a>
                                </div>
                                @if ($entry->isPending() && $canApproveTalentEntries)
                                    <form id="entry-reject-form-{{ $entry->id }}" method="POST" action="{{ route('admin.talent.entries.reject', $entry) }}" data-confirm-sensitive class="mt-1.5 hidden space-y-1.5">
                                        @csrf
                                        <textarea name="reason" required rows="2" class="w-full rounded border border-slate-700 bg-slate-950 px-2 py-1.5 text-xs text-white"></textarea>
                                        <button type="submit" class="rounded bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white">Submit</button>
                                    </form>
                                @endif
                            </article>
                        @empty
                            <p class="text-center text-sm text-slate-400">No entries yet.</p>
                        @endforelse
                    </div>

                    @if ($talentEvent->currentStatusKey() === 'results_published' && $showTalentVotes)
                        @php $winner = $talentEvent->entries->sortByDesc('votes_count')->first(); @endphp
                        @if ($winner && (int) ($winner->votes_count ?? 0) > 0)
                            <div class="mt-2.5 rounded-lg border border-emerald-500/20 bg-emerald-500/5 px-3 py-2 text-sm text-emerald-200">
                                Winner: <strong>{{ $winner->display_name }}</strong> with {{ (int) $winner->votes_count }} {{ \Illuminate\Support\Str::plural('vote', (int) $winner->votes_count) }}
                            </div>
                        @endif
                    @endif
                </div>
            </details>
        @empty
            <p class="text-sm text-slate-400">No talent events in your assigned scope.</p>
        @endforelse
    </div>
</section>

<x-app-layout>
    <x-admin-portal
        :title="$talentEvent->title"
        :user="$user"
        :notifications-count="$notificationsCount"
        :assigned-role="$assignedRole"
    >
        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
        @endif

        {{-- Competition Banner (16:9) --}}
        <div class="mb-6">
            <div class="relative aspect-video overflow-hidden rounded-2xl border border-violet-500/15 bg-slate-950/90">
                <x-competition-detail-banner :event="$talentEvent" bare :show-warning="false" class="absolute inset-0" />
                <div class="absolute inset-0 z-[1] bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 z-[2] p-5 sm:p-6">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-widest text-violet-300">{{ $talentEvent->talent_category?->label() ?? 'Talent Competition' }}</p>
                            <h1 class="mt-1 text-2xl font-bold text-white sm:text-3xl">{{ $talentEvent->title }}</h1>
                            @if ($talentEvent->competition_code)
                                <p class="mt-1 text-sm text-slate-300">Code: {{ $talentEvent->competition_code }}</p>
                            @endif
                        </div>
                        <span class="rounded-full bg-violet-500/20 px-3 py-1 text-xs font-bold text-violet-100">{{ $talentEvent->displayStatusLabel() }}</span>
                    </div>
                </div>
            </div>
            @if ($talentEvent->shouldWarnNonLandscapeBanner())
                <p class="mt-2 rounded-xl border border-amber-500/25 bg-amber-500/10 px-3 py-2 text-center text-xs font-medium text-amber-100 sm:text-sm">
                    This image is portrait. For best appearance, upload a landscape banner (1600 × 900).
                </p>
            @endif
        </div>

        <x-competition-poster :event="$talentEvent" download class="mb-6" />

        {{-- Quick stats --}}
        <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4 xl:grid-cols-7">
            @foreach ([
                ['Total Participants', $talentEvent->entries_count],
                ['Pending', $talentEvent->pending_entries_count],
                ['Approved', $talentEvent->approved_entries_count],
                ['Rejected', $talentEvent->rejected_entries_count],
                ['Votes Cast', $talentEvent->votes_count],
                ['Winners', $talentEvent->number_of_winners ?? 3],
                ['Status', $talentEvent->displayStatusLabel()],
            ] as [$label, $value])
                <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-500">{{ $label }}</p>
                    <p class="mt-1 truncate text-xl font-bold text-white">{{ is_numeric($value) ? number_format($value) : $value }}</p>
                </div>
            @endforeach
        </div>

        {{-- Quick actions --}}
        @if ($canManageTalentEvents)
            <div class="mb-6 flex flex-wrap gap-2">
                <a href="{{ route('admin.talent-competition.edit', $talentEvent) }}" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white hover:opacity-90">Edit Competition</a>
                <a href="{{ route('admin.talent-competition.settings', $talentEvent) }}" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Settings</a>
                <a href="{{ route('admin.talent-competition.judges', $talentEvent) }}" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Judges</a>
                <a href="{{ route('admin.talent-participants.index', ['event' => $talentEvent->id]) }}" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Participants</a>
                <a href="{{ route('admin.live.talent') }}" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Live Monitoring</a>
                <a href="{{ route('admin.results.talent.show', $talentEvent) }}" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Results →</a>

                <form method="POST" action="{{ route('admin.talent-competition.open-registration', $talentEvent) }}">
                    @csrf
                    <button type="submit" class="rounded-xl border border-emerald-500/40 px-4 py-2 text-sm font-semibold text-emerald-200 hover:bg-emerald-500/10">Open Registration</button>
                </form>
                <form method="POST" action="{{ route('admin.talent-competition.close-registration', $talentEvent) }}">
                    @csrf
                    <button type="submit" class="rounded-xl border border-amber-500/40 px-4 py-2 text-sm font-semibold text-amber-200 hover:bg-amber-500/10">Close Registration</button>
                </form>
                <form method="POST" action="{{ route('admin.talent.open-voting', $talentEvent) }}">
                    @csrf
                    <button type="submit" class="rounded-xl border border-emerald-500/40 px-4 py-2 text-sm font-semibold text-emerald-200 hover:bg-emerald-500/10">Open Voting</button>
                </form>
                <form method="POST" action="{{ route('admin.talent-competition.close-voting', $talentEvent) }}">
                    @csrf
                    <button type="submit" class="rounded-xl border border-amber-500/40 px-4 py-2 text-sm font-semibold text-amber-200 hover:bg-amber-500/10">Close Voting</button>
                </form>
                @if ($canPublishResults)
                    <form method="POST" action="{{ route('admin.talent.publish-results', $talentEvent) }}">
                        @csrf
                        <button type="submit" class="rounded-xl border border-cyan-500/40 px-4 py-2 text-sm font-semibold text-cyan-200 hover:bg-cyan-500/10">Publish Results</button>
                    </form>
                @endif
                @unless ($talentEvent->published_to_students)
                    <form method="POST" action="{{ route('admin.talent-competition.publish', $talentEvent) }}">
                        @csrf
                        <button type="submit" class="rounded-xl border border-emerald-500/40 px-4 py-2 text-sm font-semibold text-emerald-200 hover:bg-emerald-500/10">Publish Competition</button>
                    </form>
                @endunless
                @unless ($talentEvent->isArchived())
                    <form method="POST" action="{{ route('admin.talent-competition.archive', $talentEvent) }}" onsubmit="return confirm('Archive this competition?');">
                        @csrf
                        <button type="submit" class="rounded-xl border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Archive</button>
                    </form>
                @endunless
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                <h2 class="text-base font-semibold text-white">Competition Information</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Organizer</dt><dd class="text-slate-200">{{ $talentEvent->organizer ?: '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Venue</dt><dd class="text-slate-200">{{ $talentEvent->venue }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Event Date</dt><dd class="text-slate-200">{{ optional($talentEvent->event_date)->format('M d, Y g:i A') }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Type</dt><dd class="text-slate-200">{{ $talentEvent->type?->label() }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Visible to Students</dt><dd class="text-slate-200">{{ $talentEvent->published_to_students ? 'Yes' : 'No' }}</dd></div>
                </dl>
                @if ($talentEvent->description)
                    <p class="mt-4 text-sm text-slate-400">{{ $talentEvent->description }}</p>
                @endif
            </section>

            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                <h2 class="text-base font-semibold text-white">Schedule</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Registration</dt><dd class="text-right text-slate-200">{{ $talentEvent->registrationWindowLabel() }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Submission Deadline</dt><dd class="text-slate-200">{{ optional($talentEvent->submission_deadline)->format('M d, Y g:i A') ?: '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Voting</dt><dd class="text-right text-slate-200">{{ $talentEvent->votingWindowLabel() }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Results Target</dt><dd class="text-slate-200">{{ optional($talentEvent->results_publish_at)->format('M d, Y g:i A') ?: '—' }}</dd></div>
                </dl>
            </section>

            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                <h2 class="text-base font-semibold text-white">Rules</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Max Participants</dt><dd class="text-slate-200">{{ $talentEvent->contestantLimitLabel() }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Performance Duration</dt><dd class="text-slate-200">{{ $talentEvent->performanceDurationLabel() }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Max Video Duration</dt><dd class="text-slate-200">{{ $talentEvent->maxVideoDurationLabel() }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Max Upload Size</dt><dd class="text-slate-200">{{ $talentEvent->maxUploadSizeMb() }} MB</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Formats</dt><dd class="text-slate-200">.{{ implode(', .', $talentEvent->acceptedVideoFormatsArray()) }}</dd></div>
                </dl>
            </section>

            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                <h2 class="text-base font-semibold text-white">Registration & Voting</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Registration Method</dt><dd class="text-slate-200">{{ $talentEvent->registrationMethodLabel() }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Submission Method</dt><dd class="text-slate-200">{{ $talentEvent->submissionMethodLabel() }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Voting Method</dt><dd class="text-right text-slate-200">{{ $talentEvent->votingMethodLabel() }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Ranking Method</dt><dd class="text-slate-200">{{ $talentEvent->rankingMethodLabel() }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Number of Winners</dt><dd class="text-slate-200">{{ $talentEvent->number_of_winners ?? 3 }}</dd></div>
                </dl>
            </section>
        </div>

        <div class="mt-6">
            <a href="{{ route('admin.talent-competition.index') }}" class="text-sm font-semibold text-violet-300 hover:text-violet-200">← Back to Competition Management</a>
        </div>
    </x-admin-portal>
</x-app-layout>

<x-app-layout>
    <x-faculty-portal title="Dashboard" :user="$user" :notifications-count="$notificationsCount">
        <section class="overflow-hidden rounded-2xl border border-teal-500/20 bg-gradient-to-br from-teal-900/70 via-slate-900 to-emerald-900/30 p-6 sm:p-8">
            <span class="inline-block rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-teal-200">Faculty Portal</span>
            <h2 class="mt-4 text-2xl font-bold text-white sm:text-3xl">{{ $user->name }}</h2>
            <p class="mt-3 max-w-2xl text-slate-300">
                @if ($hasJudgingAssignment)
                    Review assigned competitions, evaluate participant performances, submit scores, and stay informed with school announcements and school events{{ $showFundraising ? ', and support fundraising campaigns' : '' }}.
                @else
                    Stay informed with school announcements, elections, school events, and published talent competitions{{ $showFundraising ? ', and support fundraising campaigns' : '' }}.
                @endif
            </p>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                @if ($hasJudgingAssignment)
                <a
                    href="{{ route('faculty.judging.index', ['filter' => 'current']) }}"
                    class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-900/30 transition hover:opacity-90 focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-400 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900 sm:w-auto"
                    aria-label="View assigned competitions"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 21h8m-4-4v4m-6.5-8.5A5.5 5.5 0 015 6.5V5a2 2 0 012-2h10a2 2 0 012 2v1.5a5.5 5.5 0 01-.5 2.5M6.5 12.5h11"/>
                    </svg>
                    View Assigned Competitions
                </a>
                @elseif ($showTalent)
                <a
                    href="{{ route('faculty.talent.index') }}"
                    class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-900/30 transition hover:opacity-90 focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-400 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900 sm:w-auto"
                    aria-label="View talent competitions"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                    View Talent Competitions
                </a>
                @endif

                <a
                    href="{{ route('faculty.events.index') }}"
                    class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border border-teal-400/40 bg-transparent px-5 py-2.5 text-sm font-semibold text-teal-100 transition hover:border-teal-300/60 hover:bg-teal-500/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-400 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900 sm:w-auto"
                    aria-label="View school events"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    View School Events
                </a>

                @if ($showFundraising)
                <a
                    href="{{ route('faculty.fundraising.index') }}"
                    class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border border-amber-400/40 bg-transparent px-5 py-2.5 text-sm font-semibold text-amber-100 transition hover:border-amber-300/60 hover:bg-amber-500/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900 sm:w-auto"
                    aria-label="Support fundraising campaigns"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Support a Campaign
                </a>
                @endif
            </div>
        </section>

        <div class="grid gap-4 sm:grid-cols-2 {{ ($showFundraising && ($hasJudgingAssignment || $showTalent)) ? 'xl:grid-cols-4' : 'xl:grid-cols-3' }}">
            <a href="{{ route('faculty.elections.index', ['filter' => 'open']) }}" class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 transition hover:border-teal-400/40 hover:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Open elections</p>
                <p class="mt-2 text-3xl font-bold text-white">{{ $openElectionsCount }}</p>
                <p class="mt-1 text-sm text-teal-300">Currently open →</p>
            </a>
            <a href="{{ route('faculty.events.index', ['filter' => 'upcoming']) }}" class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 transition hover:border-teal-400/40 hover:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Upcoming events</p>
                <p class="mt-2 text-3xl font-bold text-white">{{ $upcomingEventsCount }}</p>
                <p class="mt-1 text-sm text-teal-300">Scheduled upcoming →</p>
            </a>
            @if ($hasJudgingAssignment)
            <a href="{{ route('faculty.judging.index', ['filter' => 'current']) }}" class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 transition hover:border-teal-400/40 hover:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Assigned competitions</p>
                <p class="mt-2 text-3xl font-bold text-white">{{ $assignedCompetitionsCount }}</p>
                <p class="mt-1 text-sm text-teal-300">Open judging →</p>
            </a>
            @elseif ($showTalent)
            <a href="{{ route('faculty.talent.index') }}" class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 transition hover:border-teal-400/40 hover:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Talent competitions</p>
                <p class="mt-2 text-3xl font-bold text-white">{{ $publishedTalentCount }}</p>
                <p class="mt-1 text-sm text-teal-300">View published →</p>
            </a>
            @endif
            @includeWhen($showFundraising, 'faculty.partials.fundraising-overview-card', [
                'activeFundraisersCount' => $activeFundraisersCount,
            ])
        </div>

        @include('dashboards._upcoming-activities', [
            'upcomingSchedule' => $upcomingSchedule,
            'activitiesDescription' => 'View-only overview of elections, school events, talent competitions, and fundraising campaigns.',
            'activitiesBorder' => 'border-teal-500/15',
        ])

        @if ($hasJudgingAssignment)
        <section class="rounded-2xl border border-amber-500/20 bg-amber-500/5 p-5 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold text-amber-100">Assigned Competitions</h3>
                    <p class="mt-1 text-sm text-amber-100/80">Current competitions where the Super Administrator assigned you as a judge.</p>
                </div>
                <a href="{{ route('faculty.judging.index', ['filter' => 'current']) }}" class="text-sm font-semibold text-amber-200 hover:text-amber-100">View all →</a>
            </div>

            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                @forelse ($assignedCompetitions as $competition)
                    @php
                        $assignment = $assignments[$competition->id] ?? null;
                        $p = $progress[$competition->id] ?? ['approved' => 0, 'submitted' => 0, 'percent' => 0, 'judging_status' => 'Not Started'];
                    @endphp
                    <article class="rounded-xl border border-amber-500/15 bg-slate-950/40 p-4">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <h4 class="font-semibold text-white">{{ $competition->title }}</h4>
                                <p class="mt-1 text-xs text-slate-400">
                                    {{ $competition->talent_category?->label() ?? $competition->type?->label() ?? 'Talent' }}
                                    · {{ $competition->displayStatusLabel() }}
                                </p>
                            </div>
                            <span class="rounded-full border border-teal-500/30 bg-teal-500/10 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-teal-200">
                                {{ $assignment?->roleLabel() ?? 'Judge' }}
                            </span>
                        </div>
                        <dl class="mt-3 grid grid-cols-2 gap-2 text-xs text-slate-400">
                            <div>
                                <dt class="uppercase tracking-wide text-slate-500">Date</dt>
                                <dd class="mt-0.5 text-slate-200">{{ optional($competition->event_date)->format('M d, Y') ?? 'TBA' }}</dd>
                            </div>
                            <div>
                                <dt class="uppercase tracking-wide text-slate-500">Participants</dt>
                                <dd class="mt-0.5 text-slate-200">{{ $competition->approved_entries_count ?? 0 }}</dd>
                            </div>
                            <div class="col-span-2">
                                <dt class="uppercase tracking-wide text-slate-500">Judging Status</dt>
                                <dd class="mt-0.5 text-amber-100">{{ $p['judging_status'] }} · {{ $p['percent'] }}% complete</dd>
                            </div>
                        </dl>
                        <div class="mt-4">
                            <a href="{{ route('faculty.judging.show', $competition) }}" class="inline-flex rounded-lg bg-gradient-to-r from-teal-500 to-emerald-400 px-3 py-1.5 text-xs font-semibold text-slate-950">Open Judging</a>
                        </div>
                    </article>
                @empty
                    <div class="lg:col-span-2 rounded-xl border border-dashed border-amber-500/20 px-4 py-8 text-center text-sm text-amber-100/70">
                        @if ($pastAssignedCount > 0)
                            No current competitions to judge.
                            <a href="{{ route('faculty.judging.index', ['filter' => 'past']) }}" class="font-semibold text-amber-100 hover:text-white">View past assignments →</a>
                        @else
                            No competitions assigned yet. The Super Administrator must assign you as a judge before competitions appear here.
                        @endif
                    </div>
                @endforelse
            </div>
        </section>
        @endif

        <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold text-white">Announcements</h3>
                    <p class="mt-1 text-sm text-slate-400">Messages targeted to faculty and all users.</p>
                </div>
                <a href="{{ route('faculty.announcements.index') }}" class="text-sm font-semibold text-teal-300 hover:text-teal-200">View all →</a>
            </div>

            <ul class="mt-4 space-y-3">
                @forelse ($announcements as $announcement)
                    <li>
                        <a href="{{ route('faculty.announcements.show', $announcement) }}" class="block rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3 transition hover:border-teal-500/30">
                            <p class="font-medium text-white">{{ $announcement->title }}</p>
                            <p class="mt-1 line-clamp-2 text-sm text-slate-400">{{ $announcement->summary ?? strip_tags((string) $announcement->body) }}</p>
                        </a>
                    </li>
                @empty
                    <li class="rounded-xl border border-dashed border-slate-700 px-4 py-6 text-center text-sm text-slate-500">
                        No announcements for faculty right now.
                    </li>
                @endforelse
            </ul>
        </section>
    </x-faculty-portal>
</x-app-layout>

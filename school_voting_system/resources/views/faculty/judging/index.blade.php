<x-app-layout>
    <x-faculty-portal title="Assigned Competitions" :user="$user" :notifications-count="$notificationsCount">
        <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
            <p class="text-sm text-slate-400">
                Talent competitions where you are assigned as a judge. You can only judge competitions assigned to you by the Super Administrator.
            </p>
        </section>

        <div class="space-y-4">
            @forelse ($competitions as $competition)
                @php
                    $p = $progress[$competition->id] ?? ['approved' => 0, 'submitted' => 0, 'remaining' => 0, 'drafted' => 0, 'percent' => 0, 'judging_status' => 'Not Started'];
                    $assignment = $assignments[$competition->id] ?? null;
                    $registrationOpen = $competition->isRegistrationOpen();
                @endphp
                <article class="overflow-hidden rounded-2xl border border-teal-500/15 bg-slate-900/70">
                    <x-competition-card-banner :event="$competition" />
                    <div class="p-5">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full border border-teal-500/30 bg-teal-500/10 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-teal-200">
                                        {{ $assignment?->roleLabel() ?? 'Judge' }}
                                    </span>
                                    <span class="rounded-full border border-slate-600/40 bg-slate-800/60 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-300">
                                        {{ $competition->talent_category?->label() ?? 'Talent' }}
                                    </span>
                                    <span @class([
                                        'rounded-full px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide',
                                        'bg-emerald-500/15 text-emerald-200' => $registrationOpen,
                                        'bg-slate-800/80 text-slate-300' => ! $registrationOpen,
                                    ])>
                                        {{ $registrationOpen ? 'Registration Open' : 'Registration Closed' }}
                                    </span>
                                </div>
                                <h2 class="mt-2 text-lg font-semibold text-white">{{ $competition->title }}</h2>
                                <p class="mt-1 text-sm text-slate-400">
                                    {{ $competition->displayStatusLabel() }}
                                    · {{ optional($competition->event_date)->format('M d, Y') ?? 'Date TBA' }}
                                    · {{ $competition->approved_entries_count ?? 0 }} participants
                                </p>
                                <div class="mt-3">
                                    <div class="flex items-center justify-between text-xs text-slate-500">
                                        <span>Progress · {{ $p['judging_status'] }}</span>
                                        <span>{{ $p['submitted'] }}/{{ $p['approved'] }} · {{ $p['percent'] }}%</span>
                                    </div>
                                    <div class="mt-1 h-2 overflow-hidden rounded-full bg-slate-800">
                                        <div class="h-full rounded-full bg-gradient-to-r from-teal-500 to-emerald-400" style="width: {{ min(100, $p['percent']) }}%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a
                                    href="{{ route('faculty.judging.show', $competition) }}"
                                    class="rounded-xl bg-gradient-to-r from-teal-500 to-emerald-400 px-4 py-2 text-sm font-semibold text-slate-950"
                                >
                                    Open Judging
                                </a>
                                <a
                                    href="{{ route('faculty.judging.show', $competition) }}"
                                    class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800"
                                >
                                    View Participants
                                </a>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-700 px-4 py-10 text-center text-sm text-slate-500">
                    You have not been assigned to any competitions yet. The Super Administrator must assign you as a judge.
                </div>
            @endforelse
        </div>

        <div>{{ $competitions->links() }}</div>
    </x-faculty-portal>
</x-app-layout>

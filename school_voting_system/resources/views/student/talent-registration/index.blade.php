<x-app-layout>
    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Talent Competitions</h1>
                <p class="mt-1 text-sm text-slate-400">Browse competitions, review details, and register when the window is open.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('student.talent-registration.my-entries') }}" class="inline-flex items-center gap-2 rounded-xl border border-cyan-500/30 bg-cyan-500/10 px-4 py-2 text-sm font-semibold text-cyan-100 transition hover:bg-cyan-500/20">
                    My Entries
                </a>
                <a href="{{ route('student.talent-voting.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:bg-slate-800">
                    Back to Talent Voting
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="mt-6 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mt-6 rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                {{ session('error') }}
            </div>
        @endif

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            @forelse ($events as $event)
                @php
                    $action = $flow->registrationAction($event, auth()->user());
                    $alreadySubmitted = $myEntries->has($event->id);
                @endphp
                <article class="flex flex-col overflow-hidden rounded-2xl border border-cyan-500/15 bg-slate-900/70 transition hover:border-cyan-500/40">
                    <x-competition-card-banner :event="$event" />
                    <div class="flex flex-1 flex-col p-5">
                        <div class="flex items-center justify-between gap-2">
                            <span class="rounded-full border border-cyan-500/30 bg-cyan-500/10 px-3 py-1 text-xs font-semibold text-cyan-200">{{ $event->talent_category?->label() ?? 'Talent' }}</span>
                            <span class="text-xs text-slate-500">
                                @if ($event->isRegistrationOpen())
                                    Registration Open
                                @else
                                    {{ $event->displayStatusLabel() }}
                                @endif
                            </span>
                        </div>
                        <h2 class="mt-3 text-lg font-semibold text-white">{{ $event->title }}</h2>
                        @if ($event->description)
                            <p class="mt-2 text-sm text-slate-400 line-clamp-3">{{ $event->description }}</p>
                        @endif
                        <div class="mt-4 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                            <span>Deadline: {{ optional($event->submission_deadline ?? $event->registration_ends_at)->format('M d, Y g:i A') ?? '—' }}</span>
                            <span>Participants: {{ $event->active_entries_count ?? 0 }}{{ $event->max_contestants ? ' / '.$event->max_contestants : '' }}</span>
                        </div>
                        <div class="mt-auto flex flex-wrap gap-3 pt-4">
                            <a href="{{ route('student.talent-registration.show', $event) }}"
                                class="inline-flex flex-1 items-center justify-center rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:from-cyan-400 hover:to-sky-300">
                                View
                            </a>
                            @if ($alreadySubmitted)
                                <a href="{{ route('student.talent-registration.entry.show', $myEntries->get($event->id)) }}"
                                    class="inline-flex items-center justify-center rounded-xl border border-cyan-500/30 bg-cyan-500/10 px-4 py-2 text-sm font-semibold text-cyan-100 transition hover:bg-cyan-500/20">
                                    My Entry
                                </a>
                            @elseif ($action['can_register'])
                                <a href="{{ $action['href'] }}"
                                    class="inline-flex items-center justify-center rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:bg-slate-800">
                                    Register Now
                                </a>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="sm:col-span-2 rounded-2xl border border-slate-800 bg-slate-900/70 p-8 text-center">
                    <p class="text-sm text-slate-400">No talent competitions are available right now. Please check back later.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>

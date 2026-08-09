<x-app-layout>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-white">Talent Competitions</h1>
                    <p class="mt-1 text-sm text-slate-400">View published events, explore candidate profiles, and cast secure votes.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('student.talent-registration.index') }}" class="rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:from-cyan-400 hover:to-sky-300">
                        Register your talent
                    </a>
                    <a href="{{ route('student.talent-registration.my-entries') }}" class="rounded-xl border border-cyan-500/25 bg-slate-900 px-4 py-2 text-sm font-semibold text-cyan-300 hover:bg-slate-800">
                        My Entries
                    </a>
                    <a href="{{ route('student.dashboard') }}" class="rounded-xl border border-cyan-500/25 bg-slate-900 px-4 py-2 text-sm font-semibold text-cyan-300 hover:bg-slate-800">
                        Back to dashboard
                    </a>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                @forelse ($events as $event)
                    @php
                        $badge = $event->student_phase_badge ?? $event->displayStatusLabel();
                        $cta = $event->student_phase_cta ?? 'View Event';
                        $href = $event->student_phase_href ?? route('student.talent-voting.show', $event);
                    @endphp
                    <article class="overflow-hidden rounded-2xl border border-cyan-500/15 bg-slate-900/70">
                        <x-competition-card-banner :event="$event" />
                        <div class="p-5">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <h2 class="text-lg font-semibold text-white">{{ $event->title }}</h2>
                                    <p class="mt-1 text-xs text-cyan-300">{{ $event->type?->label() }}</p>
                                    @if ($event->description)
                                        <p class="mt-2 text-sm text-slate-300">{{ \Illuminate\Support\Str::limit($event->description, 100) }}</p>
                                    @endif
                                    <p class="mt-2 text-xs text-slate-500">{{ $event->approved_entries_count ?? $event->entries_count ?? 0 }} approved candidate(s)</p>
                                </div>
                                <div class="text-right text-xs text-slate-400">
                                    <p>{{ $event->event_date?->format('M d, Y') }}</p>
                                    @if ($event->venue)
                                        <p class="mt-1">{{ $event->venue }}</p>
                                    @endif
                                    <p class="mt-2 uppercase text-cyan-300">{{ $badge }}</p>
                                </div>
                            </div>
                            <div class="mt-4 flex flex-wrap items-center gap-3">
                                <a href="{{ $href }}" class="inline-block rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-4 py-2 text-sm font-semibold text-slate-950">
                                    {{ $cta }}
                                </a>
                                @if ($event->student_has_voted ?? false)
                                    <span class="text-xs text-emerald-300">You voted in this event</span>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-8 text-center md:col-span-2">
                        <p class="text-sm text-slate-400">No talent competitions have been published yet. Check back after your election admin approves candidates.</p>
                        <a href="{{ route('student.dashboard') }}" class="mt-4 inline-block text-sm text-cyan-300 hover:text-cyan-200">Return to dashboard</a>
                    </div>
                @endforelse
            </div>

            {{ $events->links() }}
        </div>
    </div>
</x-app-layout>

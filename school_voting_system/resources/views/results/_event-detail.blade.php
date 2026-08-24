@php
    $accent = $accent ?? 'cyan';
    $isTeal = $accent === 'teal';
    $linkClass = $isTeal ? 'text-teal-300 hover:text-teal-200' : 'text-cyan-300 hover:text-cyan-200';
    $cardBorder = $isTeal ? 'border-teal-500/15' : 'border-cyan-500/15';
    $headingClass = $isTeal ? 'text-teal-300' : 'text-cyan-300';
    $winnerBorder = $isTeal ? 'border-teal-500/15 hover:border-teal-400/25' : 'border-cyan-500/15 hover:border-cyan-400/25';
    $congratsBg = $isTeal ? 'from-emerald-500/10 to-teal-500/5' : 'from-emerald-500/10 to-cyan-500/5';
    $spotlightTheme = $isTeal ? 'faculty' : 'student';
@endphp

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <a href="{{ $backUrl }}" class="text-sm font-medium {{ $linkClass }} transition">&larr; {{ $backLabel }}</a>
        <h2 class="mt-2 text-2xl font-bold text-white">{{ $detail['name'] }}</h2>
        <p class="mt-1 text-sm text-slate-400">{{ $detail['category'] }}</p>
    </div>
</div>

{{-- Status banner --}}
@if ($detail['is_official'])
    <div class="flex items-start gap-3 rounded-2xl border border-emerald-500/25 bg-emerald-500/10 px-5 py-4">
        <span class="mt-0.5 text-lg" aria-hidden="true">🟢</span>
        <div>
            <p class="font-semibold text-emerald-200">Official Results</p>
            <p class="mt-1 text-sm text-emerald-100/80">Congratulations to the winners.</p>
        </div>
    </div>
@elseif ($detail['is_open'])
    <div class="flex items-start gap-3 rounded-2xl border border-amber-500/25 bg-amber-500/10 px-5 py-4">
        <span class="mt-0.5 text-lg" aria-hidden="true">🟡</span>
        <div>
            <p class="font-semibold text-amber-200">Voting is still ongoing.</p>
            <p class="mt-1 text-sm text-amber-100/80">Official results will be published once voting officially closes.</p>
        </div>
    </div>
@elseif (($detail['student_status_tone'] ?? '') === 'review' || ($detail['student_status'] ?? '') === 'Under Review')
    <div class="flex items-start gap-3 rounded-2xl border border-amber-500/25 bg-amber-500/10 px-5 py-4">
        <span class="mt-0.5 text-lg" aria-hidden="true">⏳</span>
        <div>
            <p class="font-semibold text-amber-200">Results are not yet available.</p>
            <p class="mt-1 text-sm text-amber-100/80">Official results will be published after administrator review.</p>
        </div>
    </div>
@else
    <div class="flex items-start gap-3 rounded-2xl border border-slate-700 bg-slate-900/70 px-5 py-4">
        <span class="mt-0.5 text-lg" aria-hidden="true">⏳</span>
        <div>
            <p class="font-semibold text-slate-200">Event not yet open</p>
            <p class="mt-1 text-sm text-slate-400">Results will be available after the voting event is completed.</p>
        </div>
    </div>
@endif

{{-- Event information --}}
<section class="mb-6 rounded-2xl border {{ $cardBorder }} bg-slate-900/70 p-5 sm:p-6">
    <h2 class="text-sm font-semibold uppercase tracking-wide {{ $headingClass }}">Event Information</h2>
    <dl class="mt-4 grid gap-4 sm:grid-cols-2">
        <div>
            <dt class="text-xs uppercase tracking-wide text-slate-500">Status</dt>
            <dd class="mt-1 font-medium text-white">{{ $detail['student_status'] }}</dd>
        </div>
        @if ($detail['event_date'])
            <div>
                <dt class="text-xs uppercase tracking-wide text-slate-500">Date</dt>
                <dd class="mt-1 font-medium text-white">{{ $detail['event_date'] }}</dd>
            </div>
        @endif
        @if ($detail['starts_at'])
            <div>
                <dt class="text-xs uppercase tracking-wide text-slate-500">Voting Starts</dt>
                <dd class="mt-1 font-medium text-white">{{ $detail['starts_at'] }}</dd>
            </div>
        @endif
        @if ($detail['ends_at'])
            <div>
                <dt class="text-xs uppercase tracking-wide text-slate-500">Voting Ends</dt>
                <dd class="mt-1 font-medium text-white">{{ $detail['ends_at'] }}</dd>
            </div>
        @endif
    </dl>
    @if ($detail['description'])
        <p class="mt-4 text-sm leading-relaxed text-slate-300">{{ $detail['description'] }}</p>
    @endif
</section>

@if ($detail['is_official'])
    <x-winner-spotlight
        :spotlight="$detail['winner_spotlight'] ?? []"
        :primary="$detail['primary_winner'] ?? null"
        :published-at="$detail['results_published_at'] ?? null"
        :published-time="$detail['results_published_time'] ?? null"
        :published-by="$detail['results_published_by'] ?? null"
        :theme="$spotlightTheme"
    />

    {{-- Legacy winners grid for talent layouts --}}
    @if (count($detail['winners']) > 0 && ($detail['winners_layout'] ?? '') !== 'election')
        <section class="mb-6">
            <h2 class="mb-4 text-lg font-bold text-white">
                @if ($detail['winners_layout'] === 'election')
                    Winner by Position
                @elseif ($detail['winners_layout'] === 'intramurals')
                    Placements &amp; Awards
                @else
                    Winners
                @endif
            </h2>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($detail['winners'] as $winner)
                    <article class="rounded-2xl border {{ $winnerBorder }} bg-slate-900/70 p-5 transition">
                        <p class="text-xs font-semibold uppercase tracking-wide {{ $headingClass }}">🏆 {{ $winner['label'] }}</p>
                        <p class="mt-2 text-lg font-semibold text-white">{{ $winner['name'] }}</p>
                        @if (! empty($winner['party']) && ($detail['winners_layout'] ?? '') === 'election')
                            <p class="mt-1 text-sm text-slate-400">Party · {{ $winner['party'] }}</p>
                        @elseif (! empty($winner['position'] ?? null))
                            <p class="mt-1 text-sm text-slate-400">{{ $winner['position'] }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Special awards (intramurals) --}}
    @if (count($detail['special_awards'] ?? []) > 0)
        <section class="mb-6">
            <h2 class="mb-4 text-lg font-bold text-white">Special Awards</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($detail['special_awards'] as $award)
                    <article class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-violet-300">{{ $award['label'] }}</p>
                        <p class="mt-2 text-lg font-semibold text-white">{{ $award['name'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Top finalists (talent competition) --}}
    @if (count($detail['top_finalists'] ?? []) > 0)
        <section class="mb-6">
            <h2 class="mb-4 text-lg font-bold text-white">Top Finalists</h2>
            <div class="overflow-hidden rounded-2xl border {{ $cardBorder }} bg-slate-900/70">
                <ul class="divide-y divide-slate-800">
                    @foreach ($detail['top_finalists'] as $finalist)
                        <li class="flex items-center justify-between gap-4 px-5 py-3.5">
                            <span class="text-sm font-medium text-slate-400">#{{ $finalist['rank'] }}</span>
                            <span class="flex-1 font-medium text-white">{{ $finalist['name'] }}</span>
                            @if (! empty($finalist['position']))
                                <span class="text-xs text-slate-500">{{ $finalist['position'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- Final rankings --}}
    @if (count($detail['rankings']) > 0 && ($detail['winners_layout'] ?? '') !== 'election')
        <section class="mb-6">
            <h2 class="mb-4 text-lg font-bold text-white">Final Rankings</h2>
            <div class="overflow-hidden rounded-2xl border {{ $cardBorder }} bg-slate-900/70">
                <ul class="divide-y divide-slate-800">
                    @foreach ($detail['rankings'] as $row)
                        @include('student.results._ranking-row', [
                            'rank' => $row['rank'] ?? null,
                            'name' => $row['name'] ?? '—',
                            'meta' => $row['position'] ?? null,
                            'votes' => $row['votes'] ?? null,
                            'percent' => $row['percent'] ?? 0,
                            'accent' => $accent,
                        ])
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- Election full rankings table --}}
    @if (($detail['winners_layout'] ?? '') === 'election' && count($detail['rankings']) > 0)
        <section class="mb-6">
            <h2 class="mb-4 text-lg font-bold text-white">Full Rankings</h2>
            @php
                $grouped = collect($detail['rankings'])->groupBy('position');
            @endphp
            <div class="space-y-4">
                @foreach ($grouped as $position => $candidates)
                    <div class="overflow-hidden rounded-2xl border {{ $cardBorder }} bg-slate-900/70">
                        <div class="border-b border-slate-800 px-5 py-3">
                            <h3 class="text-sm font-semibold {{ $headingClass }}">{{ $position }}</h3>
                        </div>
                        <ul class="divide-y divide-slate-800">
                            @foreach ($candidates as $row)
                                @include('student.results._ranking-row', [
                                    'rank' => $row['rank'] ?? null,
                                    'name' => $row['name'] ?? '—',
                                    'meta' => $row['party'] ?? null,
                                    'votes' => $row['votes'] ?? null,
                                    'percent' => $row['percent'] ?? 0,
                                    'accent' => $accent,
                                ])
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Vote statistics --}}
    @if (! empty($detail['statistics']))
        <section class="mb-6 rounded-2xl border {{ $cardBorder }} bg-slate-900/70 p-5 sm:p-6">
            <h2 class="text-lg font-bold text-white">Vote Statistics</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl bg-slate-950/50 p-4">
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Turnout</dt>
                    <dd class="mt-1 text-2xl font-bold text-emerald-300">{{ number_format($detail['statistics']['turnout_percent'], 1) }}%</dd>
                </div>
                <div class="rounded-xl bg-slate-950/50 p-4">
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Total Votes</dt>
                    <dd class="mt-1 text-2xl font-bold text-white">{{ number_format($detail['statistics']['total_votes']) }}</dd>
                </div>
                <div class="rounded-xl bg-slate-950/50 p-4">
                    <dt class="text-xs uppercase tracking-wide text-slate-500">
                        {{ ($detail['type'] ?? '') === 'election' ? 'Eligible Voters' : 'Contestants' }}
                    </dt>
                    <dd class="mt-1 text-2xl font-bold text-white">{{ number_format($detail['statistics']['participants']) }}</dd>
                </div>
            </dl>
        </section>
    @endif

    <div class="rounded-2xl border border-emerald-500/20 bg-gradient-to-br {{ $congratsBg }} px-6 py-8 text-center">
        <p class="text-2xl" aria-hidden="true">🎉</p>
        <p class="mt-3 text-lg font-semibold text-white">Congratulations to all winners!</p>
        <p class="mt-1 text-sm text-slate-400">Thank you to everyone who participated in this event.</p>
    </div>
@endif

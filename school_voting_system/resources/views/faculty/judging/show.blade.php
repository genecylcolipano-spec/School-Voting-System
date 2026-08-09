<x-app-layout>
    <x-faculty-portal title="{{ $competition->title }}" :user="$user" :notifications-count="$notificationsCount">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('faculty.judging.index') }}" class="text-sm font-semibold text-teal-300 hover:text-teal-200">&larr; Assigned competitions</a>
            <span class="rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-wide {{ $acceptingScores ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-200' : 'border-amber-500/30 bg-amber-500/10 text-amber-200' }}">
                {{ $acceptingScores ? 'Judging open' : 'Judging closed' }}
            </span>
        </div>

        <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-white">{{ $competition->title }}</h2>
                    <p class="mt-1 text-sm text-slate-400">{{ $competition->votingMethodLabel() }} · {{ $competition->displayStatusLabel() }}</p>
                </div>
                <div class="text-right text-sm text-slate-400">
                    <p>{{ $progress['submitted'] }}/{{ $progress['approved'] }} submitted</p>
                    <p>{{ $progress['remaining'] }} remaining</p>
                </div>
            </div>
        </section>

        <div class="space-y-3">
            @forelse ($entries as $entry)
                @php
                    $sheet = $sheets->get($entry->id);
                    $status = $sheet?->status?->label() ?? 'Not started';
                    $tone = match ($sheet?->status?->value ?? null) {
                        'submitted' => 'text-emerald-200',
                        'draft' => 'text-amber-200',
                        default => 'text-slate-400',
                    };
                @endphp
                <article class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-teal-500/15 bg-slate-900/70 p-4 sm:p-5">
                    <div class="min-w-0">
                        <p class="font-semibold text-white">{{ $entry->display_name }}</p>
                        <p class="mt-1 text-sm text-slate-400">
                            {{ $entry->performance_title ?: 'Performance' }}
                            @if ($entry->talent_category)
                                · {{ $entry->talent_category->label() }}
                            @endif
                        </p>
                        <p class="mt-1 text-xs {{ $tone }}">
                            {{ $status }}
                            @if ($sheet)
                                · {{ number_format((float) $sheet->total_score, 2) }} pts
                            @endif
                        </p>
                    </div>
                    <a
                        href="{{ route('faculty.judging.score', [$competition, $entry]) }}"
                        class="rounded-xl bg-gradient-to-r from-teal-500 to-emerald-400 px-4 py-2 text-sm font-semibold text-slate-950"
                    >
                        {{ $sheet?->isLocked() ? 'View scores' : ($sheet ? 'Continue' : 'Score') }}
                    </a>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-700 px-4 py-10 text-center text-sm text-slate-500">
                    No approved performances are ready for judging yet.
                </div>
            @endforelse
        </div>
    </x-faculty-portal>
</x-app-layout>

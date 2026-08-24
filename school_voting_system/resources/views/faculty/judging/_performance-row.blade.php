@php
    $status = $sheet?->status?->label() ?? 'Not started';
    $tone = match ($sheet?->status?->value ?? null) {
        'submitted' => 'text-emerald-200',
        'draft' => 'text-amber-200',
        default => 'text-slate-400',
    };
    $cta = $sheet?->isLocked() ? 'View scores' : ($sheet ? 'Continue' : 'Score');
    $from = $from ?? null;
    $routeParams = $from
        ? [$competition, $entry, 'from' => $from]
        : [$competition, $entry];
    $category = $entry->talentCategoryLabel();
@endphp

<article class="flex flex-wrap items-center gap-4 overflow-hidden rounded-2xl border border-teal-500/15 bg-slate-900/70 p-4 sm:p-5">
    <div class="h-24 w-24 shrink-0 overflow-hidden rounded-xl border border-slate-800 bg-slate-950">
        @include('faculty.judging._entry-photo', ['entry' => $entry])
    </div>

    <div class="min-w-0 flex-1">
        <p class="font-semibold text-white">{{ $entry->display_name }}</p>
        @if ($entry->grade_level || $entry->section)
            <p class="mt-0.5 text-xs text-slate-500">
                {{ trim(($entry->grade_level ? 'Grade '.$entry->grade_level : '').($entry->section ? ' · '.$entry->section : '')) }}
            </p>
        @endif
        <p class="mt-1 text-sm text-slate-400">
            {{ $entry->performance_title ?: 'Performance' }}
            @if ($category)
                · {{ $category }}
            @endif
        </p>
        <p class="mt-1 text-xs {{ $tone }}">
            {{ $status }}
            @if ($sheet)
                · {{ number_format((float) $sheet->total_score, 2) }} pts
            @endif
        </p>
    </div>

    <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto sm:flex-col sm:items-stretch">
        <a
            href="{{ route('faculty.judging.profile', $routeParams) }}"
            class="inline-flex items-center justify-center rounded-xl border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-200 transition hover:bg-slate-800"
        >
            View profile
        </a>
        <a
            href="{{ route('faculty.judging.score', $routeParams) }}"
            class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-teal-500 to-emerald-400 px-4 py-2 text-sm font-semibold text-slate-950"
        >
            {{ $cta }}
        </a>
    </div>
</article>

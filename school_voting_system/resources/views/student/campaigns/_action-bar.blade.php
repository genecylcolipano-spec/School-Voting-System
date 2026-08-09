@php
    $accent = $accent ?? '#22d3ee';
    $election = $election ?? null;
@endphp

<section class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-5">
    @if ($election)
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-4">
            <p class="text-sm font-semibold text-white">{{ $election->title }}</p>
            <x-admin-status-badge :status="$election->status?->value ?? 'draft'" />
        </div>
    @endif

    <div class="flex flex-col-reverse gap-4 sm:flex-row sm:items-center sm:justify-between">
        <a
            href="{{ route('student.campaigns.index') }}"
            class="inline-flex items-center justify-center rounded-xl border border-cyan-500/25 bg-transparent px-5 py-2.5 text-sm font-semibold text-cyan-300 transition hover:border-cyan-400/40 hover:bg-slate-800"
        >
            <span aria-hidden="true" class="mr-2">←</span>
            Back to Campaigns
        </a>

        <div class="flex w-full flex-col sm:w-auto sm:items-end">
            @if ($buttonState['enabled'] && $buttonState['url'])
                <a
                    href="{{ $buttonState['url'] }}"
                    class="inline-flex w-full items-center justify-center rounded-xl px-6 py-3 text-sm font-semibold text-slate-950 shadow-lg shadow-cyan-500/10 transition hover:opacity-90 sm:w-auto"
                    style="background: linear-gradient(135deg, {{ $accent }}, #38bdf8)"
                >
                    {{ $buttonState['label'] }}
                    <span aria-hidden="true" class="ml-2">→</span>
                </a>
            @else
                <button
                    type="button"
                    disabled
                    aria-disabled="true"
                    class="inline-flex w-full cursor-not-allowed items-center justify-center rounded-xl border border-slate-700 bg-slate-800/60 px-6 py-3 text-sm font-semibold text-slate-400 sm:w-auto"
                >
                    {{ $buttonState['label'] }}
                </button>
            @endif
        </div>
    </div>

    @if (! empty($buttonState['message']))
        <p @class([
            'mt-4 text-sm text-slate-400',
            'text-center sm:text-right' => $buttonState['state'] !== 'voted',
            'rounded-xl border border-emerald-500/20 bg-emerald-500/5 px-4 py-3 text-emerald-200/90' => $buttonState['state'] === 'voted',
        ])>
            {{ $buttonState['message'] }}
        </p>
    @endif
</section>

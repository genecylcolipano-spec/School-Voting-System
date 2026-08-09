<x-app-layout>
    <div class="mx-auto max-w-2xl px-4 py-10 sm:px-6">
        <div class="rounded-2xl border border-emerald-500/25 bg-slate-900/70 p-6 text-center sm:p-8">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full border border-emerald-500/30 bg-emerald-500/15 text-emerald-300">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="mt-4 text-2xl font-bold text-white">Registration Successful</h1>
            <p class="mt-2 text-sm text-slate-400">Your performance entry has been submitted and is awaiting review.</p>

            <dl class="mt-8 grid gap-4 text-left sm:grid-cols-2">
                <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3 sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Competition Name</dt>
                    <dd class="mt-1 text-sm font-medium text-white">{{ $talentEvent->title }}</dd>
                </div>
                <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Entry Number</dt>
                    <dd class="mt-1 text-sm font-medium text-cyan-200">{{ $entry->entry_number ?: '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Registration Status</dt>
                    <dd class="mt-1 text-sm font-medium text-amber-200">{{ $entry->statusLabel() }}</dd>
                </div>
                <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Submission Date</dt>
                    <dd class="mt-1 text-sm font-medium text-white">{{ optional($entry->submitted_at)->format('M d, Y') ?? '—' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Submission Time</dt>
                    <dd class="mt-1 text-sm font-medium text-white">{{ optional($entry->submitted_at)->format('g:i A') ?? '—' }}</dd>
                </div>
            </dl>

            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="{{ route('student.talent-registration.entry.show', $entry) }}" class="rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-5 py-2.5 text-sm font-semibold text-slate-950 transition hover:from-cyan-400 hover:to-sky-300">
                    View My Entry
                </a>
                <a href="{{ route('student.talent-registration.index') }}" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm font-semibold text-slate-300 transition hover:bg-slate-800">
                    Return to Competitions
                </a>
            </div>
        </div>
    </div>
</x-app-layout>

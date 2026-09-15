<x-app-layout>
    <x-faculty-portal title="{{ $competition->title }}" :user="$user" :notifications-count="$notificationsCount">
        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
            <a href="{{ route('faculty.talent.index') }}" class="text-sm font-semibold text-teal-300 hover:text-teal-200">&larr; Back to competitions</a>
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-200">View only</span>
                <x-admin-status-badge
                    :status="$competition->currentStatusKey()"
                    :label="$competition->displayStatusLabel()"
                />
            </div>
        </div>

        <article class="overflow-hidden rounded-2xl border border-teal-500/15 bg-slate-900/70">
            <x-competition-card-banner :event="$competition" />
            <div class="p-4 sm:p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <h2 class="text-2xl font-bold text-white">{{ $competition->title }}</h2>
                        <p class="mt-1 text-sm text-teal-300">{{ $competition->talent_category?->label() ?? $competition->type?->label() ?? 'Talent' }}</p>
                        @if ($competition->venue)
                            <p class="mt-1 text-sm text-slate-400">{{ $competition->venue }}</p>
                        @endif
                    </div>
                    <p class="text-sm text-slate-300 sm:text-right">{{ optional($competition->event_date)->format('M d, Y · g:i A') ?? 'TBA' }}</p>
                </div>

                @if ($competition->description)
                    <div class="mt-6 whitespace-pre-line text-slate-200">{{ $competition->description }}</div>
                @else
                    <p class="mt-6 text-slate-400">No description provided.</p>
                @endif

                <p class="mt-6 text-sm text-slate-400">{{ $competition->approved_entries_count ?? 0 }} approved candidate(s)</p>

                <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                    @if ($isAssignedJudge)
                        <a
                            href="{{ route('faculty.judging.show', $competition) }}"
                            class="inline-flex min-h-10 w-full items-center justify-center rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white sm:w-auto"
                        >
                            Judge this competition
                        </a>
                    @endif
                    @if ($hasOfficialResults)
                        <a
                            href="{{ route('faculty.results.talent.show', $competition) }}"
                            class="inline-flex min-h-10 w-full items-center justify-center rounded-xl border border-teal-400/40 px-4 py-2 text-sm font-semibold text-teal-100 sm:w-auto"
                        >
                            View official results
                        </a>
                    @endif
                </div>
            </div>
        </article>
    </x-faculty-portal>
</x-app-layout>

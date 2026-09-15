<x-app-layout>
    <x-faculty-portal title="Talent Competitions" :user="$user" :notifications-count="$notificationsCount">
        <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
            <p class="text-sm text-slate-400">
                Browse published talent competitions. Faculty accounts are view-only and cannot vote or register.
            </p>
        </section>

        <div class="grid gap-4 md:grid-cols-2">
            @forelse ($competitions as $competition)
                <article class="overflow-hidden rounded-2xl border border-teal-500/15 bg-slate-900/70">
                    <x-competition-card-banner :event="$competition" />
                    <div class="p-4 sm:p-5">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <h2 class="text-lg font-semibold text-white">{{ $competition->title }}</h2>
                                <p class="mt-1 text-xs text-teal-300">{{ $competition->talent_category?->label() ?? $competition->type?->label() ?? 'Talent' }}</p>
                            </div>
                            <div class="sm:shrink-0 sm:text-right">
                                <span class="block text-xs text-slate-400">{{ optional($competition->event_date)->format('M d, Y · g:i A') ?? 'TBA' }}</span>
                                <div class="mt-2 sm:flex sm:justify-end">
                                    <x-admin-status-badge
                                        :status="$competition->currentStatusKey()"
                                        :label="$competition->displayStatusLabel()"
                                    />
                                </div>
                            </div>
                        </div>
                        @if ($competition->venue)
                            <p class="mt-2 text-sm text-slate-400">{{ $competition->venue }}</p>
                        @endif
                        @if ($competition->description)
                            <p class="mt-3 line-clamp-3 text-sm text-slate-300">{{ $competition->description }}</p>
                        @endif
                        <p class="mt-3 text-xs text-slate-500">{{ $competition->approved_entries_count ?? 0 }} approved candidate(s)</p>
                        <a
                            href="{{ route('faculty.talent.show', $competition) }}"
                            class="mt-4 inline-flex min-h-10 w-full items-center justify-center rounded-xl bg-gradient-to-r from-teal-500 to-emerald-400 px-4 py-2 text-sm font-semibold text-slate-950 sm:w-auto"
                        >
                            View details
                        </a>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-700 px-4 py-8 text-center text-sm text-slate-500 md:col-span-2">
                    No talent competitions have been published yet.
                </div>
            @endforelse
        </div>

        <div class="overflow-x-auto">{{ $competitions->links() }}</div>
    </x-faculty-portal>
</x-app-layout>

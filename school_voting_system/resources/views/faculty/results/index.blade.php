<x-app-layout>
    <x-faculty-portal title="Results" :user="$user" :notifications-count="$notificationsCount">
        <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
            <p class="text-sm text-slate-400">Official results of student elections and talent competitions after administrators publish them.</p>
        </section>

        @if (! $hasEvents)
            <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-teal-500/20 bg-slate-900/50 px-6 py-16 text-center">
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-teal-500/10 text-3xl">🏆</div>
                <h2 class="text-xl font-bold text-white">No Results Available</h2>
                <p class="mt-2 max-w-md text-sm text-slate-400">Official results will appear here after administrators publish them.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($events as $event)
                    <article class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xl" aria-hidden="true">{{ $event['icon'] }}</span>
                                    <h2 class="text-lg font-semibold text-white">{{ $event['name'] }}</h2>
                                </div>
                                <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-400">
                                    <span class="rounded-full bg-slate-800/80 px-2.5 py-0.5 text-xs font-medium text-slate-300">{{ $event['category'] }}</span>
                                    @if ($event['date'])
                                        <span>{{ $event['date'] }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex shrink-0 flex-wrap items-center gap-3 sm:flex-col sm:items-end lg:flex-row lg:items-center">
                                <span class="rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-300">
                                    {{ $event['student_status'] }}
                                </span>
                                <a
                                    href="{{ $event['show_url'] }}"
                                    class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-teal-500 to-emerald-400 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:opacity-90"
                                >
                                    View Results
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </x-faculty-portal>
</x-app-layout>

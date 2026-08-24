<x-app-layout>
    <x-faculty-portal title="School Events" :user="$user" :notifications-count="$notificationsCount">
        <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
            <p class="text-sm text-slate-400">
                @if ($showUpcomingOnly)
                    Showing upcoming school events. Past events stay on the full list. This list is view-only.
                @else
                    Browse upcoming and past school events. This list is view-only.
                @endif
            </p>

            <nav class="mt-4 flex flex-wrap gap-2" aria-label="School event lists">
                <a
                    href="{{ route('faculty.events.index') }}"
                    @class([
                        'inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold transition',
                        'bg-gradient-to-r from-teal-500 to-emerald-400 text-slate-950' => ! $showUpcomingOnly,
                        'border border-slate-700 text-slate-300 hover:bg-slate-800' => $showUpcomingOnly,
                    ])
                >
                    All
                    <span class="rounded-full bg-black/10 px-1.5 py-0.5 text-[11px] leading-none">{{ $allCount }}</span>
                </a>
                <a
                    href="{{ route('faculty.events.index', ['filter' => 'upcoming']) }}"
                    @class([
                        'inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold transition',
                        'bg-gradient-to-r from-teal-500 to-emerald-400 text-slate-950' => $showUpcomingOnly,
                        'border border-slate-700 text-slate-300 hover:bg-slate-800' => ! $showUpcomingOnly,
                    ])
                >
                    Upcoming
                    <span class="rounded-full bg-black/10 px-1.5 py-0.5 text-[11px] leading-none">{{ $upcomingCount }}</span>
                </a>
            </nav>
        </section>

        <div class="grid gap-4 md:grid-cols-2">
            @forelse ($events as $event)
                <article class="overflow-hidden rounded-2xl border border-teal-500/15 bg-slate-900/70">
                    <x-event-image
                        :src="$event->image_url"
                        :src-medium="$event->bannerMediumUrl()"
                        :src-mobile="$event->bannerMobileUrl()"
                        :orientation="$event->imageOrientation()"
                        :contain="$event->bannerNeedsContainLayout()"
                        :alt="$event->title"
                    />
                    <div class="p-4 sm:p-5">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <h2 class="min-w-0 text-lg font-semibold text-white">{{ $event->title }}</h2>
                            <div class="sm:shrink-0 sm:text-right">
                                <span class="block text-xs text-slate-400">{{ $event->scheduleLabel() }}</span>
                                <div class="mt-2 sm:flex sm:justify-end">
                                    <x-ui.badge
                                        type="status"
                                        :tone-key="$event->campusStatusKey()"
                                        :label="$event->campusStatusLabel()"
                                    />
                                </div>
                            </div>
                        </div>
                        @if ($event->venue)
                            <p class="mt-2 text-sm text-slate-400">{{ $event->venue }}</p>
                        @endif
                        @if ($event->description)
                            <p class="mt-3 line-clamp-3 text-sm text-slate-300">{{ $event->description }}</p>
                        @endif
                        <a
                            href="{{ route('faculty.events.show', $event) }}"
                            class="mt-4 inline-flex min-h-10 w-full items-center justify-center rounded-xl bg-gradient-to-r from-teal-500 to-emerald-400 px-4 py-2 text-sm font-semibold text-slate-950 sm:w-auto"
                        >
                            View details
                        </a>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-700 px-4 py-8 text-center text-sm text-slate-500 md:col-span-2">
                    {{ $showUpcomingOnly ? 'No upcoming school events.' : 'No school events found.' }}
                </div>
            @endforelse
        </div>

        <div class="overflow-x-auto">{{ $events->links() }}</div>
    </x-faculty-portal>
</x-app-layout>

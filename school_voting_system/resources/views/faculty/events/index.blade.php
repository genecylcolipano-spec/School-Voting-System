<x-app-layout>
    <x-faculty-portal title="School Events" :user="$user" :notifications-count="$notificationsCount">
        <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
            <p class="text-sm text-slate-400">Browse upcoming and past school events. This list is view-only.</p>
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
                                <span class="block text-xs text-slate-400">{{ optional($event->event_date)->format('M d, Y') }}</span>
                                <span class="mt-1 inline-block text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ $event->displayStatusLabel() }}</span>
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-slate-400">{{ $event->venue }}</p>
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
                    No school events found.
                </div>
            @endforelse
        </div>

        <div class="overflow-x-auto">{{ $events->links() }}</div>
    </x-faculty-portal>
</x-app-layout>

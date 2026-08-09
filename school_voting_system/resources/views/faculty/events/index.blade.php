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
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <h2 class="text-lg font-semibold text-white">{{ $event->title }}</h2>
                            <span class="shrink-0 text-xs text-slate-400">{{ optional($event->event_date)->format('M d, Y') }}</span>
                        </div>
                        <p class="mt-2 text-sm text-slate-400">{{ $event->venue }}</p>
                        @if ($event->description)
                            <p class="mt-3 line-clamp-3 text-sm text-slate-300">{{ $event->description }}</p>
                        @endif
                        <a
                            href="{{ route('faculty.events.show', $event) }}"
                            class="mt-4 inline-block rounded-xl bg-gradient-to-r from-teal-500 to-emerald-400 px-4 py-2 text-sm font-semibold text-slate-950"
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

        <div>{{ $events->links() }}</div>
    </x-faculty-portal>
</x-app-layout>

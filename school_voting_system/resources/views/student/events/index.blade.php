<x-app-layout>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">Events</h1>
                    <p class="mt-1 text-sm text-slate-400">Browse school events and announcements.</p>
                </div>
                <a href="{{ route('student.dashboard') }}" class="inline-flex min-h-10 w-full items-center justify-center rounded-xl border border-cyan-500/25 bg-slate-900 px-4 py-2 text-sm font-semibold text-cyan-300 hover:bg-slate-800 sm:w-auto">
                    Back to dashboard
                </a>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                @forelse ($events as $event)
                    <article class="overflow-hidden rounded-2xl border border-cyan-500/15 bg-slate-900/70">
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
                                <p class="mt-3 text-sm text-slate-300 line-clamp-3">{{ $event->description }}</p>
                            @endif
                            <a href="{{ route('student.events.show', $event) }}" class="mt-4 inline-flex min-h-10 w-full items-center justify-center rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-4 py-2 text-sm font-semibold text-slate-950 sm:w-auto">
                                View details
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-slate-800 bg-slate-900/50 p-6 text-slate-300">
                        No events found.
                    </div>
                @endforelse
            </div>

            <div class="mt-6 overflow-x-auto">
                {{ $events->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

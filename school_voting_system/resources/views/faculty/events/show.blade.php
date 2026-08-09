<x-app-layout>
    <x-faculty-portal title="{{ $event->title }}" :user="$user" :notifications-count="$notificationsCount">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('faculty.events.index') }}" class="text-sm font-semibold text-teal-300 hover:text-teal-200">&larr; Back to events</a>
            <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-200">View only</span>
        </div>

        <article class="overflow-hidden rounded-2xl border border-teal-500/15 bg-slate-900/70">
            <x-event-image
                :src="$event->image_url"
                :src-medium="$event->bannerMediumUrl()"
                :src-mobile="$event->bannerMobileUrl()"
                :orientation="$event->imageOrientation()"
                :contain="$event->bannerNeedsContainLayout()"
                :alt="$event->title"
            />
            <div class="p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-white">{{ $event->title }}</h2>
                        <p class="mt-1 text-sm text-slate-400">{{ $event->venue }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-slate-300">{{ optional($event->event_date)->format('M d, Y') }}</p>
                        <p class="text-xs uppercase tracking-wide text-slate-500">{{ $event->status?->value ?? $event->status }}</p>
                    </div>
                </div>

                @if ($event->description)
                    <div class="mt-6 whitespace-pre-line text-slate-200">{{ $event->description }}</div>
                @else
                    <p class="mt-6 text-slate-400">No description provided.</p>
                @endif
            </div>
        </article>
    </x-faculty-portal>
</x-app-layout>

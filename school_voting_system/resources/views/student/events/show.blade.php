<x-app-layout>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between gap-4">
                <a href="{{ route('student.events.index') }}" class="text-sm font-semibold text-cyan-300 hover:text-cyan-200">← Back to events</a>
                <a href="{{ route('student.dashboard') }}" class="text-sm text-slate-300 hover:text-white">Dashboard</a>
            </div>

            <article class="overflow-hidden rounded-2xl border border-cyan-500/15 bg-slate-900/70">
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
                        <h1 class="text-2xl font-bold text-white">{{ $event->title }}</h1>
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
                    <p class="mt-6 text-slate-300">No description provided.</p>
                @endif
                </div>
            </article>
        </div>
    </div>
</x-app-layout>


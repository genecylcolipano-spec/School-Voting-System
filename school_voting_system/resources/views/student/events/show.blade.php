<x-app-layout>
    <x-student-portal title="{{ $event->title }}" :user="$user" :notifications-count="$notificationsCount">
        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
            <a href="{{ route('student.events.index') }}" class="text-sm font-semibold text-cyan-300 hover:text-cyan-200">&larr; Back to events</a>
            <x-ui.badge
                type="status"
                :tone-key="$event->campusStatusKey()"
                :label="$event->campusStatusLabel()"
            />
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
            <div class="p-4 sm:p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <h2 class="text-2xl font-bold text-white">{{ $event->title }}</h2>
                        @if ($event->venue)
                            <p class="mt-1 text-sm text-slate-400">{{ $event->venue }}</p>
                        @endif
                    </div>
                    <p class="text-sm text-slate-300 sm:text-right">{{ $event->scheduleLabel() }}</p>
                </div>

                @if ($event->description)
                    <div class="mt-6 whitespace-pre-line text-slate-200">{{ $event->description }}</div>
                @else
                    <p class="mt-6 text-slate-400">No description provided.</p>
                @endif
            </div>
        </article>
    </x-student-portal>
</x-app-layout>

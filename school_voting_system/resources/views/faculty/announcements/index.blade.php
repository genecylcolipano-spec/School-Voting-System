<x-app-layout>
    <x-faculty-portal title="Announcements" :user="$user" :notifications-count="$notificationsCount">
        <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
            <p class="text-sm text-slate-400">Messages targeted to faculty and all users.</p>
        </section>

        <div class="grid gap-4 md:grid-cols-2">
            @forelse ($announcements as $announcement)
                <article class="overflow-hidden rounded-2xl border border-teal-500/15 bg-slate-900/70">
                    @if ($announcement->hasUploadedBanner())
                        <x-event-image
                            :src="$announcement->bannerUrl()"
                            :src-medium="$announcement->bannerMediumUrl()"
                            :src-mobile="$announcement->bannerMobileUrl()"
                            :orientation="$announcement->bannerOrientation()"
                            :contain="$announcement->bannerNeedsContainLayout()"
                            :alt="$announcement->title"
                            class="rounded-none"
                        />
                    @endif
                    <div class="p-5">
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($announcement->is_pinned)
                                <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-200">Pinned</span>
                            @endif
                            @if ($announcement->category)
                                <span class="rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide {{ $announcement->category->badgeClasses() }}">{{ $announcement->category->label() }}</span>
                            @endif
                            @if ($announcement->priority && $announcement->priority !== \App\Enums\AnnouncementPriority::Normal)
                                <span class="rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide {{ $announcement->priority->badgeClasses() }}">{{ $announcement->priority->label() }}</span>
                            @endif
                        </div>
                        <div class="mt-3 flex items-start justify-between gap-3">
                            <h2 class="text-lg font-semibold text-white">{{ $announcement->title }}</h2>
                            <span class="shrink-0 text-xs text-slate-500">{{ optional($announcement->published_at)->format('M d, Y') }}</span>
                        </div>
                        @if ($announcement->summary)
                            <p class="mt-3 line-clamp-3 text-sm text-slate-400">{{ $announcement->summary }}</p>
                        @endif
                        <a
                            href="{{ route('faculty.announcements.show', $announcement) }}"
                            class="mt-4 inline-flex rounded-xl border border-teal-500/30 px-4 py-2 text-sm font-semibold text-teal-200 hover:bg-teal-500/10"
                        >
                            Read more
                        </a>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-700 px-4 py-8 text-center text-sm text-slate-500 md:col-span-2">
                    No announcements for faculty right now.
                </div>
            @endforelse
        </div>

        <div>{{ $announcements->links() }}</div>
    </x-faculty-portal>
</x-app-layout>

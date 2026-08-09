<x-app-layout>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-white">Announcements</h1>
                    <p class="mt-1 text-sm text-slate-400">Latest updates from the school portal.</p>
                </div>
                <a href="{{ route('student.dashboard') }}" class="rounded-xl border border-cyan-500/25 bg-slate-900 px-4 py-2 text-sm font-semibold text-cyan-300 hover:bg-slate-800">
                    Back to dashboard
                </a>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                @forelse ($announcements as $announcement)
                    <article class="overflow-hidden rounded-2xl border border-cyan-500/15 bg-slate-900/70">
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
                                    <span class="text-xs text-amber-300">📌</span>
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
                            <a href="{{ route('student.announcements.show', $announcement) }}" class="mt-4 inline-flex rounded-xl border border-cyan-500/30 px-4 py-2 text-sm font-semibold text-cyan-200 hover:bg-cyan-500/10">
                                Read more
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-6 text-slate-400 md:col-span-2">
                        No announcements published yet.
                    </div>
                @endforelse
            </div>

            <div class="mt-6">{{ $announcements->links() }}</div>
        </div>
    </div>
</x-app-layout>

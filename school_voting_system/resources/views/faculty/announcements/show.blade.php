<x-app-layout>
    <x-faculty-portal title="{{ $announcement->title }}" :user="$user" :notifications-count="$notificationsCount">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('faculty.announcements.index') }}" class="text-sm font-semibold text-teal-300 hover:text-teal-200">&larr; Back to announcements</a>
            <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-200">View only</span>
        </div>

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

            <div class="p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($announcement->is_pinned)
                                <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-200">Pinned</span>
                            @endif
                            @if ($announcement->category)
                                <span class="rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide {{ $announcement->category->badgeClasses() }}">{{ $announcement->category->label() }}</span>
                            @endif
                            @if ($announcement->priority)
                                <span class="rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide {{ $announcement->priority->badgeClasses() }}">{{ $announcement->priority->label() }}</span>
                            @endif
                        </div>
                        <h2 class="mt-3 text-2xl font-bold text-white">{{ $announcement->title }}</h2>
                        <p class="mt-2 text-xs text-slate-500">
                            Published {{ optional($announcement->published_at)->format('M d, Y g:i A') ?? '—' }}
                            @if ($announcement->expires_at)
                                · Expires {{ $announcement->expires_at->format('M d, Y g:i A') }}
                            @endif
                        </p>
                    </div>
                    <span class="rounded-full border border-slate-700 bg-slate-950/50 px-3 py-1 text-xs font-semibold text-slate-300">{{ $announcement->displayStatusLabel() }}</span>
                </div>

                @if ($announcement->summary)
                    <p class="mt-4 text-base text-slate-300">{{ $announcement->summary }}</p>
                @endif

                @if ($announcement->body)
                    <div class="mt-6 whitespace-pre-line text-sm leading-relaxed text-slate-200">{{ $announcement->body }}</div>
                @endif

                @if ($announcement->related_module && $announcement->related_module !== \App\Enums\AnnouncementRelatedModule::None && $announcement->relatedRecordUrl())
                    <div class="mt-6">
                        <a href="{{ $announcement->relatedRecordUrl() }}" class="inline-flex items-center gap-2 rounded-xl border border-teal-500/30 bg-teal-500/10 px-4 py-2 text-sm font-semibold text-teal-200 hover:bg-teal-500/20">
                            {{ $announcement->related_module->viewLabel() }}
                            @if ($announcement->relatedRecordTitle())
                                <span class="text-teal-100/80">— {{ $announcement->relatedRecordTitle() }}</span>
                            @endif
                        </a>
                    </div>
                @endif

                @if ($announcement->attachments->isNotEmpty())
                    <section class="mt-8 border-t border-slate-800 pt-6">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Attachments</h3>
                        <ul class="mt-3 space-y-2">
                            @foreach ($announcement->attachments as $attachment)
                                <li>
                                    <a
                                        href="{{ route('faculty.announcements.attachments.download', [$announcement, $attachment]) }}"
                                        class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3 text-sm hover:border-teal-500/30 hover:bg-slate-950/70"
                                    >
                                        <span class="font-medium text-slate-200">{{ $attachment->original_name }}</span>
                                        <span class="text-xs text-slate-500">{{ $attachment->formattedSize() }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </div>
        </article>
    </x-faculty-portal>
</x-app-layout>

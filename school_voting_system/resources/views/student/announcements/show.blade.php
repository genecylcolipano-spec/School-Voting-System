<x-app-layout>
    @php
        $preview = $preview ?? false;
    @endphp
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between gap-4">
                @if ($preview)
                    <a href="{{ route('admin.announcements.edit', $announcement) }}" class="text-sm font-semibold text-cyan-300 hover:text-cyan-200">← Back to editor</a>
                    <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-200">Admin preview</span>
                @else
                    <a href="{{ route('student.announcements.index') }}" class="text-sm font-semibold text-cyan-300 hover:text-cyan-200">← Back to announcements</a>
                    <a href="{{ route('student.dashboard') }}" class="text-sm text-slate-300 hover:text-white">Dashboard</a>
                @endif
            </div>

            @if ($preview)
                <div class="mb-4 rounded-xl border border-amber-500/20 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                    This is a read-only preview. The announcement has not been published to students unless it is already live.
                </div>
            @endif

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
                            <h1 class="mt-3 text-2xl font-bold text-white">{{ $announcement->title }}</h1>
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
                            <a href="{{ $announcement->relatedRecordUrl() }}" class="inline-flex items-center gap-2 rounded-xl border border-cyan-500/30 bg-cyan-500/10 px-4 py-2 text-sm font-semibold text-cyan-200 hover:bg-cyan-500/20">
                                {{ $announcement->related_module->viewLabel() }}
                                @if ($announcement->relatedRecordTitle())
                                    <span class="text-cyan-100/80">— {{ $announcement->relatedRecordTitle() }}</span>
                                @endif
                            </a>
                        </div>
                    @endif

                    @if ($announcement->attachments->isNotEmpty())
                        <section class="mt-8 border-t border-slate-800 pt-6">
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Attachments</h2>
                            <ul class="mt-3 space-y-2">
                                @foreach ($announcement->attachments as $attachment)
                                    <li>
                                        <a href="{{ ($preview ?? false) ? route('admin.announcements.attachments.download', [$announcement, $attachment]) : route('student.announcements.attachments.download', [$announcement, $attachment]) }}" class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3 text-sm hover:border-cyan-500/30 hover:bg-slate-950/70">
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
        </div>
    </div>
</x-app-layout>

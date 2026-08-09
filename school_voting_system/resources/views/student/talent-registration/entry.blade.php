<x-app-layout>
    @php
        $isPending = $entry->isPending();
        $isApproved = $entry->isApproved();
        $isRejected = $entry->status === \App\Models\TalentEventEntry::STATUS_REJECTED;
        $statusLabel = $entry->statusLabel();
        $reviewLabel = $flow->reviewStatusLabel($entry);
        $badgeClasses = match (true) {
            $isPending => 'border-amber-500/30 bg-amber-500/15 text-amber-200',
            $isApproved => 'border-emerald-500/30 bg-emerald-500/15 text-emerald-200',
            $isRejected => 'border-rose-500/30 bg-rose-500/15 text-rose-200',
            default => 'border-slate-600/40 bg-slate-800/60 text-slate-300',
        };
        $submissionMethod = $entry->isDirectVideo()
            ? 'Video Upload'
            : ($entry->video_url ? 'Video URL' : '—');
        $videoLabel = $entry->isDirectVideo()
            ? basename($entry->video_path)
            : ($entry->video_url ?: '—');
    @endphp

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Your Entry</h1>
                <p class="mt-1 text-sm text-cyan-300">{{ $talentEvent->title }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('student.talent-registration.entry.confirmation', $entry) }}" class="inline-flex items-center gap-2 rounded-xl border border-cyan-500/30 bg-cyan-500/10 px-4 py-2 text-sm font-semibold text-cyan-100 transition hover:bg-cyan-500/20">
                    Download Confirmation
                </a>
                <a href="{{ route('student.talent-registration.my-entries') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:bg-slate-800">
                    My Entries
                </a>
            </div>
        </div>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/70">
            <div class="border-b border-slate-800 px-5 py-4 sm:px-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-lg font-semibold text-white">
                            @if ($isApproved)
                                Approved
                            @elseif ($isRejected)
                                Rejected
                            @else
                                Entry Submitted Successfully
                            @endif
                        </p>
                        <p class="mt-1 text-sm text-slate-400">Entry Number: <span class="font-semibold text-cyan-200">{{ $entry->entry_number ?: '—' }}</span></p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $badgeClasses }}">
                            {{ $statusLabel }}
                        </span>
                        <span class="inline-flex items-center rounded-full border border-slate-600/40 bg-slate-800/60 px-3 py-1 text-xs font-semibold text-slate-300">
                            {{ $reviewLabel }}
                        </span>
                    </div>
                </div>

                <div class="mt-4 rounded-xl border px-4 py-3 text-sm
                    @if ($isApproved) border-emerald-500/25 bg-emerald-500/10 text-emerald-100
                    @elseif ($isRejected) border-rose-500/25 bg-rose-500/10 text-rose-100
                    @else border-amber-500/25 bg-amber-500/10 text-amber-100
                    @endif">
                    @if ($isApproved)
                        <p class="font-semibold">Congratulations!</p>
                        <p class="mt-1 text-emerald-100/90">Your performance has been approved. You are now an official contestant.</p>
                    @elseif ($isRejected)
                        <p class="font-semibold">Your submission was not approved.</p>
                        @if ($entry->review_reason)
                            <p class="mt-2 text-rose-100/95"><span class="font-semibold">Reason:</span> {{ $entry->review_reason }}</p>
                        @endif
                        <p class="mt-2 text-rose-100/80">Please contact the administrator if you need further clarification.</p>
                    @else
                        <p class="font-semibold">Your entry has been submitted successfully.</p>
                        <p class="mt-1 text-amber-100/90">Your performance is currently under review by the administrator.</p>
                        <p class="mt-1 text-amber-100/80">You will receive a notification once your submission has been approved or rejected.</p>
                    @endif
                </div>
            </div>

            <dl class="grid gap-4 px-5 py-5 sm:grid-cols-2 sm:px-6">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Competition</dt>
                    <dd class="mt-1 text-sm font-medium text-white">{{ $talentEvent->title }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Submitted</dt>
                    <dd class="mt-1 text-sm font-medium text-white">{{ optional($entry->submitted_at)->format('M d, Y g:i A') ?? '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Performance Title</dt>
                    <dd class="mt-1 text-sm font-medium text-white">{{ $entry->performance_title }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Category</dt>
                    <dd class="mt-1 text-sm font-medium text-white">{{ $entry->talent_category?->label() ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Submission Method</dt>
                    <dd class="mt-1 text-sm font-medium text-white">{{ $submissionMethod }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Video</dt>
                    <dd class="mt-1 break-all text-sm font-medium text-white">
                        @if ($entry->video_url && ! $entry->isDirectVideo())
                            <a href="{{ $entry->video_url }}" target="_blank" rel="noopener noreferrer" class="text-cyan-300 underline decoration-cyan-500/40 hover:text-cyan-200">{{ $videoLabel }}</a>
                        @else
                            {{ $videoLabel }}
                        @endif
                    </dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Description</dt>
                    <dd class="mt-1 whitespace-pre-line text-sm text-slate-300">{{ $entry->performance_description }}</dd>
                </div>
            </dl>

            @if ($entry->hasVideo())
                <div class="border-t border-slate-800 px-5 py-5 sm:px-6">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Your Video</p>
                    <div class="mt-3 overflow-hidden rounded-xl border border-slate-800 bg-slate-950/60">
                        @if ($entry->isExternalVideo())
                            <div class="aspect-video">
                                <iframe
                                    src="{{ $entry->videoEmbedUrl() }}"
                                    class="h-full w-full"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen
                                    title="Submitted performance video"
                                ></iframe>
                            </div>
                        @elseif ($entry->videoFileUrl())
                            <video
                                controls
                                playsinline
                                preload="metadata"
                                class="w-full bg-black"
                                @if ($entry->thumbnailUrl()) poster="{{ $entry->thumbnailUrl() }}" @endif
                            >
                                <source src="{{ $entry->videoFileUrl() }}">
                                Your browser does not support video playback.
                            </video>
                        @elseif ($entry->video_url)
                            <div class="px-4 py-6 text-sm text-slate-400">
                                External video link:
                                <a href="{{ $entry->video_url }}" target="_blank" rel="noopener noreferrer" class="ml-1 text-cyan-300 underline">{{ $entry->video_url }}</a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

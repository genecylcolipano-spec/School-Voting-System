<x-app-layout>
    <x-admin-portal title="Participant Details" :user="$user" :notifications-count="$notificationsCount">
        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
        @endif

        <div class="mb-4 flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.talent-participants.index', ['event' => $entry->talent_event_id]) }}" class="text-sm font-semibold text-violet-300 hover:text-violet-200">← Participants</a>
            <span class="text-slate-600">·</span>
            <a href="{{ route('admin.talent-competition.show', $entry->talentEvent) }}" class="text-sm font-semibold text-slate-400 hover:text-white">{{ $entry->talentEvent?->title }}</a>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-1">
                <div class="overflow-hidden rounded-2xl border border-violet-500/15 bg-slate-900/70">
                    @if ($entry->photoUrl())
                        <img src="{{ $entry->photoUrl() }}" alt="" class="aspect-square w-full object-cover">
                    @else
                        <div class="flex aspect-square items-center justify-center bg-violet-500/10 text-5xl font-bold text-violet-200">
                            {{ strtoupper(substr($entry->display_name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="p-5">
                        <h1 class="text-xl font-bold text-white">{{ $entry->display_name }}</h1>
                        <p class="mt-1 text-sm text-slate-400">{{ $entry->student_id_number ?: 'No Student ID' }}</p>
                        <div class="mt-3">
                            <x-admin-status-badge :status="$entry->status" />
                        </div>
                        <p class="mt-2 text-[11px] uppercase tracking-wide text-slate-500">
                            {{ $entry->source === 'self' ? 'Self-registered' : 'Admin-added' }}
                        </p>
                    </div>
                </div>

                @if ($canManage)
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.talent-participants.edit', $entry) }}" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white hover:opacity-90">Edit</a>

                        @if ($entry->status !== \App\Models\TalentEventEntry::STATUS_APPROVED)
                            <form method="POST" action="{{ route('admin.talent.entries.approve', $entry) }}">
                                @csrf
                                <button type="submit" class="rounded-xl border border-emerald-500/40 px-4 py-2 text-sm font-semibold text-emerald-200 hover:bg-emerald-500/10">Approve</button>
                            </form>
                        @endif

                        @if ($entry->status !== \App\Models\TalentEventEntry::STATUS_REJECTED)
                            <form method="POST" action="{{ route('admin.talent.entries.reject', $entry) }}" onsubmit="this.querySelector('[name=reason]').value = prompt('Reason for rejection:') || ''; return this.querySelector('[name=reason]').value !== '';">
                                @csrf
                                <input type="hidden" name="reason" value="">
                                <button type="submit" class="rounded-xl border border-rose-500/40 px-4 py-2 text-sm font-semibold text-rose-200 hover:bg-rose-500/10">Reject</button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('admin.talent.entries.status', $entry) }}">
                            @csrf
                            <input type="hidden" name="status" value="withdrawn">
                            <button type="submit" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Withdraw</button>
                        </form>
                        <form method="POST" action="{{ route('admin.talent.entries.status', $entry) }}">
                            @csrf
                            <input type="hidden" name="status" value="disqualified">
                            <button type="submit" class="rounded-xl border border-amber-500/40 px-4 py-2 text-sm font-semibold text-amber-200 hover:bg-amber-500/10">Disqualify</button>
                        </form>
                        <form method="POST" action="{{ route('admin.talent.entries.status', $entry) }}">
                            @csrf
                            <input type="hidden" name="status" value="archived">
                            <button type="submit" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Archive</button>
                        </form>

                        <form method="POST" action="{{ route('admin.talent-participants.destroy', $entry) }}" onsubmit="return confirm('Delete this participant permanently?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-xl border border-rose-500/40 px-4 py-2 text-sm font-semibold text-rose-200 hover:bg-rose-500/10">Delete</button>
                        </form>
                    </div>
                @endif
            </div>

            <div class="space-y-6 lg:col-span-2">
                <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                    <h2 class="text-base font-semibold text-white">Student Information</h2>
                    <dl class="mt-4 grid gap-3 sm:grid-cols-2 text-sm">
                        <div><dt class="text-slate-500">Grade / Year</dt><dd class="text-slate-200">{{ $entry->grade_level ?: '—' }}</dd></div>
                        <div><dt class="text-slate-500">Section</dt><dd class="text-slate-200">{{ $entry->section ?: '—' }}</dd></div>
                        <div><dt class="text-slate-500">Course / Strand</dt><dd class="text-slate-200">{{ $entry->course_strand ?: '—' }}</dd></div>
                        <div><dt class="text-slate-500">Linked Account</dt><dd class="text-slate-200">{{ $entry->student?->email ?: '—' }}</dd></div>
                        <div><dt class="text-slate-500">Submitted</dt><dd class="text-slate-200">{{ optional($entry->submitted_at ?? $entry->created_at)->format('M d, Y g:i A') }}</dd></div>
                        <div><dt class="text-slate-500">Reviewed</dt><dd class="text-slate-200">{{ optional($entry->reviewed_at)->format('M d, Y g:i A') ?: '—' }}@if($entry->reviewer) · {{ $entry->reviewer->name }}@endif</dd></div>
                    </dl>
                    @if ($entry->review_reason)
                        <p class="mt-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">Reason: {{ $entry->review_reason }}</p>
                    @endif
                </section>

                <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                    <h2 class="text-base font-semibold text-white">Performance</h2>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        @if ($entry->talentCategoryLabel())
                            <span class="rounded-full border border-violet-400/40 bg-violet-500/10 px-3 py-0.5 text-xs font-semibold text-violet-200">{{ $entry->talentCategoryLabel() }}</span>
                        @endif
                        <span class="text-sm font-semibold text-white">{{ $entry->performance_title ?: 'Untitled performance' }}</span>
                    </div>
                    @if ($entry->profile_summary)
                        <p class="mt-3 text-sm text-slate-400">{{ $entry->profile_summary }}</p>
                    @endif
                    @if ($entry->performance_description)
                        <p class="mt-2 text-sm text-slate-300">{{ $entry->performance_description }}</p>
                    @endif
                    @if ($entry->social_media)
                        <p class="mt-2 text-sm text-violet-300">{{ $entry->social_media }}</p>
                    @endif
                </section>

                <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-base font-semibold text-white">Performance Video</h2>
                        <div class="flex gap-2">
                            @if ($entry->videoFileUrl())
                                <a href="{{ $entry->videoFileUrl() }}" target="_blank" class="rounded-lg border border-violet-500/40 px-3 py-1.5 text-xs font-semibold text-violet-200 hover:bg-violet-500/10">Watch</a>
                                @if ($entry->videoDownloadUrl())
                                    <a href="{{ $entry->videoDownloadUrl() }}" class="rounded-lg border border-cyan-500/40 px-3 py-1.5 text-xs font-semibold text-cyan-200 hover:bg-cyan-500/10">Download</a>
                                @endif
                            @elseif ($entry->video_url)
                                <a href="{{ $entry->video_url }}" target="_blank" rel="noopener" class="rounded-lg border border-violet-500/40 px-3 py-1.5 text-xs font-semibold text-violet-200 hover:bg-violet-500/10">Open URL</a>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 overflow-hidden rounded-xl border border-slate-800 bg-slate-950">
                        @if ($entry->videoEmbedUrl())
                            <div class="aspect-video">
                                <iframe src="{{ $entry->videoEmbedUrl() }}" class="h-full w-full" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                            </div>
                        @elseif ($entry->videoFileUrl())
                            <video controls class="aspect-video w-full bg-black" poster="{{ $entry->thumbnailUrl() }}">
                                <source src="{{ $entry->videoFileUrl() }}">
                            </video>
                        @else
                            <div class="flex aspect-video items-center justify-center text-sm text-slate-500">No video submitted.</div>
                        @endif
                    </div>
                </section>

                <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                    <h2 class="text-base font-semibold text-white">Competition</h2>
                    <p class="mt-2 text-sm text-slate-300">{{ $entry->talentEvent?->title }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $entry->talentEvent?->displayStatusLabel() }} · {{ $entry->talentEvent?->talent_category?->label() }}</p>
                </section>
            </div>
        </div>
    </x-admin-portal>
</x-app-layout>

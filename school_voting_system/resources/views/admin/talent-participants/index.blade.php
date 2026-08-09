<x-app-layout>
    <x-admin-portal title="Talent Participants" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'Participants',
            'description' => 'Review and manage contestant registrations. Only approved participants appear in student voting.',
            'showAction' => $canManage,
            'actionLabel' => 'Add Participant',
            'action' => route('admin.talent-participants.create', array_filter(['event' => $selectedEvent])),
        ])

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">{{ session('error') }}</div>
        @endif

        @php
            $tabs = [
                'all' => 'All',
                'pending' => 'Pending',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                'withdrawn' => 'Withdrawn',
                'disqualified' => 'Disqualified',
                'archived' => 'Archived',
            ];
        @endphp

        <div class="mb-4 flex flex-wrap items-center gap-2 border-b border-slate-800 pb-3">
            @foreach ($tabs as $key => $label)
                @php
                    $params = array_filter([
                        'status' => $key === 'all' ? null : $key,
                        'event' => $selectedEvent,
                        'q' => $search ?: null,
                    ]);
                @endphp
                <a href="{{ route('admin.talent-participants.index', $params) }}"
                   class="rounded-full px-3 py-1.5 text-sm font-semibold transition {{ $activeStatus === $key ? 'bg-gradient-to-r from-violet-600 to-indigo-500 text-white' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    {{ $label }}
                    <span class="ml-1 rounded-full bg-slate-950/40 px-1.5 py-0.5 text-xs">{{ $counts[$key] ?? 0 }}</span>
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.talent-participants.index') }}" class="mb-4 flex flex-wrap items-end gap-3">
            @if ($activeStatus !== 'all')
                <input type="hidden" name="status" value="{{ $activeStatus }}">
            @endif
            <div class="min-w-[12rem] flex-1">
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Search</label>
                <input type="search" name="q" value="{{ $search }}" placeholder="Name, Student ID, title…"
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/60 px-4 py-2 text-sm text-white focus:border-violet-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Competition</label>
                <select name="event" class="mt-1 rounded-xl border border-slate-700 bg-slate-950/60 px-4 py-2 text-sm text-white">
                    <option value="">All competitions</option>
                    @foreach ($events as $event)
                        <option value="{{ $event->id }}" @selected((string) $selectedEvent === (string) $event->id)>{{ $event->title }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-500">Apply</button>
        </form>

        <div class="overflow-x-auto rounded-2xl border border-violet-500/15 bg-slate-900/70">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-800 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Photo</th>
                        <th class="px-4 py-3">Student ID</th>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Course</th>
                        <th class="px-4 py-3">Year</th>
                        <th class="px-4 py-3">Section</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3">Performance</th>
                        <th class="px-4 py-3">Submitted</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse ($participants as $entry)
                        <tr class="text-slate-300">
                            <td class="px-4 py-3">
                                @if ($entry->photoUrl())
                                    <img src="{{ $entry->photoUrl() }}" loading="lazy" alt="" class="h-10 w-10 rounded-lg object-cover">
                                @else
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-500/15 text-sm font-bold text-violet-200">{{ strtoupper(substr($entry->display_name, 0, 1)) }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs font-mono text-slate-400">{{ $entry->student_id_number ?: '—' }}</td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-white">{{ $entry->display_name }}</p>
                                <p class="text-[11px] text-slate-500">{{ $entry->talentEvent?->title }}</p>
                            </td>
                            <td class="px-4 py-3 text-xs">{{ $entry->course_strand ?: '—' }}</td>
                            <td class="px-4 py-3 text-xs">{{ $entry->grade_level ?: '—' }}</td>
                            <td class="px-4 py-3 text-xs">{{ $entry->section ?: '—' }}</td>
                            <td class="px-4 py-3 text-xs">{{ $entry->talentCategoryLabel() ?? '—' }}</td>
                            <td class="max-w-[10rem] truncate px-4 py-3 text-xs">{{ $entry->performance_title ?: '—' }}</td>
                            <td class="px-4 py-3 text-xs text-slate-400">{{ optional($entry->submitted_at ?? $entry->created_at)->format('M d, Y') }}</td>
                            <td class="px-4 py-3"><x-admin-status-badge :status="$entry->status" /></td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.talent-participants.show', $entry) }}" class="rounded-lg border border-slate-700 px-2 py-1 text-xs font-semibold text-slate-300 hover:bg-slate-800">View</a>
                                    @if ($canManage)
                                        <a href="{{ route('admin.talent-participants.edit', $entry) }}" class="rounded-lg border border-slate-700 px-2 py-1 text-xs font-semibold text-slate-300 hover:bg-slate-800">Edit</a>
                                        @if ($entry->hasVideo())
                                            @if ($entry->videoEmbedUrl())
                                                <a href="{{ $entry->video_url }}" target="_blank" rel="noopener" class="rounded-lg border border-violet-500/40 px-2 py-1 text-xs font-semibold text-violet-200 hover:bg-violet-500/10">Watch</a>
                                            @elseif ($entry->videoFileUrl())
                                                <a href="{{ $entry->videoFileUrl() }}" target="_blank" class="rounded-lg border border-violet-500/40 px-2 py-1 text-xs font-semibold text-violet-200 hover:bg-violet-500/10">Watch</a>
                                                @if ($entry->videoDownloadUrl())
                                                    <a href="{{ $entry->videoDownloadUrl() }}" class="rounded-lg border border-cyan-500/40 px-2 py-1 text-xs font-semibold text-cyan-200 hover:bg-cyan-500/10">Download</a>
                                                @endif
                                            @endif
                                        @endif
                                        @if ($entry->status !== \App\Models\TalentEventEntry::STATUS_APPROVED)
                                            <form method="POST" action="{{ route('admin.talent.entries.approve', $entry) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-emerald-500/40 px-2 py-1 text-xs font-semibold text-emerald-200 hover:bg-emerald-500/10">Approve</button>
                                            </form>
                                        @endif
                                        @if ($entry->status !== \App\Models\TalentEventEntry::STATUS_REJECTED)
                                            <form method="POST" action="{{ route('admin.talent.entries.reject', $entry) }}" onsubmit="this.querySelector('[name=reason]').value = prompt('Reason for rejection:') || ''; return this.querySelector('[name=reason]').value !== '';">
                                                @csrf
                                                <input type="hidden" name="reason" value="">
                                                <button type="submit" class="rounded-lg border border-rose-500/40 px-2 py-1 text-xs font-semibold text-rose-200 hover:bg-rose-500/10">Reject</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.talent-participants.destroy', $entry) }}" onsubmit="return confirm('Delete this participant?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-slate-700 px-2 py-1 text-xs font-semibold text-slate-400 hover:bg-rose-500/10 hover:text-rose-200">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-4 py-10 text-center text-slate-400">
                                No participants found.
                                @if ($canManage)
                                    <a href="{{ route('admin.talent-participants.create') }}" class="ml-1 font-semibold text-violet-300 hover:text-violet-200">Add one →</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $participants->links() }}</div>
    </x-admin-portal>
</x-app-layout>

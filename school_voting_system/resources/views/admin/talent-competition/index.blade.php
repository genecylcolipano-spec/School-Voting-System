<x-app-layout>
    <x-admin-portal
        title="Competition Management"
        :user="$user"
        :notifications-count="$notificationsCount"
        :assigned-role="$assignedRole"
    >
        @include('admin.partials.page-header', [
            'title' => 'Competition Management',
            'description' => 'Create, configure, publish, and archive talent competitions.',
            'showAction' => $canManageTalentEvents,
            'actionLabel' => 'Create Competition',
            'action' => route('admin.talent-competition.create'),
        ])

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('admin.talent-competition.index') }}" class="mb-4 flex flex-wrap items-end gap-3">
            <div class="min-w-[12rem] flex-1">
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Search</label>
                <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Title, code, venue…"
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/60 px-4 py-2 text-sm text-white focus:border-violet-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Status</label>
                <select name="status" class="mt-1 rounded-xl border border-slate-700 bg-slate-950/60 px-4 py-2 text-sm text-white">
                    <option value="">All</option>
                    @foreach (['draft' => 'Draft', 'registration_open' => 'Registration Open', 'registration_closed' => 'Registration Closed', 'voting_open' => 'Voting Open', 'voting_closed' => 'Voting Closed', 'results_published' => 'Results Published', 'archived' => 'Archived'] as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Category</label>
                <select name="category" class="mt-1 rounded-xl border border-slate-700 bg-slate-950/60 px-4 py-2 text-sm text-white">
                    <option value="">All</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->value }}" @selected($filters['category'] === $category->value)>{{ $category->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Sort</label>
                <select name="sort" class="mt-1 rounded-xl border border-slate-700 bg-slate-950/60 px-4 py-2 text-sm text-white">
                    <option value="newest" @selected($filters['sort'] === 'newest')>Newest</option>
                    <option value="oldest" @selected($filters['sort'] === 'oldest')>Oldest</option>
                    <option value="title" @selected($filters['sort'] === 'title')>Title</option>
                    <option value="participants" @selected($filters['sort'] === 'participants')>Participants</option>
                    <option value="votes" @selected($filters['sort'] === 'votes')>Votes</option>
                </select>
            </div>
            <button type="submit" class="rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-500">Apply</button>
            @if ($filters['q'] || $filters['status'] || $filters['category'] || $filters['sort'] !== 'newest')
                <a href="{{ route('admin.talent-competition.index') }}" class="text-sm font-semibold text-slate-400 hover:text-white">Clear</a>
            @endif
        </form>

        <div class="overflow-x-auto rounded-2xl border border-violet-500/15 bg-slate-900/70">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-800 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Banner</th>
                        <th class="px-4 py-3">Competition</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3">Registration</th>
                        <th class="px-4 py-3">Voting</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-center">Participants</th>
                        <th class="px-4 py-3 text-center">Votes</th>
                        <th class="px-4 py-3">Created</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse ($talentEvents as $event)
                        <tr class="text-slate-300">
                            <td class="px-4 py-3">
                                <img src="{{ $event->thumbnailUrl() }}" alt="" class="h-12 w-16 rounded-lg object-cover" loading="lazy">
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-white">{{ $event->title }}</p>
                                @if ($event->competition_code)
                                    <p class="text-xs text-slate-500">{{ $event->competition_code }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs">{{ $event->talent_category?->label() ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs text-slate-400">{{ $event->registrationWindowLabel() }}</td>
                            <td class="px-4 py-3 text-xs text-slate-400">{{ $event->votingWindowLabel() }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-violet-500/10 px-2.5 py-1 text-[11px] font-semibold text-violet-200">{{ $event->displayStatusLabel() }}</span>
                            </td>
                            <td class="px-4 py-3 text-center font-semibold text-white">{{ number_format($event->entries_count) }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-white">{{ $canViewRealtimeTalentCounts || ! $event->isAcceptingVotes() ? number_format($event->votes_count) : '—' }}</td>
                            <td class="px-4 py-3 text-xs text-slate-400">{{ optional($event->created_at)->format('M d, Y') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.talent-competition.show', $event) }}" class="rounded-lg border border-slate-700 px-2 py-1 text-xs font-semibold text-slate-300 hover:bg-slate-800">View</a>
                                    @if ($canManageTalentEvents)
                                        <a href="{{ route('admin.talent-competition.edit', $event) }}" class="rounded-lg border border-slate-700 px-2 py-1 text-xs font-semibold text-slate-300 hover:bg-slate-800">Edit</a>
                                        <form method="POST" action="{{ route('admin.talent-competition.duplicate', $event) }}">
                                            @csrf
                                            <button type="submit" class="rounded-lg border border-slate-700 px-2 py-1 text-xs font-semibold text-slate-300 hover:bg-slate-800">Duplicate</button>
                                        </form>
                                        @unless ($event->published_to_students)
                                            <form method="POST" action="{{ route('admin.talent-competition.publish', $event) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-emerald-500/40 px-2 py-1 text-xs font-semibold text-emerald-200 hover:bg-emerald-500/10">Publish</button>
                                            </form>
                                        @endunless
                                        @unless ($event->isArchived())
                                            <form method="POST" action="{{ route('admin.talent-competition.archive', $event) }}" onsubmit="return confirm('Archive this competition?');">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-amber-500/40 px-2 py-1 text-xs font-semibold text-amber-200 hover:bg-amber-500/10">Archive</button>
                                            </form>
                                        @endunless
                                        @php
                                            $talentDeps = collect([
                                                $event->entries_count > 0 ? 'participants' : null,
                                                $event->votes_count > 0 ? 'votes' : null,
                                            ])->filter()->values();
                                            $talentWarning = $talentDeps->isNotEmpty()
                                                ? 'This talent competition contains related data: '.$talentDeps->join(', ').'. Related judges, scores, and videos may also be linked.'
                                                : null;
                                        @endphp
                                        <x-admin.delete-action
                                            :action="route('admin.talent-competition.destroy', $event)"
                                            :warning="$talentWarning"
                                            button-class="rounded-lg border border-rose-500/40 px-2 py-1 text-xs font-semibold text-rose-200 hover:bg-rose-500/10"
                                        />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-12 text-center text-slate-400">
                                No competitions found.
                                @if ($canManageTalentEvents)
                                    <a href="{{ route('admin.talent-competition.create') }}" class="ml-1 font-semibold text-violet-300 hover:text-violet-200">Create one →</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $talentEvents->links() }}</div>
    </x-admin-portal>
</x-app-layout>

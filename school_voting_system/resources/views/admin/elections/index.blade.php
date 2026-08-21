<x-app-layout>
    <x-admin-portal title="Elections" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'Elections',
            'description' => 'Set up election details, positions, and candidates.',
            'action' => route('admin.elections.create'),
            'actionLabel' => 'Create election',
            'showAction' => auth()->user()->can('create', App\Models\Election::class),
        ])

        @if ($elections->isEmpty())
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 px-4 py-8 text-center text-sm text-slate-400">
                No elections yet.
                @can('create', App\Models\Election::class)
                    <a href="{{ route('admin.elections.create') }}" class="ml-2 text-violet-300 hover:text-violet-200">Create your first election</a>
                @endcan
            </div>
        @else
            <div class="space-y-3 md:hidden">
                @foreach ($elections as $election)
                    @php
                        $dependencyParts = collect([
                            $election->categories_count > 0 ? 'positions' : null,
                            $election->candidates_count > 0 ? 'candidates' : null,
                            $election->partylists_count > 0 ? 'partylists' : null,
                            $election->votes_count > 0 ? 'votes' : null,
                        ])->filter()->values();

                        $warningParts = collect();

                        if ($election->results_locked) {
                            $warningParts->push('Official results for this election are locked.');
                        }

                        if ($dependencyParts->isNotEmpty()) {
                            $warningParts->push('This election contains related data: '.$dependencyParts->join(', ').'.');
                        }

                        $deleteWarning = $warningParts->isNotEmpty()
                            ? $warningParts->implode(' ')
                            : null;
                    @endphp
                    <article class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <h3 class="min-w-0 text-base font-semibold text-white">{{ $election->title }}</h3>
                            <x-admin-status-badge :status="$election->status?->value ?? 'draft'" :label="$election->status?->label()" />
                        </div>
                        <dl class="mt-3 grid grid-cols-2 gap-2 text-sm text-slate-300">
                            <div>
                                <dt class="text-[10px] uppercase tracking-wide text-slate-500">Positions</dt>
                                <dd class="font-semibold text-white">{{ $election->categories_count }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] uppercase tracking-wide text-slate-500">Candidates</dt>
                                <dd class="font-semibold text-white">{{ $election->candidates_count }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] uppercase tracking-wide text-slate-500">Campaigns</dt>
                                <dd class="font-semibold text-white">{{ $election->partylists_count }}</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] uppercase tracking-wide text-slate-500">Votes</dt>
                                <dd class="font-semibold text-white">{{ $election->votes_count }}</dd>
                            </div>
                        </dl>
                        <div class="mt-4 flex flex-wrap items-center gap-3">
                            @can('update', $election)
                                <a href="{{ route('admin.elections.edit', $election) }}" class="text-sm font-semibold text-violet-300 hover:text-violet-200">Manage</a>
                            @endcan
                            @can('delete', $election)
                                <x-admin.delete-action
                                    :action="route('admin.elections.destroy', $election)"
                                    :warning="$deleteWarning"
                                />
                            @endcan
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="hidden overflow-x-auto rounded-2xl border border-violet-500/15 bg-slate-900/70 md:block">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-800 text-left text-slate-400">
                            <th class="px-4 py-3 font-medium">Title</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Positions</th>
                            <th class="px-4 py-3 font-medium">Candidates</th>
                            <th class="px-4 py-3 font-medium">Partylists</th>
                            <th class="px-4 py-3 font-medium">Votes</th>
                            <th class="px-4 py-3 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($elections as $election)
                            @php
                                $dependencyParts = collect([
                                    $election->categories_count > 0 ? 'positions' : null,
                                    $election->candidates_count > 0 ? 'candidates' : null,
                                    $election->partylists_count > 0 ? 'partylists' : null,
                                    $election->votes_count > 0 ? 'votes' : null,
                                ])->filter()->values();

                                $warningParts = collect();

                                if ($election->results_locked) {
                                    $warningParts->push('Official results for this election are locked.');
                                }

                                if ($dependencyParts->isNotEmpty()) {
                                    $warningParts->push('This election contains related data: '.$dependencyParts->join(', ').'.');
                                }

                                $deleteWarning = $warningParts->isNotEmpty()
                                    ? $warningParts->implode(' ')
                                    : null;
                            @endphp
                            <tr class="border-b border-slate-800/80 text-slate-200">
                                <td class="px-4 py-3 font-medium text-white">{{ $election->title }}</td>
                                <td class="px-4 py-3"><x-admin-status-badge :status="$election->status?->value ?? 'draft'" :label="$election->status?->label()" /></td>
                                <td class="px-4 py-3">{{ $election->categories_count }}</td>
                                <td class="px-4 py-3">{{ $election->candidates_count }}</td>
                                <td class="px-4 py-3">{{ $election->partylists_count }}</td>
                                <td class="px-4 py-3">{{ $election->votes_count }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @can('update', $election)
                                        <a href="{{ route('admin.elections.edit', $election) }}" class="text-violet-300 hover:text-violet-200">Manage</a>
                                    @endcan
                                    @can('delete', $election)
                                        <x-admin.delete-action
                                            :action="route('admin.elections.destroy', $election)"
                                            :warning="$deleteWarning"
                                        />
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        <div class="mt-6">{{ $elections->links() }}</div>
    </x-admin-portal>
</x-app-layout>

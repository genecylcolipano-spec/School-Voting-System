<x-app-layout>
    <x-admin-portal title="Events & Talent" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'Events & Talent',
            'description' => 'School events and talent competitions — separate from election voting.',
        ])

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Talent --}}
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-white">Talent Competitions</h2>
                        <p class="mt-1 text-sm text-slate-400">Debates, quiz bowls, talent shows</p>
                    </div>
                    @if ($canCreateTalent)
                        <a href="{{ route('admin.talent-competition.create') }}" class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-violet-500">Create</a>
                    @endif
                </div>

                <div class="mt-4 space-y-3">
                    @forelse ($talentEvents as $event)
                        <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-medium text-white">{{ $event->title }}</span>
                                        <x-admin-status-badge :status="$event->currentStatusKey()" :label="$event->displayStatusLabel()" />
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">{{ $event->entries_count }} entries · {{ $event->event_date?->format('M d, Y') }}</p>
                                </div>
                                <div class="flex shrink-0 flex-wrap items-center gap-2">
                                    <a href="{{ route('admin.talent-competition.edit', $event) }}" class="text-xs font-semibold text-violet-300 hover:text-violet-200">Manage</a>
                                    @if ($canCreateTalent && (auth()->user()->isSuperAdmin() || (int) $event->created_by === (int) auth()->id()))
                                        @php
                                            $talentWarning = $event->entries_count > 0
                                                ? 'This talent competition contains related data: participants. Related judges, scores, and videos may also be linked.'
                                                : null;
                                        @endphp
                                        <x-admin.delete-action
                                            :action="route('admin.talent-competition.destroy', $event)"
                                            :warning="$talentWarning"
                                            button-class="text-xs font-semibold text-rose-300 hover:text-rose-200"
                                        />
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">No talent events yet.</p>
                    @endforelse
                </div>

                <a href="{{ route('admin.talent-competition.index') }}" class="mt-4 inline-block text-sm font-semibold text-violet-300 hover:text-violet-200">View all talent events →</a>
            </section>

            {{-- School events --}}
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-white">School Events</h2>
                        <p class="mt-1 text-sm text-slate-400">Announcements, orientations, campus activities</p>
                    </div>
                    @if ($canCreateEvents)
                        <a href="{{ route('admin.events.create') }}" class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-violet-500">Create</a>
                    @endif
                </div>

                <div class="mt-4 space-y-3">
                    @forelse ($schoolEvents as $event)
                        <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-medium text-white">{{ $event->title }}</span>
                                        <x-admin-status-badge :status="$event->status?->value ?? 'scheduled'" />
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">{{ $event->event_date?->format('M d, Y') }} · {{ $event->venue }}</p>
                                </div>
                                <div class="flex shrink-0 flex-wrap items-center gap-2">
                                    @can('update', $event)
                                        <a href="{{ route('admin.events.edit', $event) }}" class="text-xs font-semibold text-violet-300 hover:text-violet-200">Manage</a>
                                    @endcan
                                    @can('delete', $event)
                                        <x-admin.delete-action
                                            :action="route('admin.events.destroy', $event)"
                                            button-class="text-xs font-semibold text-rose-300 hover:text-rose-200"
                                        />
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">No school events yet.</p>
                    @endforelse
                </div>

                <a href="{{ route('admin.events.index') }}" class="mt-4 inline-block text-sm font-semibold text-violet-300 hover:text-violet-200">View all events →</a>
            </section>
        </div>
    </x-admin-portal>
</x-app-layout>

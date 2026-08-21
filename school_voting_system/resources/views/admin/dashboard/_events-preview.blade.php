@php
    $previewEvents = collect()
        ->merge($talentEvents->map(fn ($event) => ['kind' => 'talent', 'event' => $event]))
        ->merge($schoolEvents->map(fn ($event) => ['kind' => 'school', 'event' => $event]))
        ->sortByDesc(fn ($row) => $row['event']->event_date?->timestamp ?? 0)
        ->take(5);
@endphp

<div class="flex h-full flex-col rounded-2xl border border-violet-500/15 bg-slate-900/80 p-4 shadow-sm shadow-black/20 sm:p-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
        <div>
            <h3 class="text-base font-semibold text-white">Event Management</h3>
            <p class="mt-0.5 text-xs text-slate-400">Talent competitions and school events in your scope</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.events-talent.index') }}" class="inline-flex min-h-9 flex-1 items-center justify-center rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-200 hover:bg-violet-500/10 sm:flex-none">View all</a>
            @if ($canCreateTalentEvents)
                <a href="{{ route('admin.talent-competition.create') }}" class="inline-flex min-h-9 flex-1 items-center justify-center rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-200 hover:bg-violet-500/10 sm:flex-none">Create talent</a>
            @endif
            @if ($canCreateEvents)
                <a href="{{ route('admin.events.create') }}" class="inline-flex min-h-9 flex-1 items-center justify-center rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-violet-500 sm:flex-none">Create school event</a>
            @endif
        </div>
    </div>

    @if ($previewEvents->isEmpty())
        <p id="dashboard-events-empty" class="mt-6 px-2 py-8 text-center text-sm text-slate-500">No events yet. Create a talent competition or school event to populate this table.</p>
        <div id="dashboard-events-cards" class="mt-4 hidden space-y-3 md:hidden"></div>
        <div id="dashboard-events-table" class="mt-4 hidden flex-1 overflow-x-auto">
            <table class="min-w-full text-left text-xs sm:text-sm">
                <thead class="border-b border-slate-800 text-slate-400">
                    <tr>
                        <th class="px-2 py-2 font-medium">Event</th>
                        <th class="px-2 py-2 font-medium">Category</th>
                        <th class="px-2 py-2 font-medium">Schedule</th>
                        <th class="px-2 py-2 font-medium">Status</th>
                        <th class="px-2 py-2 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="dashboard-events-tbody"></tbody>
            </table>
        </div>
    @else
        <p id="dashboard-events-empty" class="mt-6 hidden px-2 py-8 text-center text-sm text-slate-500">No events yet. Create a talent competition or school event to populate this table.</p>

        <div id="dashboard-events-cards" class="mt-4 space-y-3 md:hidden">
            @foreach ($previewEvents as $row)
                @php
                    $event = $row['event'];
                    $isTalent = $row['kind'] === 'talent';
                @endphp
                <article class="rounded-xl border border-slate-800 bg-slate-950/50 p-3">
                    <div class="flex gap-3">
                        @if ($event->image_url)
                            <img src="{{ $event->image_url }}" alt="" class="h-12 w-12 shrink-0 rounded-lg object-cover ring-1 ring-slate-700">
                        @else
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-violet-500/15 text-[10px] font-bold text-violet-300">EV</div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-white">{{ $event->title }}</p>
                            <p class="mt-0.5 text-xs text-slate-400">
                                {{ $isTalent ? ($event->type?->label() ?? 'Talent') : 'School Event' }}
                                · {{ $event->event_date?->format('M d, Y') ?? '—' }}
                            </p>
                            <div class="mt-2">
                                <x-admin-status-badge
                                    :status="$isTalent ? $event->currentStatusKey() : $event->displayStatus()->value"
                                    :label="$event->displayStatusLabel()"
                                />
                            </div>
                            @include('admin.dashboard._event-preview-actions', [
                                'event' => $event,
                                'isTalent' => $isTalent,
                                'canCreateTalentEvents' => $canCreateTalentEvents,
                            ])
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div id="dashboard-events-table" class="mt-4 hidden flex-1 overflow-x-auto md:block">
            <table class="min-w-full text-left text-xs sm:text-sm">
                <thead class="border-b border-slate-800 text-slate-400">
                    <tr>
                        <th class="px-2 py-2 font-medium">Event</th>
                        <th class="px-2 py-2 font-medium">Category</th>
                        <th class="px-2 py-2 font-medium">Schedule</th>
                        <th class="px-2 py-2 font-medium">Status</th>
                        <th class="px-2 py-2 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="dashboard-events-tbody" class="divide-y divide-slate-800/80">
                    @foreach ($previewEvents as $row)
                        @php
                            $event = $row['event'];
                            $isTalent = $row['kind'] === 'talent';
                        @endphp
                        <tr class="text-slate-200">
                            <td class="px-2 py-3">
                                <div class="flex items-center gap-2.5">
                                    @if ($event->image_url)
                                        <img src="{{ $event->image_url }}" alt="" class="h-9 w-9 shrink-0 rounded-lg object-cover ring-1 ring-slate-700">
                                    @else
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-violet-500/15 text-[10px] font-bold text-violet-300">EV</div>
                                    @endif
                                    <span class="line-clamp-1 font-medium text-white">{{ $event->title }}</span>
                                </div>
                            </td>
                            <td class="px-2 py-3 text-slate-400">
                                {{ $isTalent ? ($event->type?->label() ?? 'Talent') : 'School Event' }}
                            </td>
                            <td class="px-2 py-3 whitespace-nowrap text-slate-400">{{ $event->event_date?->format('M d, Y') ?? '—' }}</td>
                            <td class="px-2 py-3">
                                <x-admin-status-badge
                                    :status="$isTalent ? $event->currentStatusKey() : $event->displayStatus()->value"
                                    :label="$event->displayStatusLabel()"
                                />
                            </td>
                            <td class="px-2 py-3 text-right">
                                @include('admin.dashboard._event-preview-actions', [
                                    'event' => $event,
                                    'isTalent' => $isTalent,
                                    'canCreateTalentEvents' => $canCreateTalentEvents,
                                    'wrapperClass' => 'flex flex-wrap items-center justify-end gap-2',
                                ])
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@php
    $previewEvents = collect()
        ->merge($talentEvents->map(fn ($event) => ['kind' => 'talent', 'event' => $event]))
        ->merge($schoolEvents->map(fn ($event) => ['kind' => 'school', 'event' => $event]))
        ->sortByDesc(fn ($row) => $row['event']->event_date?->timestamp ?? 0)
        ->take(5);
@endphp

<div class="flex h-full flex-col rounded-2xl border border-violet-500/15 bg-slate-900/80 p-5 shadow-sm shadow-black/20">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-base font-semibold text-white">Event Management</h3>
            <p class="mt-0.5 text-xs text-slate-400">Talent competitions and school events in your scope</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.events-talent.index') }}" class="rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-200 hover:bg-violet-500/10">View all</a>
            @if ($canCreateTalentEvents)
                <a href="{{ route('admin.talent-competition.create') }}" class="rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-200 hover:bg-violet-500/10">Create talent</a>
            @endif
            @if ($canCreateEvents)
                <a href="{{ route('admin.events.create') }}" class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-violet-500">Create school event</a>
            @endif
        </div>
    </div>

    <div class="mt-4 flex-1 overflow-x-auto">
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
                @forelse ($previewEvents as $row)
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
                        <td class="px-2 py-3 text-slate-400">{{ $event->event_date?->format('M d, Y') ?? '—' }}</td>
                        <td class="px-2 py-3">
                            <x-admin-status-badge
                                :status="$isTalent ? $event->currentStatusKey() : ($event->status?->value ?? 'scheduled')"
                                :label="$isTalent ? $event->displayStatusLabel() : null"
                            />
                        </td>
                        <td class="px-2 py-3 text-right">
                            <div class="inline-flex items-center gap-3">
                                @if ($isTalent)
                                    <a href="{{ route('admin.talent-competition.edit', $event) }}" class="text-xs font-semibold text-violet-300 hover:text-violet-200">Manage</a>
                                    @if ($canCreateTalentEvents && (auth()->user()->isSuperAdmin() || (int) $event->created_by === (int) auth()->id()))
                                        <x-admin.delete-action
                                            :action="route('admin.talent-competition.destroy', $event)"
                                            button-class="text-xs font-semibold text-rose-300 hover:text-rose-200"
                                        />
                                    @endif
                                @else
                                    @can('update', $event)
                                        <a href="{{ route('admin.events.edit', $event) }}" class="text-xs font-semibold text-violet-300 hover:text-violet-200">Manage</a>
                                    @endcan
                                    @can('delete', $event)
                                        <x-admin.delete-action
                                            :action="route('admin.events.destroy', $event)"
                                            button-class="text-xs font-semibold text-rose-300 hover:text-rose-200"
                                        />
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-2 py-8 text-center text-slate-500">No events yet. Create a talent competition or school event to populate this table.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

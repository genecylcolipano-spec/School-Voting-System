@php
    $wrapperClass = $wrapperClass ?? 'mt-3 flex flex-wrap items-center gap-2';
    $isTalent = $isTalent ?? false;
    $canCreateTalentEvents = $canCreateTalentEvents ?? false;
@endphp

<div class="{{ $wrapperClass }}">
    @if ($isTalent)
        <a href="{{ route('admin.talent-competition.edit', $event) }}" class="inline-flex min-h-10 items-center rounded-lg border border-violet-500/30 px-3 py-2 text-sm font-semibold text-violet-200 hover:bg-violet-500/10 md:min-h-8 md:py-1.5 md:text-xs">Manage</a>
        @if ($canCreateTalentEvents && (auth()->user()->isSuperAdmin() || (int) $event->created_by === (int) auth()->id()))
            <x-admin.delete-action
                :action="route('admin.talent-competition.destroy', $event)"
                button-class="inline-flex min-h-10 items-center rounded-lg border border-rose-500/30 px-3 py-2 text-sm font-semibold text-rose-300 hover:bg-rose-500/10 md:min-h-8 md:py-1.5 md:text-xs"
            />
        @endif
    @else
        @can('update', $event)
            <a href="{{ route('admin.events.edit', $event) }}" class="inline-flex min-h-10 items-center rounded-lg border border-violet-500/30 px-3 py-2 text-sm font-semibold text-violet-200 hover:bg-violet-500/10 md:min-h-8 md:py-1.5 md:text-xs">Manage</a>
        @endcan
        @can('delete', $event)
            <x-admin.delete-action
                :action="route('admin.events.destroy', $event)"
                button-class="inline-flex min-h-10 items-center rounded-lg border border-rose-500/30 px-3 py-2 text-sm font-semibold text-rose-300 hover:bg-rose-500/10 md:min-h-8 md:py-1.5 md:text-xs"
            />
        @endcan
    @endif
</div>

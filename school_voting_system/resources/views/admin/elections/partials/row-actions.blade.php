<div class="flex flex-wrap items-center {{ $align ?? 'justify-end' }} gap-1.5">
    @can('view', $election)
        <a href="{{ route('admin.elections.show', $election) }}" class="rounded-lg border border-slate-700 px-2 py-1 text-xs font-semibold text-slate-300 hover:bg-slate-800">View</a>
    @endcan
    @can('update', $election)
        <a href="{{ route('admin.elections.edit', $election) }}" class="rounded-lg border border-slate-700 px-2 py-1 text-xs font-semibold text-slate-300 hover:bg-slate-800">Edit</a>
    @endcan
    @if ($canCreateElections ?? false)
        <form method="POST" action="{{ route('admin.elections.duplicate', $election) }}">
            @csrf
            <button type="submit" class="rounded-lg border border-slate-700 px-2 py-1 text-xs font-semibold text-slate-300 hover:bg-slate-800">Duplicate</button>
        </form>
    @endif
    @can('update', $election)
        @unless ($election->status === \App\Enums\ElectionStatus::Archived)
            <form method="POST" action="{{ route('admin.elections.archive', $election) }}" onsubmit="return confirm('Archive this election? Students will no longer see it as an open vote.');">
                @csrf
                <button type="submit" class="rounded-lg border border-amber-500/40 px-2 py-1 text-xs font-semibold text-amber-200 hover:bg-amber-500/10">Archive</button>
            </form>
        @endunless
    @endcan
    @can('delete', $election)
        <x-admin.delete-action
            :action="route('admin.elections.destroy', $election)"
            :warning="$deleteWarning"
            button-class="rounded-lg border border-rose-500/40 px-2 py-1 text-xs font-semibold text-rose-200 hover:bg-rose-500/10"
        />
    @endcan
</div>

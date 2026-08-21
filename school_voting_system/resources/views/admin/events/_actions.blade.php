@props(['event'])

<div class="flex flex-wrap gap-2">
    @can('update', $event)
        <a href="{{ route('admin.events.edit', $event) }}"
            class="inline-flex min-h-10 items-center justify-center rounded-lg border border-violet-500/30 px-3 py-2 text-sm font-semibold text-violet-200 hover:bg-violet-500/10">
            Manage
        </a>
    @endcan
    @can('delete', $event)
        <x-admin.delete-action
            :action="route('admin.events.destroy', $event)"
            button-class="inline-flex min-h-10 items-center justify-center rounded-lg border border-rose-500/30 px-3 py-2 text-sm font-semibold text-rose-300 hover:bg-rose-500/10"
        />
    @endcan
</div>

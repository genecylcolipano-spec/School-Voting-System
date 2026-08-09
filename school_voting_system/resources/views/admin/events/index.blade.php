<x-app-layout>
    <x-admin-portal title="Events" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'Events',
            'action' => route('admin.events.create'),
            'actionLabel' => 'Create event',
            'showAction' => auth()->user()->can('create', App\Models\Event::class),
        ])

        <div class="overflow-x-auto rounded-2xl border border-violet-500/15 bg-slate-900/70">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800 text-left text-slate-400">
                        <th class="px-4 py-3 font-medium">Title</th>
                        <th class="px-4 py-3 font-medium">Date</th>
                        <th class="px-4 py-3 font-medium">Venue</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($events as $event)
                        <tr class="border-b border-slate-800/80 text-slate-200">
                            <td class="px-4 py-3">{{ $event->title }}</td>
                            <td class="px-4 py-3">{{ optional($event->event_date)->format('M d, Y g:i A') }}</td>
                            <td class="px-4 py-3">{{ $event->venue }}</td>
                            <td class="px-4 py-3">{{ $event->status?->value }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @can('update', $event)
                                    <a href="{{ route('admin.events.edit', $event) }}" class="text-violet-300 hover:text-violet-200">Manage</a>
                                @endcan
                                @can('delete', $event)
                                    <x-admin.delete-action :action="route('admin.events.destroy', $event)" />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-slate-400">No events yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $events->links() }}</div>
    </x-admin-portal>
</x-app-layout>

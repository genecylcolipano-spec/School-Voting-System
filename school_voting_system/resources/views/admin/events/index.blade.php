<x-app-layout>
    <x-admin-portal title="Events" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'Events',
            'action' => route('admin.events.create'),
            'actionLabel' => 'Create event',
            'showAction' => auth()->user()->can('create', App\Models\Event::class),
        ])

        @if ($events->isEmpty())
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 px-4 py-8 text-center text-sm text-slate-400">
                No events yet.
            </div>
        @else
            <div class="space-y-3 md:hidden">
                @foreach ($events as $event)
                    <article class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <h3 class="min-w-0 text-base font-semibold text-white">{{ $event->title }}</h3>
                            <x-admin-status-badge :status="$event->displayStatus()->value" :label="$event->displayStatusLabel()" />
                        </div>
                        <p class="mt-2 text-sm text-slate-300">{{ optional($event->event_date)->format('M d, Y · g:i A') }}</p>
                        <p class="mt-1 text-sm text-slate-400">{{ $event->venue }}</p>
                        <div class="mt-4">
                            @include('admin.events._actions', ['event' => $event])
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="hidden overflow-x-auto rounded-2xl border border-violet-500/15 bg-slate-900/70 md:block">
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
                        @foreach ($events as $event)
                            <tr class="border-b border-slate-800/80 text-slate-200">
                                <td class="px-4 py-3">{{ $event->title }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ optional($event->event_date)->format('M d, Y g:i A') }}</td>
                                <td class="px-4 py-3">{{ $event->venue }}</td>
                                <td class="px-4 py-3">
                                    <x-admin-status-badge :status="$event->displayStatus()->value" :label="$event->displayStatusLabel()" />
                                </td>
                                <td class="px-4 py-3">
                                    @include('admin.events._actions', ['event' => $event])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="mt-6 overflow-x-auto">{{ $events->links() }}</div>
    </x-admin-portal>
</x-app-layout>

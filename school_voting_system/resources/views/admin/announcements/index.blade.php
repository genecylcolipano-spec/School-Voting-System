<x-app-layout>
    <x-admin-portal title="Announcements" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'Communication Center',
            'action' => route('admin.announcements.create'),
            'actionLabel' => 'New announcement',
            'showAction' => auth()->user()->can('create', App\Models\Announcement::class),
        ])

        <div class="space-y-4">
            @forelse ($announcements as $announcement)
                <article class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                @if ($announcement->is_pinned)
                                    <span class="text-xs text-amber-300">📌</span>
                                @endif
                                @if ($announcement->is_auto_generated)
                                    <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-200">Auto</span>
                                @endif
                                @if ($announcement->category)
                                    <span class="rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide {{ $announcement->category->badgeClasses() }}">{{ $announcement->category->label() }}</span>
                                @endif
                                @if ($announcement->priority)
                                    <span class="rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide {{ $announcement->priority->badgeClasses() }}">{{ $announcement->priority->label() }}</span>
                                @endif
                            </div>
                            <h3 class="mt-2 text-lg font-semibold text-white">{{ $announcement->title }}</h3>
                            <p class="mt-1 line-clamp-2 text-sm text-slate-400">{{ $announcement->summary }}</p>
                        </div>
                        <div class="text-right text-xs text-slate-500">
                            <p class="font-semibold text-slate-300">{{ $announcement->displayStatusLabel() }}</p>
                            <p class="mt-1">{{ optional($announcement->published_at)->format('M d, Y g:i A') ?? 'Not scheduled' }}</p>
                            <p class="mt-1">{{ $announcement->author?->name ?? 'System' }}</p>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-3">
                        @can('update', $announcement)
                            <a href="{{ route('admin.announcements.edit', $announcement) }}" class="text-sm font-semibold text-violet-300 hover:text-violet-200">Edit</a>
                            <a href="{{ route('admin.announcements.preview', $announcement) }}" target="_blank" rel="noopener noreferrer" class="text-sm font-semibold text-cyan-300 hover:text-cyan-200">Preview</a>
                        @endcan
                        @can('delete', $announcement)
                            <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" onsubmit="return confirm('Delete announcement?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-sm text-rose-300 hover:text-rose-200">Delete</button>
                            </form>
                        @endcan
                    </div>
                </article>
            @empty
                <p class="text-slate-400">No announcements yet.</p>
            @endforelse
        </div>
        <div class="mt-6">{{ $announcements->links() }}</div>
    </x-admin-portal>
</x-app-layout>

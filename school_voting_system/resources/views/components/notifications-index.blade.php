@props([
    'notifications',
    'filters' => [],
    'indexRoute' => '#',
    'theme' => 'admin',
    'markAllRoute' => null,
    'markOneRouteName' => null,
    'deleteRouteName' => null,
])

@php
    $isAdmin = $theme === 'admin';
    $isFaculty = $theme === 'faculty';
    $cardBorder = match (true) {
        $isAdmin => 'border-violet-500/15',
        $isFaculty => 'border-teal-500/15',
        default => 'border-cyan-500/15',
    };
    $accent = match (true) {
        $isAdmin => 'text-violet-300',
        $isFaculty => 'text-teal-300',
        default => 'text-cyan-300',
    };
    $btnClass = match (true) {
        $isAdmin => 'rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-500',
        $isFaculty => 'rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-500',
        default => 'rounded-lg bg-cyan-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:opacity-90',
    };
@endphp

<form method="GET" action="{{ $indexRoute }}" class="mb-6 grid gap-3 rounded-2xl border {{ $cardBorder }} bg-slate-900/70 p-4 md:grid-cols-5">
    <div class="md:col-span-2">
        <label class="text-[10px] uppercase text-slate-500">Search</label>
        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search notifications…" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white">
    </div>
    <div>
        <label class="text-[10px] uppercase text-slate-500">Status</label>
        <select name="status" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white">
            <option value="">All</option>
            <option value="unread" @selected(($filters['status'] ?? '') === 'unread')>Unread</option>
            <option value="read" @selected(($filters['status'] ?? '') === 'read')>Read</option>
        </select>
    </div>
    <div>
        <label class="text-[10px] uppercase text-slate-500">Period</label>
        <select name="period" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white">
            <option value="">Any time</option>
            <option value="today" @selected(($filters['period'] ?? '') === 'today')>Today</option>
            <option value="week" @selected(($filters['period'] ?? '') === 'week')>This Week</option>
            <option value="month" @selected(($filters['period'] ?? '') === 'month')>This Month</option>
        </select>
    </div>
    <div class="flex items-end gap-2">
        <button type="submit" class="{{ $btnClass }}">Apply</button>
        @if (($filters['search'] ?? '') || ($filters['status'] ?? '') || ($filters['period'] ?? ''))
            <a href="{{ $indexRoute }}" class="rounded-lg border border-slate-700 px-4 py-2 text-sm text-slate-300 hover:text-white">Clear</a>
        @endif
    </div>
</form>

@if ($markAllRoute)
    <div class="mb-4 flex justify-end">
        <form method="POST" action="{{ $markAllRoute }}">
            @csrf
            <button type="submit" class="rounded-lg border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-slate-800">Mark all as read</button>
        </form>
    </div>
@endif

<div class="space-y-3">
    @forelse ($notifications as $entry)
        @php
            $notification = is_array($entry) ? $entry['model'] : $entry;
            $icon = is_array($entry) ? ($entry['icon'] ?? '📌') : '📌';
            $url = is_array($entry) ? ($entry['url'] ?? null) : null;
            $isUnread = $notification->read_at === null;
        @endphp
        <article class="rounded-2xl border {{ $cardBorder }} p-4 transition hover:border-slate-600 {{ $isUnread ? 'bg-slate-900/90 ring-1 ring-sky-500/20' : 'bg-slate-900/50 opacity-80' }}">
            <div class="flex items-start gap-4">
                <span class="text-2xl" aria-hidden="true">{{ $icon }}</span>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        @if ($url)
                            <a href="{{ $url }}" class="text-sm font-semibold text-white hover:underline">{{ $notification->title }}</a>
                        @else
                            <p class="text-sm font-semibold text-white">{{ $notification->title }}</p>
                        @endif
                        @if ($isUnread)
                            <span class="inline-flex items-center gap-1 rounded-full bg-sky-500/15 px-2 py-0.5 text-[10px] font-bold uppercase text-sky-300">
                                <span class="h-1.5 w-1.5 rounded-full bg-sky-400"></span> Unread
                            </span>
                        @else
                            <span class="text-[10px] uppercase text-slate-500">Read</span>
                        @endif
                    </div>
                    <p class="mt-1 text-sm text-slate-300">{{ $notification->message }}</p>
                    <p class="mt-2 text-xs text-slate-500">
                        {{ $notification->created_at?->diffForHumans() }}
                        · {{ $notification->created_at?->format('M d, Y g:i A') }}
                        @if ($notification->module)
                            · {{ $notification->module->label() }}
                        @endif
                    </p>
                </div>
                <div class="flex shrink-0 flex-col items-end gap-2">
                    @if ($isUnread && $markOneRouteName)
                        <form method="POST" action="{{ route($markOneRouteName, $notification) }}">
                            @csrf
                            <button type="submit" class="text-xs font-semibold {{ $accent }} hover:opacity-80">Mark read</button>
                        </form>
                    @endif
                    @if ($deleteRouteName)
                        <form method="POST" action="{{ route($deleteRouteName, $notification) }}"
                            onsubmit="return confirm('Delete this notification?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-rose-300 hover:text-rose-200">Delete</button>
                        </form>
                    @endif
                </div>
            </div>
        </article>
    @empty
        <div class="rounded-2xl border border-dashed border-slate-700 px-6 py-12 text-center text-sm text-slate-400">
            No notifications yet.
        </div>
    @endforelse
</div>

<div class="mt-6">{{ $notifications->links() }}</div>

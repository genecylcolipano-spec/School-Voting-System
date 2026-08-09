@php
    $f = $filters ?? [];
    $chipClass = 'inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold transition';
    $chipOn = 'border-violet-400/40 bg-violet-500/15 text-violet-100';
    $chipOff = 'border-slate-700 bg-slate-950/60 text-slate-400 hover:border-slate-600 hover:text-slate-200';
    $isSuper = (bool) ($isSuperAdmin ?? false);
@endphp

<div class="sticky top-0 z-20 -mx-1 mb-5 space-y-3 bg-slate-950/90 px-1 py-3 backdrop-blur-md" data-live-toolbar>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.live.election', request()->except('status')) }}"
           class="rounded-full px-4 py-1.5 text-sm font-semibold transition {{ ($mode ?? '') === 'election' ? 'bg-gradient-to-r from-violet-600 to-indigo-500 text-white' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
            Elections
        </a>
        <a href="{{ route('admin.live.talent', request()->except('status')) }}"
           class="rounded-full px-4 py-1.5 text-sm font-semibold transition {{ ($mode ?? '') === 'talent' ? 'bg-gradient-to-r from-violet-600 to-indigo-500 text-white' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
            Talent Competitions
        </a>

        <div class="ml-auto flex flex-wrap items-center gap-x-3 gap-y-2">
            <div class="inline-flex rounded-xl border border-slate-700 bg-slate-950/70 p-0.5" data-view-toggle>
                <button type="button" data-view="cards" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-white bg-slate-800">Cards</button>
                <button type="button" data-view="list" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-400">List</button>
            </div>

            <button type="button" class="rounded-xl border border-slate-700 px-4 py-1.5 text-sm font-semibold text-slate-300 hover:bg-slate-800" data-live-refresh>
                Refresh
            </button>

            <div class="flex flex-col items-end gap-0.5 text-right" data-system-status>
                <span class="flex items-center gap-2 text-xs font-semibold text-emerald-300" data-live-sync-indicator>
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                    </span>
                    Auto-sync · 5s
                </span>
                <span class="text-[10px] text-slate-500">
                    <span data-live-updated-at>Last updated —</span>
                    <span class="mx-1 text-slate-700">·</span>
                    <span class="text-emerald-400/90" data-system-online>Online</span>
                </span>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ url()->current() }}" class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4" data-live-filters>
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                {{ $isSuper ? 'Institution filters' : 'Filter your activities' }}
            </p>
        </div>

        <div class="grid gap-3 {{ $isSuper ? 'sm:grid-cols-2 lg:grid-cols-4' : 'sm:grid-cols-2 lg:grid-cols-3' }}">
            @if ($isSuper)
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Administrator
                    <select name="administrator" class="mt-1.5 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-200">
                        <option value="">All administrators</option>
                        @foreach ($administrators as $admin)
                            <option value="{{ $admin->id }}" @selected((int) ($f['administrator'] ?? 0) === (int) $admin->id)>
                                {{ $admin->name }}
                            </option>
                        @endforeach
                    </select>
                </label>
            @endif

            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">
                School Year
                <select name="school_year" class="mt-1.5 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-200">
                    <option value="">All years</option>
                    @foreach ($schoolYears as $year)
                        <option value="{{ $year }}" @selected((string) ($f['school_year'] ?? '') === (string) $year)>{{ $year }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">
                Status
                <select name="status" class="mt-1.5 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-200">
                    <option value="">All statuses</option>
                    @foreach ($statusOptions as $opt)
                        <option value="{{ $opt['value'] }}" @selected(($f['status'] ?? '') === $opt['value'])>{{ $opt['label'] }}</option>
                    @endforeach
                </select>
            </label>

            <div class="flex flex-wrap items-end gap-2">
                <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white hover:opacity-90">Apply</button>
                <a href="{{ url()->current() }}" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Clear</a>
            </div>
        </div>

        <div class="mt-3.5 flex flex-wrap gap-2">
            <label class="{{ $chipClass }} {{ ! empty($f['active_only']) ? $chipOn : $chipOff }} cursor-pointer">
                <input type="checkbox" name="active_only" value="1" class="sr-only" @checked(! empty($f['active_only'])) onchange="this.form.submit()">
                Active Only
            </label>
            <label class="{{ $chipClass }} {{ ! empty($f['published']) ? $chipOn : $chipOff }} cursor-pointer">
                <input type="checkbox" name="published" value="1" class="sr-only" @checked(! empty($f['published'])) onchange="this.form.submit()">
                Published
            </label>
            <label class="{{ $chipClass }} {{ ! empty($f['results_pending']) ? $chipOn : $chipOff }} cursor-pointer">
                <input type="checkbox" name="results_pending" value="1" class="sr-only" @checked(! empty($f['results_pending'])) onchange="this.form.submit()">
                Results Pending
            </label>
        </div>
    </form>
</div>

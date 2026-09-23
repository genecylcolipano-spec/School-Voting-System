<x-app-layout>
    <x-admin-portal title="Yearly Student Roster Sync" :user="$user" :notifications-count="$notificationsCount">
        <div class="mb-4">
            <a href="{{ route($routePrefix.'.index') }}" class="text-sm font-semibold text-violet-300 hover:text-violet-200">&larr; Back to student roster</a>
        </div>

        @include('admin.partials.page-header', [
            'title' => 'Yearly student roster sync',
            'description' => 'Upload next year’s official list. Returning students keep the same Student ID and passkey. Missing students can be archived so they cannot sign in.',
            'showAction' => false,
        ])

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6 lg:col-span-2">
                <h2 class="text-lg font-semibold text-white">Upload next school year CSV</h2>
                <p class="mt-1 text-sm text-slate-400">
                    Use the same columns as the regular roster import: account_id, first_name, last_name, grade_level, section.
                    This does not create login accounts.
                </p>

                <form method="POST" action="{{ route($routePrefix.'.year-sync.preview') }}" enctype="multipart/form-data" class="mt-6 space-y-5">
                    @csrf
                    <div>
                        <label for="school_year" class="block text-sm font-medium text-slate-300">School year</label>
                        <input id="school_year" name="school_year" value="{{ old('school_year', $suggestedSchoolYear) }}" required
                            class="mt-2 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white" placeholder="2026-2027">
                        <p class="mt-1 text-xs text-slate-500">Format 2026-2027. This is stamped on every returning and new roster row.</p>
                        @error('school_year')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="csv_file" class="block text-sm font-medium text-slate-300">CSV file</label>
                        <input id="csv_file" name="csv_file" type="file" accept=".csv,text/csv,text/plain" required
                            class="mt-2 block w-full cursor-pointer rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-sm text-slate-200 file:mr-4 file:rounded-lg file:border-0 file:bg-violet-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-violet-500">
                        @error('csv_file')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
                    <label class="flex items-start gap-3 rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3 text-sm text-slate-300">
                        <input type="checkbox" name="archive_missing" value="1" class="mt-1 rounded border-slate-600 bg-slate-900 text-violet-500" @checked(old('archive_missing', true))>
                        <span>
                            <span class="font-medium text-white">Archive students missing from this file</span>
                            <span class="mt-1 block text-slate-500">Use this at year-end for graduates and transfers. Their portal login is deactivated. Vote history is kept. Uncheck if this file is only a partial section update.</span>
                        </span>
                    </label>
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90">Preview changes</button>
                        <a href="{{ route($routePrefix.'.import.template') }}" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm font-semibold text-slate-300 hover:bg-slate-800">Download template</a>
                    </div>
                </form>
            </section>

            <aside class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6 text-sm text-slate-400">
                <h2 class="text-lg font-semibold text-white">What this does</h2>
                <ul class="mt-3 list-disc space-y-2 pl-5">
                    <li>Same Student ID keeps the same account and passkey.</li>
                    <li>Grade and section are updated on the roster and the login record.</li>
                    <li>New IDs are added to the roster only. They still register once.</li>
                    <li>Regular Import CSV still refuses to overwrite registered rows. Use this page for year-end updates.</li>
                </ul>
            </aside>
        </div>

        @if ($preview)
            <section class="mt-8 space-y-4">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-white">Preview · SY {{ $preview['school_year'] }}</h2>
                        <p class="mt-1 text-sm text-slate-400">Review the counts, then apply. Nothing is saved until you confirm.</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <form method="POST" action="{{ route($routePrefix.'.year-sync.cancel') }}">
                            @csrf
                            <button type="submit" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Clear preview</button>
                        </form>
                        <form method="POST" action="{{ route($routePrefix.'.year-sync.apply') }}" onsubmit="return confirm('Apply this school year sync? Missing students will be archived if that option is on.');">
                            @csrf
                            <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90">Apply sync</button>
                        </form>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                    @foreach ([
                        ['Add to roster', $preview['counts']['created']],
                        ['Update class', $preview['counts']['updated']],
                        ['Restore', $preview['counts']['restored']],
                        ['Unchanged', $preview['counts']['unchanged']],
                        ['Archive', $preview['counts']['archived']],
                    ] as [$label, $value])
                        <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</p>
                            <p class="mt-1 text-2xl font-bold text-white">{{ number_format($value) }}</p>
                        </div>
                    @endforeach
                </div>

                @foreach ([
                    ['updated', 'Class updates (sample)'],
                    ['created', 'New roster rows (sample)'],
                    ['restored', 'Returning archived students (sample)'],
                    ['archived', 'Will be archived (sample)'],
                ] as [$key, $title])
                    @if (($preview['counts'][$key] ?? 0) > 0)
                        <div class="overflow-x-auto rounded-2xl border border-violet-500/15 bg-slate-900/70">
                            <div class="border-b border-slate-800 px-4 py-3">
                                <h3 class="text-sm font-semibold text-white">{{ $title }}</h3>
                            </div>
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-800 text-left text-slate-400">
                                        <th class="px-4 py-2 font-medium">Student ID</th>
                                        <th class="px-4 py-2 font-medium">Name</th>
                                        <th class="px-4 py-2 font-medium">From</th>
                                        <th class="px-4 py-2 font-medium">To</th>
                                        <th class="px-4 py-2 font-medium">Login</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($preview[$key] as $row)
                                        <tr class="border-b border-slate-800/80 text-slate-200">
                                            <td class="px-4 py-2 font-mono text-xs">{{ $row['account_id'] }}</td>
                                            <td class="px-4 py-2">{{ $row['name'] }}</td>
                                            <td class="px-4 py-2 text-slate-400">{{ $row['from'] }}</td>
                                            <td class="px-4 py-2">{{ $row['to'] }}</td>
                                            <td class="px-4 py-2">{{ $row['has_account'] ? 'Keep account' : 'Roster only' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @endforeach
            </section>
        @endif
    </x-admin-portal>
</x-app-layout>

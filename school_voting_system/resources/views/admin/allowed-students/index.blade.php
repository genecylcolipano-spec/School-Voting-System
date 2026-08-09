<x-app-layout>
    <x-admin-portal title="Imported Student Roster" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'Imported Student Roster',
            'description' => 'Official allowed_students list used for student registration verification.',
            'showAction' => false,
        ])

        <div class="mb-6 flex flex-wrap justify-end gap-3">
            <a href="{{ route('super-admin.allowed-students.export') }}" class="rounded-xl border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Export CSV</a>
            <a href="{{ route('super-admin.allowed-students.import') }}" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white hover:opacity-90">Import CSV</a>
        </div>

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

        @php($importResult = session('import_result'))
        @if ($importResult && ! empty($importResult['errors']))
            <div class="mb-6 overflow-x-auto rounded-2xl border border-rose-500/20 bg-rose-500/5">
                <div class="border-b border-rose-500/20 px-4 py-3">
                    <h3 class="text-sm font-semibold text-rose-200">Rows with errors</h3>
                </div>
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-rose-500/10 text-left text-slate-400">
                            <th class="px-4 py-2 font-medium">Row</th>
                            <th class="px-4 py-2 font-medium">Account ID</th>
                            <th class="px-4 py-2 font-medium">Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($importResult['errors'] as $error)
                            <tr class="border-b border-rose-500/10 text-slate-200">
                                <td class="px-4 py-2">{{ $error['row'] }}</td>
                                <td class="px-4 py-2 font-mono text-xs">{{ $error['account_id'] }}</td>
                                <td class="px-4 py-2 text-rose-200">{{ $error['message'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="mb-6 grid gap-3 sm:grid-cols-3">
            @foreach ([
                ['Total Records', $summary['total']],
                ['Registered', $summary['registered']],
                ['Pending Registration', $summary['pending']],
            ] as [$label, $value])
                <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</p>
                    <p class="mt-1 text-2xl font-bold text-white">{{ number_format($value) }}</p>
                </div>
            @endforeach
        </div>

        <form method="GET" class="mb-6 flex flex-wrap gap-3">
            <input name="q" type="search" value="{{ request('q') }}" placeholder="Search account ID or name"
                class="min-w-[16rem] flex-1 rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100" />
            <select name="status" class="rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-sm text-slate-100">
                <option value="">All registration statuses</option>
                <option value="registered" @selected($statusFilter === 'registered')>Registered</option>
                <option value="pending" @selected($statusFilter === 'pending')>Pending</option>
            </select>
            <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white">Search</button>
        </form>

        <div class="overflow-x-auto rounded-2xl border border-violet-500/15 bg-slate-900/70">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800 text-left text-slate-400">
                        <th class="px-4 py-3 font-medium">Account ID</th>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Grade</th>
                        <th class="px-4 py-3 font-medium">Section</th>
                        <th class="px-4 py-3 font-medium">Registration</th>
                        <th class="px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        <tr class="border-b border-slate-800/80 text-slate-200">
                            <td class="px-4 py-3 font-mono text-xs">{{ $record->account_id }}</td>
                            <td class="px-4 py-3">{{ $record->first_name }} {{ $record->last_name }}</td>
                            <td class="px-4 py-3">{{ $record->grade_level ?: '—' }}</td>
                            <td class="px-4 py-3">{{ $record->section ?: '—' }}</td>
                            <td class="px-4 py-3">
                                @if ($record->is_registered)
                                    <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-xs font-semibold text-emerald-200">Registered</span>
                                @else
                                    <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-2 py-0.5 text-xs font-semibold text-amber-200">Pending</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('super-admin.allowed-students.edit', $record) }}" class="rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-300 hover:bg-violet-500/10">Edit</a>
                                    <form method="POST" action="{{ route('super-admin.allowed-students.destroy', $record) }}"
                                        onsubmit="return confirm('{{ $record->is_registered ? 'This row is linked to a registered student. Remove roster row only (student account kept)?' : 'Remove this roster record?' }}');">
                                        @csrf
                                        @method('DELETE')
                                        @if ($record->is_registered)
                                            <input type="hidden" name="confirm_linked" value="1">
                                        @endif
                                        <button type="submit" class="rounded-lg border border-rose-500/30 px-3 py-1.5 text-xs font-semibold text-rose-300 hover:bg-rose-500/10">Remove</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-slate-400">No roster records yet. Import a CSV to begin.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $records->links() }}</div>
    </x-admin-portal>
</x-app-layout>

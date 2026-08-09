<x-app-layout>
    <x-admin-portal title="Import Official Roster" :user="$user" :notifications-count="$notificationsCount">
        <div class="mb-4">
            <a href="{{ route('super-admin.allowed-students.index') }}" class="text-sm font-semibold text-violet-300 hover:text-violet-200">&larr; Back to roster</a>
        </div>

        @include('admin.partials.page-header', [
            'title' => 'Import Official Student Roster',
            'description' => 'Upload the official roster CSV. Students must match this list to register with a passkey.',
            'showAction' => false,
        ])

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
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

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6 lg:col-span-2">
                <h2 class="text-lg font-semibold text-white">Upload CSV</h2>
                <p class="mt-1 text-sm text-slate-400">
                    Existing roster entries are matched by account ID and updated. Students who have already registered cannot be overwritten.
                </p>

                <form method="POST" action="{{ route('super-admin.allowed-students.import.store') }}" enctype="multipart/form-data" class="mt-6 space-y-5">
                    @csrf

                    <div>
                        <label for="csv_file" class="block text-sm font-medium text-slate-300">CSV File</label>
                        <input id="csv_file" name="csv_file" type="file" accept=".csv,text/csv,text/plain" required
                            class="mt-2 block w-full cursor-pointer rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-sm text-slate-200 file:mr-4 file:rounded-lg file:border-0 file:bg-violet-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-violet-500" />
                        @error('csv_file')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex flex-wrap gap-3 pt-2">
                        <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90">
                            Import Roster
                        </button>
                        <a href="{{ route('super-admin.dashboard') }}" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm font-semibold text-slate-300 hover:bg-slate-800">
                            Back to Portal Accounts
                        </a>
                    </div>
                </form>
            </section>

            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-white">CSV Format</h2>
                <p class="mt-2 text-sm text-slate-400">Maximum {{ number_format($maxRows) }} rows per upload. UTF-8 encoding recommended.</p>

                <div class="mt-4 space-y-3 text-sm text-slate-300">
                    <p><span class="font-medium text-white">Required columns:</span> account_id, first_name, last_name</p>
                    <p><span class="font-medium text-white">Optional columns:</span> grade_level, section</p>
                    <p class="text-slate-400">Registration matches account ID plus first and last name (case-insensitive).</p>
                </div>

                <a href="{{ route('super-admin.allowed-students.import.template') }}" class="mt-5 inline-flex rounded-xl border border-violet-500/30 px-4 py-2 text-sm font-semibold text-violet-300 hover:bg-violet-500/10">
                    Download template CSV
                </a>

                <pre class="mt-4 overflow-x-auto rounded-xl border border-slate-800 bg-slate-950/80 p-3 text-xs text-slate-400">account_id,first_name,last_name,grade_level,section
2026-00002,Maria,Santos,10,A</pre>
            </section>
        </div>
    </x-admin-portal>
</x-app-layout>

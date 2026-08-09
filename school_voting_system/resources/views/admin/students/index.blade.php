<x-app-layout>
    <x-admin-portal title="Manage Students" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'Students',
            'description' => 'Registered student system accounts only. Official institutional records live under Roster Management.',
            'showAction' => false,
        ])

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">{{ session('error') }}</div>
        @endif

        @if (session('enrollment_url'))
            <div class="mb-4 rounded-xl border border-violet-500/20 bg-slate-900/70 p-4">
                <p class="text-sm text-slate-300">Enrollment link (valid 2 hours):</p>
                <a href="{{ session('enrollment_url') }}" class="mt-2 block break-all text-sm text-violet-300 hover:text-violet-200">{{ session('enrollment_url') }}</a>
            </div>
        @endif

        <form method="GET" class="mb-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
            <input
                name="q"
                type="search"
                value="{{ request('q') }}"
                placeholder="Search by Student ID, name, or email"
                class="w-full min-w-0 flex-1 rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100 sm:min-w-[16rem]"
            />
            <select name="status" class="w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-sm text-slate-100 sm:w-auto">
                <option value="">All account statuses</option>
                <option value="active" @selected(($statusFilter ?? '') === 'active')>Active</option>
                <option value="suspended" @selected(in_array($statusFilter ?? '', ['suspended', 'inactive'], true))>Suspended</option>
                <option value="deactivated" @selected(in_array($statusFilter ?? '', ['deactivated', 'archived'], true))>Deactivated</option>
            </select>
            @if (count($gradeLevels) > 0)
                <select name="grade_level" class="w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-sm text-slate-100 sm:w-auto">
                    <option value="">All grades</option>
                    @foreach ($gradeLevels as $grade)
                        <option value="{{ $grade }}" @selected(request('grade_level') === $grade)>Grade {{ $grade }}</option>
                    @endforeach
                </select>
            @endif
            @if (count($sections) > 0)
                <select name="section" class="w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-sm text-slate-100 sm:w-auto">
                    <option value="">All sections</option>
                    @foreach ($sections as $sectionOption)
                        <option value="{{ $sectionOption }}" @selected(request('section') === $sectionOption)>Section {{ $sectionOption }}</option>
                    @endforeach
                </select>
            @endif
            <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white sm:w-auto">Search</button>
        </form>

        <div class="overflow-x-auto rounded-2xl border border-violet-500/15 bg-slate-900/70">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800 text-left text-slate-400">
                        <th class="px-4 py-3 font-medium">Student ID</th>
                        <th class="px-4 py-3 font-medium">Student Name</th>
                        <th class="px-4 py-3 font-medium">Grade</th>
                        <th class="px-4 py-3 font-medium">Section</th>
                        <th class="hidden px-4 py-3 font-medium lg:table-cell">Email Address</th>
                        <th class="px-4 py-3 font-medium">Account Status</th>
                        <th class="hidden px-4 py-3 font-medium sm:table-cell">Registered Devices</th>
                        <th class="hidden px-4 py-3 font-medium xl:table-cell">Last Login</th>
                        <th class="px-4 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        <tr class="border-b border-slate-800/80 text-slate-200">
                            <td class="px-4 py-3 font-mono text-xs">{{ $student->account_id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-white">{{ $student->name }}</div>
                                <div class="text-xs text-slate-500 lg:hidden">{{ $student->email }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @if ($student->grade_level)
                                    {{ $student->grade_level }}
                                @else
                                    <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-2 py-0.5 text-xs font-semibold text-amber-200">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $student->section ?: '—' }}</td>
                            <td class="hidden px-4 py-3 text-slate-400 lg:table-cell">{{ $student->email }}</td>
                            <td class="px-4 py-3">
                                @php($accountStatus = $student->accountStatusLabel())
                                <span @class([
                                    'rounded-full border px-2 py-0.5 text-xs font-semibold',
                                    'border-emerald-500/30 bg-emerald-500/10 text-emerald-200' => $accountStatus === 'Active',
                                    'border-amber-500/30 bg-amber-500/10 text-amber-200' => $accountStatus === 'Suspended',
                                    'border-slate-600 bg-slate-800/80 text-slate-300' => $accountStatus === 'Deactivated',
                                ])>{{ $accountStatus }}</span>
                            </td>
                            <td class="hidden px-4 py-3 sm:table-cell">{{ $student->passkeys_count }}</td>
                            <td class="hidden px-4 py-3 text-slate-400 xl:table-cell">
                                @if ($student->last_login_at)
                                    {{ \Illuminate\Support\Carbon::parse($student->last_login_at)->format('M d, Y g:i A') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <x-admin.user-action-menu :account="$student" variant="student" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-6 text-slate-400">No registered student accounts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $students->links() }}</div>
    </x-admin-portal>

    @vite(['resources/js/regular-admin-dashboard.js'])
</x-app-layout>

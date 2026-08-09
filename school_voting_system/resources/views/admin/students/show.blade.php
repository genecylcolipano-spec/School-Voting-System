<x-app-layout>
    <x-admin-portal :title="$account->name" :user="$user" :notifications-count="$notificationsCount">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('admin.students.index') }}" class="text-sm font-semibold text-violet-300 hover:text-violet-200">&larr; Back to students</a>
            <div class="flex flex-wrap gap-2">
                @can('updateStudentRecord', $account)
                    <a href="{{ route('admin.students.edit', $account) }}" class="rounded-xl border border-violet-500/30 px-4 py-2 text-sm font-semibold text-violet-300 hover:bg-violet-500/10">Edit</a>
                @endcan
                @can('issuePasskeyReset', $account)
                    <form method="POST" action="{{ route('admin.passkey.reset', $account) }}" onsubmit="return confirm('Generate a passkey reset / enrollment link?');">
                        @csrf
                        <button type="submit" class="rounded-xl border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Reset Passkey</button>
                    </form>
                @endcan
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">{{ session('error') }}</div>
        @endif
        @include('admin.partials.enrollment-link-banner')

        <section class="mb-6 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="font-mono text-xs text-slate-500">{{ $account->account_id }}</p>
                    <h2 class="mt-1 text-2xl font-bold text-white">{{ $account->name }}</h2>
                    <p class="mt-1 text-sm text-slate-400">{{ $account->email }} · {{ $account->roleLabel() }}</p>
                    <p class="mt-1 text-sm text-slate-400">
                        Grade {{ $account->grade_level ?: '—' }} · Section {{ $account->section ?: '—' }}
                    </p>
                </div>
                <div class="text-right">
                    @php($accountStatus = $account->accountStatusLabel())
                    <span @class([
                        'rounded-full border px-3 py-1 text-xs font-semibold',
                        'border-emerald-500/30 bg-emerald-500/10 text-emerald-200' => $accountStatus === 'Active',
                        'border-amber-500/30 bg-amber-500/10 text-amber-200' => $accountStatus === 'Suspended',
                        'border-slate-600 bg-slate-800 text-slate-300' => $accountStatus === 'Deactivated',
                    ])>{{ $accountStatus }}</span>
                    <p class="mt-2 text-xs text-slate-500">{{ $account->passkeys_count }} registered device(s)</p>
                </div>
            </div>
        </section>

        <section id="devices" class="mb-6 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
            <h3 class="text-lg font-semibold text-white">Registered Devices</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-800 text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-2 py-2">Name</th>
                            <th class="px-2 py-2">Status</th>
                            <th class="px-2 py-2">Last used</th>
                            <th class="px-2 py-2">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($devices as $device)
                            <tr class="border-b border-slate-800/70 text-slate-200">
                                <td class="px-2 py-2">{{ $device->device_name ?: $device->name }}</td>
                                <td class="px-2 py-2">{{ $device->status?->value ?? $device->status ?? 'active' }}</td>
                                <td class="px-2 py-2 text-slate-400">{{ optional($device->last_used_at)->format('M d, Y g:i A') ?? '—' }}</td>
                                <td class="px-2 py-2 text-slate-400">{{ optional($device->created_at)->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-2 py-6 text-center text-slate-500">No registered passkey devices.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section id="login-history" class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
            <h3 class="text-lg font-semibold text-white">Login History</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-800 text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-2 py-2">When</th>
                            <th class="px-2 py-2">Browser</th>
                            <th class="px-2 py-2">OS</th>
                            <th class="px-2 py-2">IP</th>
                            <th class="px-2 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($loginHistory as $row)
                            <tr class="border-b border-slate-800/70 text-slate-200">
                                <td class="px-2 py-2 text-slate-400">{{ optional($row['occurred_at'])->format('M d, Y g:i A') }}</td>
                                <td class="px-2 py-2">{{ $row['browser'] }}</td>
                                <td class="px-2 py-2">{{ $row['os'] }}</td>
                                <td class="px-2 py-2 font-mono text-xs">{{ $row['ip_address'] ?? '—' }}</td>
                                <td class="px-2 py-2">{{ $row['status'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-2 py-6 text-center text-slate-500">No login history available yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </x-admin-portal>
</x-app-layout>

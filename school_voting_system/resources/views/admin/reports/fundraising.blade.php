<x-app-layout>
    <x-admin-portal title="Fundraising Reports" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'Fundraising Reports',
            'description' => 'Donation summary, goal progress, and transactions across all campaigns.',
            'showAction' => false,
        ])

        <div class="mb-5 flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.reports.index') }}" class="rounded-full px-4 py-1.5 text-sm font-semibold text-slate-400 transition hover:bg-slate-800/70 hover:text-white">Election Reports</a>
            <a href="{{ route('admin.reports.talent') }}" class="rounded-full px-4 py-1.5 text-sm font-semibold text-slate-400 transition hover:bg-slate-800/70 hover:text-white">Talent Reports</a>
            <a href="{{ route('admin.reports.fundraising') }}" class="rounded-full bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-1.5 text-sm font-semibold text-white">Fundraising Reports</a>
        </div>

        <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] uppercase tracking-wide text-slate-500">Campaigns</p>
                <p class="mt-1 text-2xl font-bold text-white">{{ number_format($summary['campaigns']) }}</p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] uppercase tracking-wide text-slate-500">Total Goal</p>
                <p class="mt-1 text-2xl font-bold text-white">₱{{ number_format($summary['total_goal'], 2) }}</p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] uppercase tracking-wide text-slate-500">Total Raised</p>
                <p class="mt-1 text-2xl font-bold text-emerald-300">₱{{ number_format($summary['total_raised'], 2) }}</p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] uppercase tracking-wide text-slate-500">Donations</p>
                <p class="mt-1 text-2xl font-bold text-white">{{ number_format($summary['total_donations']) }}</p>
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-violet-500/15 bg-slate-900/70">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-800 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Campaign</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-center">Donations</th>
                        <th class="px-4 py-3 text-right">Goal</th>
                        <th class="px-4 py-3 text-right">Raised</th>
                        <th class="px-4 py-3">Progress</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse ($fundraisers as $fundraiser)
                        @php $progress = $fundraiser->progressPercent(); @endphp
                        <tr class="text-slate-300">
                            <td class="px-4 py-3 font-semibold text-white">{{ $fundraiser->title }}</td>
                            <td class="px-4 py-3 text-xs">{{ $fundraiser->displayStatusLabel() }}</td>
                            <td class="px-4 py-3 text-center">{{ number_format($fundraiser->donations_count) }}</td>
                            <td class="px-4 py-3 text-right">₱{{ number_format((float) $fundraiser->goal_amount, 2) }}</td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-300">₱{{ number_format((float) $fundraiser->amount_raised, 2) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-28 overflow-hidden rounded-full bg-slate-800">
                                        <div class="h-full rounded-full bg-gradient-to-r from-violet-500 to-emerald-500" style="width: {{ min(100, $progress) }}%"></div>
                                    </div>
                                    <span class="text-xs text-slate-400">{{ round($progress) }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">No fundraising campaigns yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin-portal>
</x-app-layout>

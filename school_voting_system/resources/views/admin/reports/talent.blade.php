<x-app-layout>
    <x-admin-portal title="Talent Competition Reports" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'Talent Competition Reports',
            'description' => 'Participants, votes, performance statistics, and winners across all competitions.',
            'showAction' => false,
        ])

        <div class="mb-5 flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.reports.index') }}" class="rounded-full px-4 py-1.5 text-sm font-semibold text-slate-400 transition hover:bg-slate-800/70 hover:text-white">Election Reports</a>
            <a href="{{ route('admin.reports.talent') }}" class="rounded-full bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-1.5 text-sm font-semibold text-white">Talent Reports</a>
            <a href="{{ route('admin.reports.fundraising') }}" class="rounded-full px-4 py-1.5 text-sm font-semibold text-slate-400 transition hover:bg-slate-800/70 hover:text-white">Fundraising Reports</a>
        </div>

        <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] uppercase tracking-wide text-slate-500">Competitions</p>
                <p class="mt-1 text-2xl font-bold text-white">{{ number_format($totals['events']) }}</p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] uppercase tracking-wide text-slate-500">Total Participants</p>
                <p class="mt-1 text-2xl font-bold text-white">{{ number_format($totals['participants']) }}</p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] uppercase tracking-wide text-slate-500">Approved</p>
                <p class="mt-1 text-2xl font-bold text-white">{{ number_format($totals['approved']) }}</p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] uppercase tracking-wide text-slate-500">Total Votes</p>
                <p class="mt-1 text-2xl font-bold text-white">{{ number_format($totals['votes']) }}</p>
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-violet-500/15 bg-slate-900/70">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-800 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Competition</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-center">Participants</th>
                        <th class="px-4 py-3 text-center">Approved</th>
                        <th class="px-4 py-3 text-center">Rejected</th>
                        <th class="px-4 py-3 text-center">Votes</th>
                        <th class="px-4 py-3">Voting Method</th>
                        <th class="px-4 py-3 text-center">Winners</th>
                        <th class="px-4 py-3 text-right">Reports</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse ($rows as $row)
                        <tr class="text-slate-300">
                            <td class="px-4 py-3 font-semibold text-white">{{ $row['name'] }}</td>
                            <td class="px-4 py-3">{{ $row['category'] }}</td>
                            <td class="px-4 py-3 text-xs">{{ $row['status'] }}</td>
                            <td class="px-4 py-3 text-center">{{ number_format($row['participants']) }}</td>
                            <td class="px-4 py-3 text-center text-emerald-300">{{ number_format($row['approved']) }}</td>
                            <td class="px-4 py-3 text-center text-rose-300">{{ number_format($row['rejected']) }}</td>
                            <td class="px-4 py-3 text-center font-bold text-white">{{ number_format($row['votes']) }}</td>
                            <td class="px-4 py-3 text-xs">{{ $row['voting_method'] }}</td>
                            <td class="px-4 py-3 text-center">{{ $row['winners'] }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2 text-xs">
                                    <a href="{{ $row['show_url'] }}" class="font-semibold text-violet-300 hover:text-violet-200">View</a>
                                    <a href="{{ $row['export_pdf'] }}" class="font-semibold text-cyan-300 hover:text-cyan-200">PDF</a>
                                    <a href="{{ $row['export_excel'] }}" class="font-semibold text-cyan-300 hover:text-cyan-200">Excel</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="px-4 py-10 text-center text-slate-400">No talent competitions in your scope yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin-portal>
</x-app-layout>

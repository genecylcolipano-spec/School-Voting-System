<x-app-layout>
    <x-admin-portal title="Reports" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'Election Reports',
            'description' => 'Election summary, turnout, winners, party performance, and exports.',
        ])

        @if ($report)
            <section class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="vm-stat-card rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Election</p>
                    <p class="mt-2 text-lg font-bold text-white">{{ $report['election_name'] }}</p>
                </div>
                <div class="vm-stat-card rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Total Votes</p>
                    <p class="mt-2 text-2xl font-bold text-white">{{ number_format($report['total_votes']) }}</p>
                </div>
                <div class="vm-stat-card rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Turnout</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-300">{{ number_format($report['turnout_percent'], 1) }}%</p>
                </div>
                <div class="vm-stat-card rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Registered Students</p>
                    <p class="mt-2 text-2xl font-bold text-white">{{ number_format($report['participants']) }}</p>
                </div>
            </section>

            @if ($exportUrls)
                <div class="mb-6 flex flex-wrap gap-2">
                    <a href="{{ $exportUrls['pdf'] }}" class="rs-export-btn">Export PDF</a>
                    <a href="{{ $exportUrls['excel'] }}" class="rs-export-btn">Export Excel</a>
                    <a href="{{ $exportUrls['print'] }}" class="rs-export-btn">Print Report</a>
                    @if ($election)
                        <a href="{{ route('admin.results.election.show', $election) }}" class="rs-export-btn">Open Results Dashboard</a>
                    @endif
                </div>
            @endif

            <x-winner-spotlight
                :spotlight="$report['winners']"
                :primary="collect($report['winners'])->first()"
                :theme="'admin'"
            />

            <div class="mt-6 grid gap-6 xl:grid-cols-2">
                <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                    <h3 class="text-lg font-semibold text-white">Winning Party</h3>
                    @if ($winningParty)
                        <p class="mt-3 text-2xl font-bold text-violet-200">{{ $winningParty['party'] ?? '—' }}</p>
                        <p class="mt-1 text-sm text-slate-400">{{ number_format($winningParty['total_votes'] ?? $winningParty['votes'] ?? 0) }} votes · {{ number_format($winningParty['percent'] ?? $winningParty['share'] ?? 0, 1) }}% share · {{ $winningParty['seats_won'] ?? 0 }} seats</p>
                    @else
                        <p class="mt-4 text-sm text-slate-400">No party performance data yet.</p>
                    @endif
                </section>

                <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                    <h3 class="text-lg font-semibold text-white">Party Performance</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($report['party_performance'] as $party)
                            <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3 text-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="font-medium text-white">{{ $party['party'] ?? '—' }}</p>
                                    <span class="font-semibold text-violet-300">{{ number_format($party['percent'] ?? $party['share'] ?? 0, 1) }}%</span>
                                </div>
                                <p class="mt-1 text-xs text-slate-400">{{ number_format($party['total_votes'] ?? $party['votes'] ?? 0) }} votes · {{ $party['seats_won'] ?? 0 }} seats won</p>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400">No party data available.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <section class="mt-6 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
                <h3 class="text-lg font-semibold text-white">Participation by Grade / Section</h3>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-800 text-left text-slate-400">
                                <th class="px-4 py-3">Grade</th>
                                <th class="px-4 py-3">Section</th>
                                <th class="px-4 py-3">Registered</th>
                                <th class="px-4 py-3">Voted</th>
                                <th class="px-4 py-3">Turnout</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @php
                                $turnoutRows = collect($report['turnout_sections'] ?? [])->isNotEmpty()
                                    ? collect($report['turnout_sections'])
                                    : collect($turnoutSections ?? []);
                            @endphp
                            @forelse ($turnoutRows as $row)
                                @php
                                    $grade = $row['grade'] ?? null;
                                    $section = $row['section'] ?? null;
                                    if (($grade === null || $section === null) && filled($row['label'] ?? null)) {
                                        $parts = array_map('trim', explode('·', (string) $row['label'], 2));
                                        $grade ??= $parts[0] ?? 'All';
                                        $section ??= $parts[1] ?? 'General';
                                    }
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 text-white">{{ $grade ?: '—' }}</td>
                                    <td class="px-4 py-3 text-white">{{ $section ?: '—' }}</td>
                                    <td class="px-4 py-3">{{ number_format($row['registered'] ?? $row['eligible'] ?? 0) }}</td>
                                    <td class="px-4 py-3">{{ number_format($row['voted'] ?? 0) }}</td>
                                    <td class="px-4 py-3 font-semibold text-emerald-300">{{ number_format($row['turnout_percent'] ?? $row['turnout'] ?? 0, 1) }}%</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">No turnout breakdown available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @else
            <div class="rounded-2xl border border-dashed border-slate-700 bg-slate-900/50 px-6 py-12 text-center">
                <p class="text-lg font-semibold text-white">No assigned election report</p>
                <p class="mt-2 text-sm text-slate-400">Assign an election to your admin account to generate election summary reports.</p>
                <a href="{{ route('admin.analytics.index') }}" class="mt-5 inline-flex rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-500">Open Analytics Dashboard</a>
            </div>
        @endif
    </x-admin-portal>
</x-app-layout>

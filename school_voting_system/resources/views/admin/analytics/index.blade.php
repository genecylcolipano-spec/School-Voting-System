<x-app-layout>
    <x-admin-portal title="Reports & Analytics" :user="$user" :notifications-count="$notificationsCount">
        <div
            id="admin-analytics-live"
            data-live-url="{{ route('admin.analytics.live') }}"
            class="hidden"
            aria-hidden="true"
        ></div>

        @include('admin.partials.page-header', [
            'title' => 'Reports & Analytics',
            'description' => 'Voting turnout, campaign engagement, event attendance, and fundraising performance.',
        ])

        <div class="mb-4 flex items-center justify-end gap-2">
            <span class="relative flex h-2 w-2">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
            </span>
            <p id="analytics-live-updated" class="text-[11px] font-medium text-slate-500">Live · syncing…</p>
        </div>

        <div class="grid gap-4 xl:grid-cols-2">
            <x-admin-chart-panel
                title="Participation Growth (Events/Voting)"
                subtitle="Monthly turnout and event participation — Jan to Jun"
                type="line"
                live-key="participation"
                :labels="$report['participation']['labels']"
                :values="$report['participation']['values']"
                :y-max="$report['participation']['yMax']"
                :y-ticks="$report['participation']['yTicks']"
                :value-suffix="$report['participation']['valueSuffix']"
                accent="#34d399"
            />

            <x-admin-chart-panel
                title="Donation/Fundraising History"
                subtitle="Monthly donation totals — Jan to Dec"
                type="bar"
                live-key="fundraising"
                :labels="$report['fundraising']['labels']"
                :values="$report['fundraising']['values']"
                :y-max="$report['fundraising']['yMax']"
                :y-ticks="$report['fundraising']['yTicks']"
                :value-prefix="$report['fundraising']['valuePrefix']"
                accent="#818cf8"
            />

            <x-admin-chart-panel
                title="Voting Turnout Trends"
                subtitle="Turnout by grade and section"
                type="horizontal-bar"
                live-key="turnout"
                :labels="$report['turnout']['labels']"
                :values="$report['turnout']['values']"
                :y-max="$report['turnout']['yMax']"
                :y-ticks="$report['turnout']['yTicks']"
                :value-suffix="$report['turnout']['valueSuffix']"
                accent="#a78bfa"
            />

            <x-admin-chart-panel
                title="Campaign Engagement Stats"
                subtitle="Engagement score by partylist campaign"
                type="bar"
                live-key="campaigns"
                :labels="$report['campaigns']['labels']"
                :values="$report['campaigns']['values']"
                :y-max="$report['campaigns']['yMax']"
                :y-ticks="$report['campaigns']['yTicks']"
                :value-suffix="$report['campaigns']['valueSuffix']"
                accent="#f472b6"
            />
        </div>

        <div class="mt-4 grid gap-4 xl:grid-cols-12">
            <div class="xl:col-span-8">
                <x-admin-chart-panel
                    title="Event Attendance History"
                    subtitle="School events scheduled and talent event participation by month"
                    type="bar"
                    live-key="events"
                    :labels="$report['events']['labels']"
                    :values="$report['events']['values']"
                    :y-max="$report['events']['yMax']"
                    :y-ticks="$report['events']['yTicks']"
                    accent="#38bdf8"
                />
            </div>

            <section class="xl:col-span-4 rounded-2xl border border-violet-500/15 bg-slate-900/80 p-5 shadow-sm shadow-black/20">
                <h3 class="text-base font-semibold text-white">Turnout Breakdown</h3>
                <p class="mt-0.5 text-xs text-slate-400">Eligible vs voted students by section</p>

                <div id="analytics-turnout-breakdown" class="mt-4 max-h-72 space-y-3 overflow-y-auto">
                    @forelse ($report['turnoutSections'] as $section)
                        <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3">
                            <div class="flex items-center justify-between gap-2">
                                <p class="truncate text-sm font-medium text-white">{{ $section['label'] }}</p>
                                <span class="shrink-0 text-sm font-semibold text-violet-300">{{ $section['turnout'] }}%</span>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">{{ $section['voted'] }} voted · {{ $section['eligible'] }} eligible</p>
                            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-800">
                                <div class="h-full rounded-full bg-violet-500" style="width: {{ min(100, $section['turnout']) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">No turnout data for your assigned election yet.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <section class="mt-4 rounded-2xl border border-violet-500/15 bg-slate-900/80 p-5">
            <h3 class="text-base font-semibold text-white">Campaign Performance</h3>
            <p class="mt-0.5 text-xs text-slate-400">Vote share and seats won, derived from candidate votes</p>

            <div class="mt-4 space-y-3">
                @forelse ($report['campaignPerformance'] ?? [] as $campaign)
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                @if (! empty($campaign['color']))
                                    <span class="inline-block h-3 w-3 rounded-full" style="background: {{ $campaign['color'] }}"></span>
                                @endif
                                <p class="text-sm font-medium text-white">{{ $campaign['name'] }}</p>
                                @if (! empty($campaign['acronym']))
                                    <span class="text-xs text-violet-300">{{ $campaign['acronym'] }}</span>
                                @endif
                            </div>
                            <span class="shrink-0 text-sm font-semibold text-violet-300">{{ $campaign['vote_share'] }}%</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ $campaign['total_votes'] }} votes · {{ $campaign['total_candidates'] }} candidate(s) · {{ $campaign['winning_candidates'] }} winning
                        </p>
                        @if (! empty($campaign['winning_positions']))
                            <p class="mt-1 text-xs text-emerald-300">Won: {{ implode(', ', $campaign['winning_positions']) }}</p>
                        @endif
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-800">
                            <div class="h-full rounded-full bg-violet-500" style="width: {{ min(100, $campaign['vote_share']) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No campaign vote data for your assigned election yet.</p>
                @endforelse
            </div>
        </section>

        <section id="talent-competitions" class="mt-4 scroll-mt-24 rounded-2xl border border-violet-500/15 bg-slate-900/80 p-5">
            <h3 class="text-base font-semibold text-white">Talent Competition Insights</h3>
            <p class="mt-0.5 text-xs text-slate-400">Category, contestants, votes, voting method, and winner settings</p>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-2">Event</th>
                            <th class="px-3 py-2">Category</th>
                            <th class="px-3 py-2">Contestants</th>
                            <th class="px-3 py-2">Total Votes</th>
                            <th class="px-3 py-2">Voting Method</th>
                            <th class="px-3 py-2">Winners</th>
                            <th class="px-3 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse ($report['talentCompetitions'] ?? [] as $event)
                            <tr class="text-slate-300">
                                <td class="px-3 py-3 font-medium text-white">{{ $event['name'] }}</td>
                                <td class="px-3 py-3">{{ $event['talent_category'] }}</td>
                                <td class="px-3 py-3">{{ $event['contestants'] }}</td>
                                <td class="px-3 py-3">{{ number_format($event['total_votes']) }}</td>
                                <td class="px-3 py-3">{{ $event['voting_method'] }}</td>
                                <td class="px-3 py-3">{{ $event['winner_count'] }}</td>
                                <td class="px-3 py-3">{{ $event['display_status'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-6 text-center text-slate-400">No talent competition data in your scope yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mt-4 rounded-2xl border border-violet-500/15 bg-slate-900/80 p-5">
            <h3 class="text-base font-semibold text-white">Fundraising Performance</h3>
            <p class="mt-0.5 text-xs text-slate-400">Year-to-date donation summary</p>

            @php
                $ytdTotal = array_sum($report['fundraising']['values']);
                $fundraisingValues = $report['fundraising']['values'];
                $bestMonthIndex = count($fundraisingValues) > 0
                    ? (int) array_search(max($fundraisingValues), $fundraisingValues, true)
                    : 0;
                $bestMonth = $report['fundraising']['labels'][$bestMonthIndex] ?? '—';
                $bestAmount = $fundraisingValues[$bestMonthIndex] ?? 0;
                $avgMonth = count($fundraisingValues) > 0
                    ? $ytdTotal / count($fundraisingValues)
                    : 0;
            @endphp

            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">YTD Total</p>
                    <p data-live-fundraising-ytd class="mt-1 text-2xl font-bold text-white">₱{{ number_format($ytdTotal, 2) }}</p>
                </div>
                <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Best Month</p>
                    <p data-live-fundraising-best-month class="mt-1 text-2xl font-bold text-white">{{ $bestMonth }}</p>
                    <p data-live-fundraising-best-amount class="text-xs text-slate-500">₱{{ number_format($bestAmount, 2) }}</p>
                </div>
                <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Monthly Average</p>
                    <p data-live-fundraising-avg class="mt-1 text-2xl font-bold text-white">₱{{ number_format($avgMonth, 2) }}</p>
                </div>
            </div>
        </section>
    </x-admin-portal>

    @vite(['resources/js/admin-analytics-live.js'])
</x-app-layout>

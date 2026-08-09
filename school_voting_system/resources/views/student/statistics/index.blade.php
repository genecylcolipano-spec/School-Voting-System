<x-app-layout>
    <x-student-portal title="Statistics" :user="$user" :notifications-count="$notificationsCount">
        {{-- Hero --}}
        <section class="overflow-hidden rounded-2xl border border-cyan-500/20 bg-gradient-to-br from-sky-900/60 via-slate-900 to-violet-900/20 p-6 sm:p-8">
            <span class="inline-block rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-cyan-200">Analytics</span>
            <h2 class="mt-4 text-2xl font-bold text-white sm:text-3xl">Your Statistics</h2>
            <p class="mt-2 max-w-2xl text-sm text-slate-300 sm:text-base">
                Track your participation across elections, school events, talent competitions, and fundraising campaigns.
            </p>
        </section>

        {{-- 1. Participation Overview --}}
        <section>
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-400">Participation Overview</h3>
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                @foreach ([
                    ['label' => 'Votes Cast', 'value' => $overview['votes_cast']],
                    ['label' => 'Elections Joined', 'value' => $overview['elections_joined']],
                    ['label' => 'Competitions Joined', 'value' => $overview['competitions_joined']],
                    ['label' => 'Fundraisers Supported', 'value' => $overview['fundraisers_supported']],
                ] as $stat)
                    <div class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-4 sm:p-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $stat['label'] }}</p>
                        <p class="mt-1 text-2xl font-bold text-white">{{ $stat['value'] }}</p>
                        <p class="mt-1 text-xs text-slate-500">Lifetime</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- 2. Student Activity Summary + 3. Voting Analytics --}}
        <section class="grid gap-4 lg:grid-cols-3">
            <div class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-5 lg:col-span-2">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Student Activity Summary</h3>
                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Recent Vote</p>
                        <p class="mt-1 text-sm font-semibold text-white">{{ $activitySummary['recent_vote'] ?? 'No voting history yet.' }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Last Event Joined</p>
                        <p class="mt-1 text-sm font-semibold text-white">Event registration is not available yet.</p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Last Competition Joined</p>
                        <p class="mt-1 text-sm font-semibold text-white">{{ $activitySummary['last_competition'] ?? 'No competitions joined.' }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Last Donation</p>
                        <p class="mt-1 text-sm font-semibold text-white">{{ $activitySummary['last_donation'] ?? 'No donations yet.' }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Registered Devices</p>
                        <p class="mt-1 text-sm font-semibold text-white">
                            {{ $activitySummary['passkeys'] }} {{ \Illuminate\Support\Str::plural('Passkey', $activitySummary['passkeys']) }}
                        </p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Member Since</p>
                        <p class="mt-1 text-sm font-semibold text-white">{{ optional($activitySummary['member_since'])->format('M d, Y') ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-5">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Participation Analytics</h3>

                @if ($votingAnalytics['has_history'] || $votingAnalytics['has_open_elections'])
                    <div class="mt-6 flex flex-col items-center">
                        <div class="relative flex h-32 w-32 items-center justify-center">
                            <svg class="h-32 w-32 -rotate-90" viewBox="0 0 120 120" aria-hidden="true">
                                <circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="10" class="text-slate-800"/>
                                <circle
                                    cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="10"
                                    stroke-linecap="round"
                                    class="text-cyan-400"
                                    stroke-dasharray="{{ 2 * 3.14159 * 52 }}"
                                    stroke-dashoffset="{{ 2 * 3.14159 * 52 * (1 - $votingAnalytics['lifetime_percent'] / 100) }}"
                                />
                            </svg>
                            <span class="absolute text-2xl font-bold text-white">{{ $votingAnalytics['lifetime_percent'] }}%</span>
                        </div>
                        <p class="mt-4 text-center text-sm font-semibold text-cyan-300">
                            {{ $votingAnalytics['elections_joined'] }} / {{ max($votingAnalytics['eligible_elections'], 0) }} Elections Joined
                        </p>
                    </div>

                    <dl class="mt-6 space-y-3 border-t border-slate-800 pt-4 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-400">Completed Votes</dt>
                            <dd class="font-semibold text-white">{{ $votingAnalytics['completed_votes'] }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-400">Eligible Elections</dt>
                            <dd class="font-semibold text-white">{{ $votingAnalytics['eligible_elections'] }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-400">Last Vote Date</dt>
                            <dd class="font-semibold text-white">{{ optional($votingAnalytics['last_vote_at'])->format('M d, Y') ?? '—' }}</dd>
                        </div>
                        @if ($votingAnalytics['has_open_elections'])
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-slate-400">Open categories voted</dt>
                                <dd class="font-semibold text-white">{{ $votingAnalytics['votes_in_open'] }}/{{ $votingAnalytics['open_categories'] }}</dd>
                            </div>
                        @endif
                    </dl>
                @else
                    <div class="mt-8 rounded-xl border border-dashed border-slate-700 px-4 py-10 text-center">
                        <p class="text-sm font-medium text-slate-300">No voting history yet.</p>
                        <p class="mt-2 text-xs text-slate-500">Your participation rate will appear here after you cast your first vote.</p>
                    </div>
                @endif
            </div>
        </section>

        {{-- 4. Recent Activity + 5. Donation Summary --}}
        <section class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Recent Activity</h3>
                <ul class="mt-4 divide-y divide-slate-800">
                    @forelse ($recentActivity as $activity)
                        <li class="flex items-start gap-3 py-3 first:pt-0 last:pb-0">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-300" aria-hidden="true">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-slate-200">{{ $activity['message'] }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">{{ $activity['time'] }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="py-8 text-center text-sm text-slate-500">No recent activity yet.</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Donation Summary</h3>

                @if ($donationSummary['donations_made'] > 0)
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Total Donations</p>
                            <p class="mt-1 text-lg font-bold text-white">₱{{ number_format($donationSummary['total_donated'], 2) }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Campaigns Supported</p>
                            <p class="mt-1 text-lg font-bold text-white">{{ $donationSummary['campaigns_supported'] }}</p>
                        </div>
                    </div>

                    <div class="mt-4 rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Latest Donation</p>
                        <p class="mt-1 text-sm font-semibold text-white">{{ $donationSummary['latest_title'] ?? '—' }}</p>
                        <p class="mt-1 text-xs text-slate-400">
                            @if ($donationSummary['latest_amount'] !== null)
                                ₱{{ number_format($donationSummary['latest_amount'], 2) }}
                                ·
                            @endif
                            {{ optional($donationSummary['latest_at'])->format('M d, Y') ?? '—' }}
                        </p>
                    </div>

                    <div class="mt-4">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Recent Donation History</p>
                        <ul class="space-y-2">
                            @foreach ($donationSummary['history'] as $donation)
                                <li class="flex items-center justify-between gap-3 rounded-xl border border-slate-800 bg-slate-950/40 px-3 py-2.5">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-white">{{ $donation->fundraiser?->title ?? 'Fundraiser' }}</p>
                                        <p class="text-xs text-slate-500">{{ optional($donation->donated_at)->format('M d, Y') }}</p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <p class="text-sm font-semibold text-emerald-300">₱{{ number_format((float) $donation->amount, 2) }}</p>
                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Completed</p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="mt-6 rounded-xl border border-dashed border-slate-700 px-4 py-10 text-center">
                        <p class="text-sm font-medium text-slate-300">No donations yet.</p>
                        <p class="mt-2 text-xs text-slate-500">Your fundraising support history will appear here.</p>
                    </div>
                @endif
            </div>
        </section>

        {{-- 6. Engagement + 7. Achievements --}}
        <section class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Student Engagement</h3>
                <div class="mt-5 space-y-5">
                    @foreach ([
                        ['label' => 'Voting Participation', 'value' => $engagement['voting'], 'available' => true],
                        ['label' => 'Event Participation', 'value' => $engagement['events'], 'available' => false],
                        ['label' => 'Competition Participation', 'value' => $engagement['competitions'], 'available' => true],
                        ['label' => 'Fundraising Participation', 'value' => $engagement['fundraising'], 'available' => true],
                    ] as $bar)
                        <div>
                            <div class="mb-1.5 flex items-center justify-between gap-3 text-sm">
                                <span class="text-slate-300">{{ $bar['label'] }}</span>
                                @if ($bar['available'])
                                    <span class="font-semibold text-white">{{ $bar['value'] }}%</span>
                                @else
                                    <span class="text-xs text-slate-500">Not available yet</span>
                                @endif
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-800">
                                @if ($bar['available'])
                                    <div
                                        class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-sky-400 transition-all"
                                        style="width: {{ max(0, min(100, (int) $bar['value'])) }}%"
                                    ></div>
                                @else
                                    <div class="h-full w-0 rounded-full bg-slate-700"></div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Achievements</h3>
                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Most Active Semester</p>
                        <p class="mt-1 text-sm font-semibold text-white">{{ $achievements['most_active_semester'] ?? 'No activity yet' }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Total Activities</p>
                        <p class="mt-1 text-2xl font-bold text-white">{{ $achievements['total_activities'] }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Certificates Earned</p>
                        <p class="mt-1 text-sm font-semibold text-white">
                            @if ($achievements['certificates_earned'] > 0)
                                {{ $achievements['certificates_earned'] }}
                            @else
                                Not available yet
                            @endif
                        </p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Current Participation Level</p>
                        <p class="mt-1 text-sm font-semibold text-cyan-300">{{ $achievements['participation_level'] }}</p>
                    </div>
                </div>
            </div>
        </section>
    </x-student-portal>
</x-app-layout>

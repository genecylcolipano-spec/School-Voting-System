@php
    $metricCards = [
        [
            'label' => 'Voter Turnout',
            'value' => $statistics['turnout_percent'].'%',
            'href' => '#live-voting-panel',
            'tone' => 'emerald',
            'chart' => 'line',
        ],
        [
            'label' => 'Total Votes',
            'value' => number_format($statistics['votes_cast']),
            'href' => '#live-voting-panel',
            'tone' => 'violet',
            'chart' => 'bars',
        ],
        [
            'label' => 'Eligible Voters',
            'value' => number_format($statistics['eligible_voters']),
            'href' => '#live-voting-panel',
            'tone' => 'indigo',
            'chart' => 'donut',
        ],
        [
            'label' => 'Not Voted',
            'value' => number_format($voterBreakdown['notVoted']),
            'href' => '#live-voting-panel',
            'tone' => 'rose',
            'chart' => 'line',
        ],
        [
            'label' => 'Campaigns',
            'value' => number_format($statistics['partylists']),
            'href' => '#posters',
            'tone' => 'violet',
            'chart' => 'bars',
        ],
        [
            'label' => 'Candidates',
            'value' => number_format($statistics['candidates']),
            'href' => '#live-voting-panel',
            'tone' => 'emerald',
            'chart' => 'donut',
        ],
    ];

    $chartStroke = [
        'violet' => '#a78bfa',
        'emerald' => '#34d399',
        'indigo' => '#818cf8',
        'rose' => '#fb7185',
        'amber' => '#fbbf24',
    ];
    $sparklineKeys = [
        'Voter Turnout' => 'turnout_percent',
        'Total Votes' => 'votes_cast',
        'Eligible Voters' => 'eligible_voters',
        'Not Voted' => 'not_voted',
        'Campaigns' => 'partylists',
        'Candidates' => 'candidates',
    ];
@endphp

<div class="grid h-full grid-cols-2 gap-3 sm:grid-cols-3">
    @foreach ($metricCards as $card)
        @php
            $stroke = $chartStroke[$card['tone']] ?? '#a78bfa';
            $sparklineKey = $sparklineKeys[$card['label']] ?? null;
            $sparkline = $sparklineKey ? ($statSparklines[$sparklineKey] ?? null) : null;
        @endphp
        <a
            href="{{ $card['href'] }}"
            class="group flex flex-col justify-between rounded-2xl border border-violet-500/15 bg-slate-900/80 p-4 shadow-sm shadow-black/20 transition hover:border-violet-400/35 hover:bg-slate-900"
        >
            <div class="flex items-start justify-between gap-2">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ $card['label'] }}</p>
                <span class="text-[10px] text-slate-600 group-hover:text-violet-300">↗</span>
            </div>
            <p class="mt-2 text-2xl font-bold text-white" data-live-stat="{{ match($card['label']) {
                'Voter Turnout' => 'turnout_percent',
                'Total Votes' => 'votes_cast',
                'Eligible Voters' => 'eligible_voters',
                'Not Voted' => 'not_voted',
                'Campaigns' => 'partylists',
                'Candidates' => 'candidates',
                default => 'metric',
            } }}">{{ $card['value'] }}</p>
            <div class="mt-3 h-8 opacity-80">
                @if ($sparkline)
                    <x-stat-sparkline
                        :type="$sparkline['type']"
                        :values="$sparkline['values'] ?? []"
                        :percent="$sparkline['percent'] ?? 0"
                        :stroke="$stroke"
                        :live-key="$sparklineKey"
                    />
                @endif
            </div>
        </a>
    @endforeach
</div>

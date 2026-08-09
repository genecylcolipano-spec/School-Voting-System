@php
    $presentation = $presentation ?? [];
    $summary = $detail['summary'] ?? [];
    $extended = $presentation['extended_summary'] ?? [
        'registered_students' => $summary['participants'] ?? 0,
        'students_voted' => (int) round(($summary['participants'] ?? 0) * (($summary['turnout_percent'] ?? 0) / 100)),
        'total_votes' => $summary['total_votes'] ?? 0,
        'turnout_percent' => $summary['turnout_percent'] ?? 0,
        'valid_votes' => $summary['total_votes'] ?? 0,
        'invalid_votes' => 0,
        'total_winners' => $summary['winners_count'] ?? 0,
    ];
    $winningCandidates = $presentation['winning_candidates'] ?? collect($detail['winners'] ?? [])
        ->reject(fn ($w) => ($w['group'] ?? null) === 'top_ten')
        ->map(fn ($w) => [
            'name' => $w['name'] ?? '—',
            'position' => $w['label'] ?? '—',
            'party' => $w['party'] ?? 'Independent',
            'votes' => $w['votes'] ?? 0,
            'percent' => $w['percent'] ?? 0,
        ])
        ->values()
        ->all();
    $partyPerformance = $presentation['party_performance'] ?? [];
    $turnoutSections = $presentation['turnout_sections'] ?? [];
    $charts = $detail['charts'] ?? [];
    $hasChartData = $presentation['has_chart_data'] ?? collect($charts['bar']['values'] ?? [])->sum() > 0;
    $isOfficial = $presentation['is_official'] ?? false;
    $isUnofficial = ($detail['is_live'] ?? false) || ! $isOfficial;
    $reportTitle = ($detail['type'] ?? '') === 'election' ? 'OFFICIAL ELECTION RESULTS' : 'OFFICIAL EVENT RESULTS';
    $barChart = $charts['bar'] ?? ['labels' => [], 'values' => [], 'yMax' => 10];
    $pieChart = $charts['pie'] ?? ['labels' => [], 'values' => []];
    $doughnutChart = $charts['doughnut'] ?? ['labels' => [], 'values' => []];
    $barMax = max((int) ($barChart['yMax'] ?? 1), 1);

    $pieColors = ['#1d4ed8', '#2563eb', '#3b82f6', '#60a5fa', '#93c5fd', '#1e40af', '#1e3a8a', '#172554'];
    $pieStops = [];
    $cursor = 0;
    $pieTotal = max(array_sum(array_map('floatval', $pieChart['values'] ?? [])), 1);
    foreach ($pieChart['values'] ?? [] as $index => $value) {
        $slice = ((float) $value / $pieTotal) * 100;
        $next = $cursor + $slice;
        $pieStops[] = ($pieColors[$index % count($pieColors)] ?? '#64748b').' '.$cursor.'% '.$next.'%';
        $cursor = $next;
    }
    $pieGradient = $pieStops !== [] ? 'conic-gradient('.implode(', ', $pieStops).')' : '#e2e8f0';

    $seatStops = [];
    $cursor = 0;
    $seatTotal = max(collect($partyPerformance)->sum('seats_won'), 1);
    foreach ($partyPerformance as $index => $party) {
        $slice = ((int) $party['seats_won'] / $seatTotal) * 100;
        if ($slice <= 0) {
            continue;
        }
        $next = $cursor + $slice;
        $seatStops[] = ($pieColors[$index % count($pieColors)] ?? '#64748b').' '.$cursor.'% '.$next.'%';
        $cursor = $next;
    }
    $seatGradient = $seatStops !== [] ? 'conic-gradient('.implode(', ', $seatStops).')' : '#e2e8f0';

    $forPdf = (bool) ($forPdf ?? false);
    $schoolName = \App\Support\SchoolBranding::schoolName();
    $systemName = \App\Support\SchoolBranding::systemName();
    // DomPDF needs embedded data URIs / local files — never remote http assets.
    $schoolLogoSrc = \App\Support\SchoolBranding::logoDataUri() ?? '';
    if ($schoolLogoSrc === '' && is_file(public_path('images/rosemont-hills-logo.png'))) {
        $schoolLogoSrc = 'data:image/png;base64,'.base64_encode((string) file_get_contents(public_path('images/rosemont-hills-logo.png')));
    }
    $academicYearLabel = $presentation['academic_year']
        ?? \App\Support\SchoolBranding::academicYear();
    $semesterLabel = \App\Support\SchoolBranding::semester();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportTitle }} — {{ $detail['name'] ?? 'Report' }}</title>
    <style>
        /* Hex colors only — DomPDF often drops CSS variables, which made
           white table headers invisible (white text, no blue background). */
        @page {
            size: A4 portrait;
            margin: 16mm 14mm 24mm 14mm;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            color: #0f172a;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11pt;
            line-height: 1.45;
            background: #ffffff;
            counter-reset: page;
        }

        .watermark {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            z-index: 0;
            opacity: 0.06;
            font-size: 72pt;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-align: center;
            line-height: 1.15;
            color: #1e3a8a;
            transform: rotate(-28deg);
        }

        .watermark--official {
            opacity: 0.05;
            font-size: 56pt;
            color: #1e40af;
        }

        .report {
            position: relative;
            z-index: 1;
            max-width: 210mm;
            margin: 0 auto;
            padding: 0;
        }

        @font-face {
            font-family: 'Monotype Corsiva';
            font-style: normal;
            font-weight: normal;
            src: url('{{ $forPdf ? storage_path('fonts/MonotypeCorsiva.ttf') : asset('fonts/MonotypeCorsiva.ttf') }}') format('truetype');
        }

        .report-header {
            display: block;
            text-align: center;
            border-bottom: 3px solid #1e40af;
            padding-bottom: 1rem;
            margin-bottom: 1.25rem;
        }

        .logo-wrap {
            width: 90px;
            height: 90px;
            margin: 0 auto 0.65rem;
            display: block;
            text-align: center;
        }

        .logo-wrap img {
            width: 90px;
            height: 90px;
            object-fit: contain;
            object-position: center;
            display: inline-block;
        }

        .institution {
            text-align: center;
        }

        .institution .school-name {
            margin: 0;
            font-family: 'Monotype Corsiva', 'Apple Chancery', cursive;
            font-size: 18pt;
            font-weight: normal;
            letter-spacing: 0.02em;
            color: #1e3a8a;
            text-transform: none;
        }

        .institution .school-address,
        .institution .system-name {
            margin: 0.15rem 0 0;
            font-size: 9.5pt;
            color: #334155;
        }

        .title-block {
            text-align: center;
            margin: 1.25rem 0 1.5rem;
        }

        .title-block h1 {
            margin: 0;
            font-size: 20pt;
            letter-spacing: 0.06em;
            color: #1e3a8a;
            font-weight: 800;
        }

        .title-block .subtitle {
            margin: 0.35rem 0 0;
            font-size: 13pt;
            font-weight: 700;
            color: #0f172a;
        }

        .title-block .academic-year {
            margin: 0.25rem 0 0;
            font-size: 10pt;
            color: #64748b;
        }

        .section {
            margin-bottom: 1.25rem;
            break-inside: avoid-page;
        }

        .section-title {
            margin: 0 0 0.65rem;
            font-size: 11pt;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #1e3a8a;
            border-left: 4px solid #1d4ed8;
            padding-left: 0.55rem;
        }

        .info-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            background: #f8fafc;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .info-item {
            padding: 0.65rem 0.85rem;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
        }

        .info-item:nth-child(2n) { border-right: none; }
        .info-item:nth-last-child(-n+2) { border-bottom: none; }

        .info-item dt {
            margin: 0;
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            font-weight: 700;
        }

        .info-item dd {
            margin: 0.15rem 0 0;
            font-size: 10pt;
            font-weight: 600;
            color: #0f172a;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e2e8f0;
        }

        .summary-table th,
        .summary-table td {
            border: 1px solid #e2e8f0;
            padding: 0.55rem 0.75rem;
            text-align: left;
            font-size: 10pt;
            color: #0f172a;
            background: #ffffff;
        }

        .summary-table th {
            width: 42%;
            background: #dbeafe;
            color: #1e3a8a;
            font-weight: 700;
        }

        .winners-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .winner-card {
            border: 1px solid #e2e8f0;
            border-top: 3px solid #1d4ed8;
            border-radius: 8px;
            padding: 0.85rem;
            background: #fff;
            break-inside: avoid;
        }

        .winner-badge {
            display: inline-block;
            margin-bottom: 0.45rem;
            padding: 0.15rem 0.5rem;
            border-radius: 999px;
            background: #dbeafe;
            color: #1e3a8a;
            font-size: 8pt;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .winner-card .position {
            font-size: 9pt;
            font-weight: 700;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .winner-card .name {
            margin: 0.25rem 0 0;
            font-size: 12pt;
            font-weight: 800;
            color: #0f172a;
        }

        .winner-card .meta {
            margin-top: 0.45rem;
            font-size: 9.5pt;
            color: #334155;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e2e8f0;
            font-size: 9.5pt;
        }

        .data-table thead th {
            background: #1e40af;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-size: 8.5pt;
            padding: 0.55rem 0.65rem;
            border: 1px solid #1e3a8a;
        }

        .data-table tbody td {
            padding: 0.5rem 0.65rem;
            border: 1px solid #e2e8f0;
            vertical-align: top;
            color: #0f172a;
            background: #ffffff;
        }

        .data-table tbody tr:nth-child(even) td { background: #f8fafc; }
        .data-table tbody tr.is-first-place td { background: #eff6ff; font-weight: 600; }
        .data-table tbody tr.is-first-place td:first-child { color: #1e3a8a; font-weight: 800; }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.85rem;
        }

        .chart-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem;
            break-inside: avoid;
        }

        .chart-card h4 {
            margin: 0 0 0.65rem;
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #1e3a8a;
        }

        .bar-row {
            display: grid;
            grid-template-columns: 72px 1fr 36px;
            gap: 0.35rem;
            align-items: center;
            margin-bottom: 0.35rem;
            font-size: 8pt;
        }

        .bar-track {
            height: 8px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            background: #1e40af;
            border-radius: 999px;
        }

        .donut {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            margin: 0 auto;
            position: relative;
        }

        .donut::after {
            content: "";
            position: absolute;
            inset: 22%;
            background: #fff;
            border-radius: 50%;
        }

        .chart-legend {
            margin-top: 0.5rem;
            font-size: 8pt;
            color: #334155;
        }

        .chart-legend div { margin-bottom: 0.15rem; }

        .empty-note {
            padding: 1rem;
            border: 1px dashed #e2e8f0;
            border-radius: 8px;
            text-align: center;
            color: #64748b;
            font-size: 10pt;
            background: #f8fafc;
        }

        .signatures {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 2rem;
            margin-top: 2rem;
            break-inside: avoid;
        }

        .signature-block p {
            margin: 0;
            font-size: 9.5pt;
            color: #334155;
        }

        .signature-line {
            margin: 2.2rem 0 0.35rem;
            border-top: 1px solid #0f172a;
            width: 85%;
        }

        .report-footer {
            margin-top: 1.5rem;
            padding-top: 0.75rem;
            border-top: 1px solid #e2e8f0;
            font-size: 8.5pt;
            color: #64748b;
            display: flex;
            justify-content: space-between;
            gap: 1rem;
        }

        .status-pill {
            display: inline-block;
            padding: 0.1rem 0.45rem;
            border-radius: 999px;
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-pill--winner { background: #dbeafe; color: #1e40af; }

        .no-print {
            margin: 1rem auto;
            max-width: 210mm;
            text-align: right;
        }

        .no-print button {
            border: 1px solid #1d4ed8;
            background: #1e40af;
            color: #fff;
            border-radius: 8px;
            padding: 0.55rem 1rem;
            font-size: 10pt;
            cursor: pointer;
        }

        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .report { max-width: none; }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
            tr, .winner-card, .chart-card, .section { break-inside: avoid; }
            .report-footer::after {
                content: "Page " counter(page);
            }
        }
    </style>
</head>
<body>
    @if ($isUnofficial)
        <div class="watermark">UNOFFICIAL<br>FOR REVIEW ONLY</div>
    @else
        <div class="watermark watermark--official">OFFICIAL COPY</div>
    @endif

    <div class="report">
        <header class="report-header">
            <div class="logo-wrap">
                <img
                    src="{{ $schoolLogoSrc }}"
                    alt="{{ $schoolName }} logo"
                    width="80"
                    height="80"
                >
            </div>
            <div class="institution">
                <p class="school-name">{{ $schoolName }}</p>
                <p class="school-address">{{ $semesterLabel }}</p>
                <p class="system-name">{{ $systemName }}</p>
            </div>
        </header>

        <div class="title-block">
            <h1>{{ $reportTitle }}</h1>
            <p class="subtitle">{{ $detail['name'] ?? 'Election Report' }}</p>
            <p class="academic-year">Academic Year {{ $academicYearLabel }}</p>
        </div>

        <section class="section">
            <h2 class="section-title">Election Information</h2>
            <div class="info-card">
                <dl class="info-grid">
                    <div class="info-item">
                        <dt>Election Name</dt>
                        <dd>{{ $detail['name'] ?? '—' }}</dd>
                    </div>
                    <div class="info-item">
                        <dt>Election Category</dt>
                        <dd>{{ $detail['category'] ?? '—' }}</dd>
                    </div>
                    <div class="info-item">
                        <dt>Election Status</dt>
                        <dd>{{ $detail['voting_status'] ?? '—' }}</dd>
                    </div>
                    <div class="info-item">
                        <dt>Voting Start</dt>
                        <dd>{{ $detail['starts_at'] ?? '—' }}</dd>
                    </div>
                    <div class="info-item">
                        <dt>Voting End</dt>
                        <dd>{{ $detail['ends_at'] ?? '—' }}</dd>
                    </div>
                    <div class="info-item">
                        <dt>Date Generated</dt>
                        <dd>{{ $generatedAt }}</dd>
                    </div>
                    <div class="info-item">
                        <dt>Generated By</dt>
                        <dd>{{ $presentation['generated_by'] ?? 'System Administrator' }} ({{ $presentation['generated_role'] ?? 'Administrator' }})</dd>
                    </div>
                    <div class="info-item">
                        <dt>Report ID</dt>
                        <dd>{{ $presentation['report_id'] ?? 'RPT-'.now()->format('YmdHis') }}</dd>
                    </div>
                </dl>
            </div>
        </section>

        <section class="section">
            <h2 class="section-title">Summary</h2>
            <table class="summary-table">
                <tbody>
                    <tr><th>Registered Students</th><td>{{ number_format($extended['registered_students'] ?? 0) }}</td></tr>
                    <tr><th>Students Voted</th><td>{{ number_format($extended['students_voted'] ?? 0) }}</td></tr>
                    <tr><th>Total Votes</th><td>{{ number_format($extended['total_votes'] ?? 0) }}</td></tr>
                    <tr><th>Turnout Percentage</th><td>{{ number_format($extended['turnout_percent'] ?? 0, 1) }}%</td></tr>
                    <tr><th>Valid Votes</th><td>{{ number_format($extended['valid_votes'] ?? 0) }}</td></tr>
                    <tr><th>Invalid Votes</th><td>{{ number_format($extended['invalid_votes'] ?? 0) }}</td></tr>
                    <tr><th>Total Winners</th><td>{{ number_format($extended['total_winners'] ?? 0) }}</td></tr>
                </tbody>
            </table>
        </section>

        <section class="section">
            <h2 class="section-title">Winning Candidates</h2>
            @if (count($winningCandidates) === 0)
                <div class="empty-note">No winners have been declared.</div>
            @else
                <div class="winners-grid">
                    @foreach ($winningCandidates as $winner)
                        <article class="winner-card">
                            <span class="winner-badge">Winner</span>
                            <p class="position">{{ $winner['position'] }}</p>
                            <p class="name">{{ $winner['name'] }}</p>
                            <p class="meta">
                                Partylist: {{ $winner['party'] ?? 'Independent' }}<br>
                                Votes: {{ number_format($winner['votes'] ?? 0) }} · {{ number_format($winner['percent'] ?? 0, 1) }}%
                            </p>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="section">
            <h2 class="section-title">Full Rankings</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Contestant / Candidate</th>
                        <th>Position</th>
                        <th>Party</th>
                        <th>Votes</th>
                        <th>Percentage</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($detail['rankings'] ?? [] as $row)
                        <tr @class(['is-first-place' => (int) ($row['rank'] ?? 0) === 1])>
                            <td>{{ $row['rank'] }}</td>
                            <td>{{ $row['name'] }}</td>
                            <td>{{ $row['position'] }}</td>
                            <td>{{ $row['party'] }}</td>
                            <td>{{ number_format($row['votes']) }}</td>
                            <td>{{ number_format($row['percent'], 1) }}%</td>
                            <td>
                                @if (($row['status'] ?? '') === 'Winner')
                                    <span class="status-pill status-pill--winner">{{ $row['status'] }}</span>
                                @else
                                    {{ $row['status'] }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center;color:#64748b;">No ranking data available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        @if (($detail['type'] ?? '') === 'election')
            <section class="section">
                <h2 class="section-title">Party Performance</h2>
                @if ($partyPerformance === [])
                    <div class="empty-note">No party performance data available.</div>
                @else
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Party</th>
                                <th>Total Votes</th>
                                <th>Seats Won</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($partyPerformance as $party)
                                <tr>
                                    <td>{{ $party['party'] }}</td>
                                    <td>{{ number_format($party['total_votes']) }}</td>
                                    <td>{{ number_format($party['seats_won']) }}</td>
                                    <td>{{ number_format($party['percent'], 1) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </section>
        @endif

        <section class="section">
            <h2 class="section-title">Charts</h2>
            @if (! $hasChartData)
                <div class="empty-note">No chart data available.</div>
            @else
                <div class="charts-grid">
                    <div class="chart-card">
                        <h4>Bar Chart — Candidate Votes</h4>
                        @foreach (($barChart['labels'] ?? []) as $index => $label)
                            @php $value = (int) (($barChart['values'][$index] ?? 0)); @endphp
                            <div class="bar-row">
                                <span>{{ \Illuminate\Support\Str::limit($label, 12) }}</span>
                                <div class="bar-track"><div class="bar-fill" style="width: {{ $barMax > 0 ? round(($value / $barMax) * 100) : 0 }}%"></div></div>
                                <span>{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="chart-card">
                        <h4>Pie Chart — Vote Share</h4>
                        @unless ($forPdf)
                            <div class="donut" style="background: {{ $pieGradient }};"></div>
                        @endunless
                        <div class="chart-legend">
                            @foreach (($pieChart['labels'] ?? []) as $index => $label)
                                <div>{{ \Illuminate\Support\Str::limit($label, 18) }} — {{ number_format((float) ($pieChart['values'][$index] ?? 0), 1) }}%</div>
                            @endforeach
                        </div>
                    </div>
                    <div class="chart-card">
                        <h4>Doughnut — Seat Distribution</h4>
                        @if ($partyPerformance === [])
                            <div class="empty-note" style="margin-top:0.5rem;">No seat distribution data.</div>
                        @else
                            @unless ($forPdf)
                                <div class="donut" style="background: {{ $seatGradient }};"></div>
                            @endunless
                            <div class="chart-legend">
                                @foreach ($partyPerformance as $party)
                                    @if (($party['seats_won'] ?? 0) > 0)
                                        <div>{{ $party['party'] }} — {{ $party['seats_won'] }} seat(s)</div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </section>

        @if (($detail['type'] ?? '') === 'election')
            <section class="section">
                <h2 class="section-title">Turnout by Grade / Section</h2>
                @if ($turnoutSections === [])
                    <div class="empty-note">No turnout breakdown available for this report scope.</div>
                @else
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Grade</th>
                                <th>Section</th>
                                <th>Registered</th>
                                <th>Voted</th>
                                <th>Turnout %</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($turnoutSections as $row)
                                <tr>
                                    <td>{{ $row['grade'] }}</td>
                                    <td>{{ $row['section'] }}</td>
                                    <td>{{ number_format($row['registered']) }}</td>
                                    <td>{{ number_format($row['voted']) }}</td>
                                    <td>{{ number_format($row['turnout_percent'], 1) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </section>
        @endif

        <div class="signatures">
            <div class="signature-block">
                <p>Prepared By</p>
                <div class="signature-line"></div>
                <p><strong>System Administrator</strong></p>
            </div>
            <div class="signature-block">
                <p>Approved By</p>
                <div class="signature-line"></div>
                <p><strong>School Administrator</strong></p>
            </div>
        </div>

        <footer class="report-footer">
            <span>Generated automatically by {{ $systemName }} · {{ $schoolName }}</span>
            <span>{{ $presentation['report_id'] ?? '' }}</span>
        </footer>
    </div>

    @if ($forPrint ?? false)
        <div class="no-print">
            <button type="button" onclick="window.print()">Print Report</button>
        </div>
        <script>window.addEventListener('load', () => window.print());</script>
    @endif
</body>
</html>

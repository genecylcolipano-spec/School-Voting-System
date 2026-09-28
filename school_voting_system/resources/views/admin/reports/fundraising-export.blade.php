@php
    $forPdf = (bool) ($forPdf ?? false);
    $schoolName = \App\Support\SchoolBranding::schoolName();
    $systemName = \App\Support\SchoolBranding::systemName();
    $schoolLogoSrc = \App\Support\SchoolBranding::logoDataUri() ?? '';
    if ($schoolLogoSrc === '' && is_file(public_path('images/rosemont-hills-logo.png'))) {
        $schoolLogoSrc = 'data:image/png;base64,'.base64_encode((string) file_get_contents(public_path('images/rosemont-hills-logo.png')));
    }
    $academicYearLabel = \App\Support\SchoolBranding::academicYear();
    $semesterLabel = \App\Support\SchoolBranding::semester();
    $summary = $summary ?? [];
    $fundraisers = $fundraisers ?? [];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OFFICIAL FUNDRAISING REPORT</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 16mm 14mm 24mm 14mm;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            color: #0f172a;
            font-family: {{ $forPdf ? "'DejaVu Sans', sans-serif" : '"Segoe UI", Tahoma, Geneva, Verdana, sans-serif' }};
            font-size: 11pt;
            line-height: 1.45;
            background: #ffffff;
        }

        .watermark {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            z-index: 0;
            opacity: 0.05;
            font-size: 56pt;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-align: center;
            line-height: 1.15;
            color: #1e40af;
            transform: rotate(-28deg);
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
            width: 240px;
            height: 170px;
            margin: 0 auto 0.65rem;
            display: block;
            text-align: center;
        }

        .logo-wrap img {
            width: 240px;
            height: 170px;
            object-fit: contain;
            object-position: center;
            display: inline-block;
        }

        .institution { text-align: center; }

        .institution .school-name {
            margin: 0;
            font-family: 'Monotype Corsiva', 'Apple Chancery', cursive;
            font-size: 32pt;
            font-weight: normal;
            letter-spacing: 0.02em;
            color: #1e3a8a;
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
            page-break-inside: avoid;
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

        .summary-table,
        .data-table {
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

        .data-table {
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
        .data-table .num { text-align: right; }

        .empty-note {
            padding: 1rem;
            border: 1px dashed #e2e8f0;
            border-radius: 8px;
            text-align: center;
            color: #64748b;
            font-size: 10pt;
            background: #f8fafc;
        }

        .campaign-block { margin-bottom: 1.1rem; }

        .campaign-block h3 {
            margin: 0 0 0.45rem;
            font-size: 11pt;
            color: #1e3a8a;
        }

        .signatures {
            margin-top: 2rem;
            width: 100%;
            page-break-inside: avoid;
        }

        .signatures td {
            width: 50%;
            vertical-align: top;
            padding-right: 1.5rem;
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
        }

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
        }
    </style>
</head>
<body>
    <div class="watermark">OFFICIAL COPY</div>

    <div class="report">
        <header class="report-header">
            <div class="logo-wrap">
                @if ($schoolLogoSrc !== '')
                    <img src="{{ $schoolLogoSrc }}" alt="{{ $schoolName }} logo" width="220" height="170">
                @endif
            </div>
            <div class="institution">
                <p class="school-name">{{ $schoolName }}</p>
                <p class="school-address">{{ $semesterLabel }}</p>
                <p class="system-name">{{ $systemName }}</p>
            </div>
        </header>

        <div class="title-block">
            <h1>OFFICIAL FUNDRAISING REPORT</h1>
            <p class="subtitle">Paid donations by campaign</p>
            <p class="academic-year">Academic Year {{ $academicYearLabel }}</p>
        </div>

        <section class="section">
            <h2 class="section-title">Report Information</h2>
            <table class="summary-table">
                <tbody>
                    <tr><th>Date Generated</th><td>{{ $generatedAt }}</td></tr>
                    <tr><th>Generated By</th><td>{{ $signatory }} ({{ $signatoryRole ?? 'Administrator' }})</td></tr>
                    <tr><th>Report ID</th><td>{{ $reportId ?? 'RPT-FR' }}</td></tr>
                    <tr><th>Included donations</th><td>Successfully paid gifts only. Pending, cancelled, and failed donations are excluded. Anonymous paid donors are listed as Anonymous.</td></tr>
                </tbody>
            </table>
        </section>

        <section class="section">
            <h2 class="section-title">Summary</h2>
            <table class="summary-table">
                <tbody>
                    <tr><th>Campaigns</th><td>{{ number_format($summary['campaigns'] ?? 0) }}</td></tr>
                    <tr><th>Paid Donations</th><td>{{ number_format($summary['total_donations'] ?? 0) }}</td></tr>
                    <tr><th>Total Goal</th><td>₱{{ number_format((float) ($summary['total_goal'] ?? 0), 2) }}</td></tr>
                    <tr><th>Total Raised</th><td>₱{{ number_format((float) ($summary['total_raised'] ?? 0), 2) }}</td></tr>
                </tbody>
            </table>
        </section>

        <section class="section">
            <h2 class="section-title">Campaigns</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Campaign</th>
                        <th>Status</th>
                        <th>Paid Donations</th>
                        <th>Goal</th>
                        <th>Raised</th>
                        <th>Progress</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($fundraisers as $row)
                        <tr>
                            <td>{{ $row['title'] }}</td>
                            <td>{{ $row['status'] }}</td>
                            <td class="num">{{ number_format($row['donations']) }}</td>
                            <td class="num">₱{{ number_format((float) $row['goal'], 2) }}</td>
                            <td class="num">₱{{ number_format((float) $row['raised'], 2) }}</td>
                            <td class="num">{{ round((float) $row['progress']) }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;color:#64748b;">No fundraising campaigns in scope.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="section">
            <h2 class="section-title">Successful Donors</h2>
            @forelse ($fundraisers as $row)
                <div class="campaign-block">
                    <h3>{{ $row['title'] }}</h3>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Donor</th>
                                <th>Role</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($row['donors'] ?? [] as $donor)
                                <tr>
                                    <td>{{ $donor['name'] }}</td>
                                    <td>{{ $donor['role'] }}</td>
                                    <td class="num">₱{{ number_format((float) $donor['amount'], 2) }}</td>
                                    <td>{{ $donor['method'] }}</td>
                                    <td>{{ $donor['donated_at'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" style="text-align:center;color:#64748b;">No successful donations for this campaign.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @empty
                <div class="empty-note">No fundraising campaigns in scope.</div>
            @endforelse
        </section>

        <table class="signatures">
            <tr>
                <td>
                    <p>Prepared By</p>
                    <div class="signature-line"></div>
                    <p><strong>{{ $signatory }}</strong></p>
                    <p>{{ $signatoryRole ?? 'Administrator' }}</p>
                </td>
                <td>
                    <p>Approved By</p>
                    <div class="signature-line"></div>
                    <p><strong>School Administrator</strong></p>
                </td>
            </tr>
        </table>

        <footer class="report-footer">
            Generated automatically by {{ $systemName }} · {{ $schoolName }} · {{ $reportId ?? '' }}
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

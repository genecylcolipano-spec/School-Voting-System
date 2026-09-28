@php
    $forPdf = (bool) ($forPdf ?? false);
    $landscape = (bool) ($landscape ?? false);
    $schoolName = \App\Support\SchoolBranding::schoolName();
    $systemName = \App\Support\SchoolBranding::systemName();
    $schoolLogoSrc = \App\Support\SchoolBranding::logoDataUri() ?? '';
    if ($schoolLogoSrc === '' && is_file(public_path('images/rosemont-hills-logo.png'))) {
        $schoolLogoSrc = 'data:image/png;base64,'.base64_encode((string) file_get_contents(public_path('images/rosemont-hills-logo.png')));
    }
    $academicYearLabel = \App\Support\SchoolBranding::academicYear();
    $semesterLabel = \App\Support\SchoolBranding::semester();
    $reportTitle = strtoupper((string) ($title ?? 'Official Report'));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Official Report' }}</title>
    <style>
        @page {
            size: A4 {{ $landscape ? 'landscape' : 'portrait' }};
            margin: 14mm 12mm 20mm 12mm;
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
            max-width: {{ $landscape ? '277mm' : '210mm' }};
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
            font-size: 18pt;
            letter-spacing: 0.06em;
            color: #1e3a8a;
            font-weight: 800;
        }

        .title-block .academic-year {
            margin: 0.25rem 0 0;
            font-size: 10pt;
            color: #64748b;
        }

        .report-body p {
            margin: 0 0 0.85rem;
            font-size: 10pt;
            color: #334155;
        }

        .report-body table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e2e8f0;
            margin: 0 0 1.1rem;
            font-size: 9.5pt;
        }

        .report-body table thead th {
            background: #1e40af;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-size: 8.5pt;
            padding: 0.5rem 0.65rem;
            border: 1px solid #1e3a8a;
            text-align: left;
        }

        .report-body table tbody th {
            width: 42%;
            background: #dbeafe;
            color: #1e3a8a;
            font-weight: 700;
            text-align: left;
            padding: 0.5rem 0.65rem;
            border: 1px solid #e2e8f0;
        }

        .report-body table td {
            padding: 0.5rem 0.65rem;
            border: 1px solid #e2e8f0;
            vertical-align: top;
            color: #0f172a;
            background: #ffffff;
        }

        .report-body table tbody tr:nth-child(even) td { background: #f8fafc; }

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
            <h1>{{ $reportTitle }}</h1>
            <p class="academic-year">Academic Year {{ $academicYearLabel }}</p>
        </div>

        <div class="report-body">
            {!! $body !!}
        </div>

        <table class="signatures">
            <tr>
                <td>
                    <p>Prepared By</p>
                    <div class="signature-line"></div>
                    <p><strong>{{ $signatory }}</strong></p>
                    <p>{{ $signatoryRole ?? 'Super Admin' }}</p>
                </td>
                <td>
                    <p>Approved By</p>
                    <div class="signature-line"></div>
                    <p><strong>School Administrator</strong></p>
                </td>
            </tr>
        </table>

        <footer class="report-footer">
            Generated automatically by {{ $systemName }} · {{ $schoolName }} · {{ $generatedAt }}
        </footer>
    </div>
</body>
</html>

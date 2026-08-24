<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Fundraising Report</title>
    <style>
        body { font-family: Georgia, serif; margin: 40px; color: #111; }
        header { border-bottom: 3px solid #4c1d95; padding-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 14px; }
        th { background: #f4f0ff; }
        td.num { text-align: right; }
        footer { margin-top: 40px; font-size: 12px; color: #555; }
    </style>
</head>
<body>
    <header>
        <h1>{{ config('app.name') }}</h1>
        <h2>Fundraising Report</h2>
        <p>Generated: {{ $generatedAt }}</p>
    </header>

    <p>Campaigns: {{ number_format($summary['campaigns']) }} · Paid donations: {{ number_format($summary['total_donations']) }}</p>
    <p>Total goal: ₱{{ number_format($summary['total_goal'], 2) }} · Total raised: ₱{{ number_format($summary['total_raised'], 2) }}</p>

    <table>
        <thead>
            <tr>
                <th>Campaign</th>
                <th>Status</th>
                <th>Paid donations</th>
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
                    <td class="num">₱{{ number_format($row['goal'], 2) }}</td>
                    <td class="num">₱{{ number_format($row['raised'], 2) }}</td>
                    <td class="num">{{ round($row['progress']) }}%</td>
                </tr>
            @empty
                <tr><td colspan="6">No fundraising campaigns in scope.</td></tr>
            @endforelse
        </tbody>
    </table>

    <footer>
        <p>Digitally signed by: <strong>{{ $signatory }}</strong></p>
        <p>Paid donations only. This document is system-generated.</p>
    </footer>
    <script>window.addEventListener('load', () => window.print());</script>
</body>
</html>

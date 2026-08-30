@php
    $reportableElections = $reportableElections ?? collect();
    $complianceElectionId = $complianceElectionId ?? $reportableElections->first()?->id;
    $buttonClass = 'inline-flex min-h-11 items-center justify-center rounded-xl border border-violet-500/30 px-4 py-2 text-center text-sm font-semibold text-violet-200 hover:bg-violet-500/10';
    $linkClass = 'text-xs font-semibold text-violet-300 hover:text-violet-200';
@endphp

<section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
    <h3 class="text-lg font-semibold text-white">Compliance & Official Reports</h3>
    <p class="mt-1 text-sm text-slate-400">
        Download PDF snapshots for filing. Live charts and election exports are in Reports &amp; Analytics.
        Official student-facing results are published per election from Results, not from System Settings.
    </p>

    <form method="GET" action="{{ route('super-admin.reports.generate') }}" class="mt-4 space-y-3">
        <input type="hidden" name="format" value="pdf">
        <div>
            <label for="compliance-election" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Election for Summary and Turnout</label>
            <select id="compliance-election" name="election_id" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white sm:max-w-md">
                <option value="">All elections (summary index) / default turnout</option>
                @foreach ($reportableElections as $election)
                    <option value="{{ $election->id }}" @selected((int) $complianceElectionId === (int) $election->id)>
                        {{ $election->title }}
                        ({{ $election->status?->label() ?? $election->status?->value }}{{ $election->public_results_published ? ', published' : '' }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4">
            <button type="submit" name="report" value="election_summary" class="{{ $buttonClass }}">Election Summary PDF</button>
            <button type="submit" name="report" value="voter_turnout" class="{{ $buttonClass }}">Voter Turnout PDF</button>
            <button type="submit" name="report" value="audit_trail" class="{{ $buttonClass }}">Audit Trail PDF</button>
            <button type="submit" name="report" value="passkey_inventory" class="{{ $buttonClass }}">Passkey Inventory PDF</button>
        </div>
    </form>

    <p class="mt-3 text-xs text-slate-500">
        Each button downloads an A4 PDF (same engine as Results). Audit Trail is the latest 500 actions (landscape).
        Passkey Inventory lists status only — it does not include credential IDs and cannot revoke a device.
    </p>

    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-2">
        <a href="{{ route('admin.reports.index') }}" class="{{ $linkClass }}">Reports &amp; Analytics</a>
        <a href="{{ route('admin.results.index') }}" class="{{ $linkClass }}">Results</a>
        <a href="{{ route('super-admin.system.audit.index') }}" class="{{ $linkClass }}">Full audit logs</a>
        <a href="{{ route('super-admin.audit.export') }}" class="{{ $linkClass }}">Export audit CSV</a>
        <a href="{{ route('admin.analytics.index') }}" class="{{ $linkClass }}">Dashboard analytics</a>
    </div>
</section>

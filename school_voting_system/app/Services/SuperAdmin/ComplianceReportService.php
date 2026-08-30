<?php

namespace App\Services\SuperAdmin;

use App\Enums\PasskeyStatus;
use App\Models\AuditLog;
use App\Models\Election;
use App\Models\Passkey;
use App\Models\User;
use App\Services\Admin\AdminScopeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use InvalidArgumentException;

class ComplianceReportService
{
    protected bool $forPdf = false;

    public function __construct(protected AdminScopeService $scope) {}

    public function html(string $type, User $actor, ?int $electionId = null, bool $forPdf = false): string
    {
        $previous = $this->forPdf;
        $this->forPdf = $forPdf;

        try {
            return match ($type) {
                'election_summary' => $this->electionSummary($actor, $electionId),
                'voter_turnout' => $this->voterTurnout($actor, $electionId),
                'audit_trail' => $this->auditTrail(),
                'passkey_inventory' => $this->passkeyInventory(),
                default => throw new InvalidArgumentException('Unknown compliance report.'),
            };
        } finally {
            $this->forPdf = $previous;
        }
    }

    public function pdf(string $type, User $actor, ?int $electionId = null): Response
    {
        $html = $this->html($type, $actor, $electionId, forPdf: true);
        $orientation = in_array($type, ['audit_trail', 'passkey_inventory'], true)
            ? 'landscape'
            : 'portrait';

        $fontDir = storage_path('fonts');
        if (! is_dir($fontDir)) {
            mkdir($fontDir, 0755, true);
        }

        $filename = str_replace('_', '-', $type).'-'.now()->format('Y-m-d').'.pdf';

        return Pdf::loadHTML($html)
            ->setPaper('a4', $orientation)
            ->setOption([
                'isRemoteEnabled' => false,
                'fontDir' => $fontDir,
                'fontCache' => $fontDir,
                'chroot' => base_path(),
            ])
            ->download($filename);
    }

    protected function electionSummary(User $actor, ?int $electionId): string
    {
        if ($electionId) {
            $election = $this->scope->resolveReportElection($actor, $electionId);

            if (! $election) {
                return $this->shell('Election Summary Report', '<p>No election is available to report on.</p>');
            }

            return $this->shell('Election Summary Report', $this->electionDetailTable($election));
        }

        $elections = Election::query()->withCount('votes')->latest('id')->get();

        if ($elections->isEmpty()) {
            return $this->shell('Election Summary Report', '<p>No elections have been created yet.</p>');
        }

        $rows = $elections->map(function (Election $election) {
            return '<tr>'
                .$this->td($election->title)
                .$this->td($election->status?->label() ?? '—')
                .$this->td($election->public_results_published ? 'Published' : 'Not published')
                .$this->td((string) $election->votes_count)
                .$this->td($election->integrity_hash ?: '—')
                .'</tr>';
        })->join('');

        $note = '<p>Index of all elections. Select an election on the dashboard for turnout and a single-election summary, or use Reports &amp; Analytics for winners and exports.</p>';

        return $this->shell(
            'Election Summary Report',
            $note.$this->table(
                ['Election', 'Status', 'Official results', 'Ballots', 'Integrity hash'],
                $rows
            )
        );
    }

    protected function voterTurnout(User $actor, ?int $electionId): string
    {
        $election = $this->scope->resolveReportElection($actor, $electionId);

        if (! $election) {
            return $this->shell('Voter Turnout Report', '<p>No election is available to report on.</p>');
        }

        return $this->shell('Voter Turnout Report', $this->electionDetailTable($election, turnoutOnly: true));
    }

    protected function electionDetailTable(Election $election, bool $turnoutOnly = false): string
    {
        $eligible = $election->eligibleVoterCount();
        $voted = (int) $election->votes()->distinct('user_id')->count('user_id');
        $ballots = $election->votes()->count();
        $turnout = $eligible > 0 ? round(($voted / $eligible) * 100, 1) : 0.0;
        $window = ($election->voting_starts_at?->format('M d, Y g:i A') ?? '—')
            .' – '
            .($election->voting_ends_at?->format('M d, Y g:i A') ?? '—');

        $rows = $this->kv('Election', $election->title)
            .$this->kv('Status', $election->status?->label() ?? '—')
            .$this->kv('Voting window', $window)
            .$this->kv('Official results', $election->public_results_published ? 'Published to students' : 'Not published')
            .$this->kv('Eligible students', (string) $eligible)
            .$this->kv('Students voted', (string) $voted)
            .$this->kv('Turnout', $turnout.'%');

        if (! $turnoutOnly) {
            $rows .= $this->kv('Total ballots', (string) $ballots)
                .$this->kv('Integrity hash', $election->integrity_hash ?: '—');
        }

        $intro = $turnoutOnly
            ? '<p>Eligible students are currently enrolled, active student accounts. Open Reports &amp; Analytics for charts, or Results for official exports.</p>'
            : '<p>Snapshot of this election. Winners and per-position tallies are in Reports &amp; Analytics / Results — this file is for filing.</p>';

        return $intro.'<table>'.$rows.'</table>';
    }

    protected function auditTrail(): string
    {
        $logs = AuditLog::query()->latest()->limit(500)->get();

        $rows = $logs->map(function (AuditLog $log) {
            return '<tr>'
                .$this->td($log->created_at?->toDateTimeString() ?? '—')
                .$this->td($log->admin_name)
                .$this->td($log->admin_role)
                .$this->td($log->action)
                .$this->td($log->action_type?->value ?? '—')
                .$this->td($log->ip_address)
                .$this->td(ucfirst((string) $log->status))
                .'</tr>';
        })->join('');

        $note = '<p>Latest 500 admin actions. Full history and CSV export are on Audit Logs. This extract does not include enrollment URLs.</p>';

        return $this->shell(
            'Audit Trail Report',
            $note.$this->table(
                ['Time', 'Admin', 'Role', 'Action', 'Type', 'IP', 'Status'],
                $rows !== '' ? $rows : '<tr><td colspan="7">No audit entries yet.</td></tr>'
            )
        );
    }

    protected function passkeyInventory(): string
    {
        $passkeys = Passkey::query()->with('user')->latest()->get();
        $active = $passkeys->filter(fn (Passkey $passkey) => $passkey->status === PasskeyStatus::Active)->count();
        $revoked = $passkeys->filter(fn (Passkey $passkey) => $passkey->status === PasskeyStatus::Revoked)->count();
        $lost = $passkeys->filter(fn (Passkey $passkey) => $passkey->status === PasskeyStatus::Lost)->count();

        $rows = $passkeys->map(function (Passkey $passkey) {
            $user = $passkey->user;

            return '<tr>'
                .$this->td($user?->name)
                .$this->td($user?->account_id)
                .$this->td($user?->roleLabel())
                .$this->td($passkey->device_name ?? $passkey->name)
                .$this->td($passkey->status?->label() ?? 'Active')
                .$this->td($passkey->created_at?->toDateTimeString())
                .$this->td($passkey->last_used_at?->toDateTimeString() ?? 'Never')
                .'</tr>';
        })->join('');

        $summary = '<p>Active: '.$active.' · Revoked: '.$revoked.' · Marked lost: '.$lost.' · Total: '.$passkeys->count().'</p>'
            .'<p>Credential IDs are omitted. Use Advanced Passkey Management to revoke or mark lost a device.</p>';

        return $this->shell(
            'Passkey Inventory',
            $summary.$this->table(
                ['Account', 'Account ID', 'Role', 'Device', 'Status', 'Added', 'Last used'],
                $rows !== '' ? $rows : '<tr><td colspan="7">No passkeys registered yet.</td></tr>'
            )
        );
    }

    protected function kv(string $label, ?string $value): string
    {
        return '<tr><th>'.$this->e($label).'</th><td>'.$this->e($value).'</td></tr>';
    }

    protected function td(?string $value): string
    {
        return '<td>'.$this->e($value).'</td>';
    }

    /**
     * @param  list<string>  $headers
     */
    protected function table(array $headers, string $body): string
    {
        $head = collect($headers)->map(fn (string $header) => '<th>'.$this->e($header).'</th>')->join('');

        return '<table><thead><tr>'.$head.'</tr></thead><tbody>'.$body.'</tbody></table>';
    }

    protected function shell(string $title, string $body): string
    {
        $school = $this->e((string) config('app.name'));
        $safeTitle = $this->e($title);
        $timestamp = $this->e(now()->toDayDateTimeString());
        $signatory = $this->e(auth()->user()?->name ?? 'Chief Super Admin');
        $font = $this->forPdf ? 'DejaVu Sans, sans-serif' : 'Georgia, serif';
        $intro = $this->forPdf
            ? 'This is a snapshot for filing, not a live dashboard.'
            : 'Print this page to save a PDF, or use Download PDF on the Super Admin dashboard.';

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>{$safeTitle}</title>
<style>
body{font-family:{$font};margin:40px;color:#111;line-height:1.45}
header{border-bottom:3px solid #4c1d95;padding-bottom:16px;margin-bottom:24px}
h1{margin:0 0 8px;font-size:22px}
h2{margin:0 0 8px;font-size:18px}
p{margin:0 0 12px}
table{border-collapse:collapse;width:100%;margin:16px 0}
th,td{border:1px solid #ccc;padding:8px;text-align:left;vertical-align:top}
th{background:#f4f0fb}
footer{margin-top:40px;font-size:12px;color:#555}
@media print{a{color:inherit;text-decoration:none}header{border-bottom-color:#000}}
</style>
</head>
<body>
<header>
<h1>{$school}</h1>
<h2>{$safeTitle}</h2>
<p>Generated: {$timestamp}</p>
<p>{$intro}</p>
</header>
{$body}
<footer>
<p>Prepared by: <strong>{$signatory}</strong></p>
<p>System-generated. Compare sensitive actions against Audit Logs if you need the full record.</p>
</footer>
</body>
</html>
HTML;
    }

    protected function e(?string $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

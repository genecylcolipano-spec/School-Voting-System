<?php

namespace App\Services\SuperAdmin;

use App\Enums\AuditActionType;
use App\Enums\UserRole;
use App\Models\SystemBackup;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use ZipArchive;

class BackupService
{
    public const TYPE_FULL = 'full_system';

    public const TYPE_ELECTION_RESULTS = 'election_results';

    public const TYPE_STUDENT_DATA = 'student_data';

    public const TYPE_ADMIN_ACCOUNTS = 'admin_accounts';

    /**
     * Application tables included in a full disaster-recovery backup.
     * Excludes cache, sessions, jobs, password reset tokens, and system_backups itself.
     *
     * @var list<string>
     */
    public const FULL_TABLES = [
        'users',
        'passkeys',
        'passkey_recovery_requests',
        'allowed_students',
        'allowed_faculty',
        'allowed_administrators',
        'staff_roles',
        'permissions',
        'staff_role_permission',
        'elections',
        'election_categories',
        'candidates',
        'votes',
        'ballot_submissions',
        'partylists',
        'partylist_posters',
        'election_partylist',
        'voter_blocks',
        'talent_events',
        'talent_event_entries',
        'talent_event_votes',
        'talent_event_judges',
        'talent_judging_criteria',
        'talent_judge_score_sheets',
        'talent_judge_criterion_scores',
        'fundraisers',
        'donations',
        'announcements',
        'announcement_attachments',
        'announcement_views',
        'portal_notifications',
        'events',
        'audit_logs',
        'system_settings',
        'admin_assignments',
        'admin_verification_requests',
        'admin_complaints',
        'admin_help_requests',
    ];

    /**
     * Public-disk folders whose uploaded files are included in full backups.
     *
     * @var list<string>
     */
    public const MEDIA_DIRECTORIES = [
        'school-logos',
        'avatars',
        'profile-photos',
        'announcements',
        'talent',
        'partylists',
        'candidates',
        'elections',
        'events',
        'fundraisers',
    ];

    public function __construct(protected AuditLogService $audit) {}

    public function create(User $actor, string $type = self::TYPE_FULL): SystemBackup
    {
        return match ($type) {
            self::TYPE_FULL => $this->createFullSystemBackup($actor),
            self::TYPE_ELECTION_RESULTS,
            self::TYPE_STUDENT_DATA,
            self::TYPE_ADMIN_ACCOUNTS => $this->createLegacyJsonBackup($actor, $type),
            default => $this->createFullSystemBackup($actor),
        };
    }

    public function download(SystemBackup $backup): StreamedResponse
    {
        $fullPath = storage_path('app/'.$backup->file_path);

        abort_unless(File::exists($fullPath), 404);

        $filename = basename($backup->file_path);
        $mime = str_ends_with(strtolower($filename), '.zip')
            ? 'application/zip'
            : 'application/json';

        return response()->streamDownload(
            fn () => readfile($fullPath),
            $filename,
            ['Content-Type' => $mime]
        );
    }

    public function delete(User $actor, SystemBackup $backup): void
    {
        $fullPath = storage_path('app/'.$backup->file_path);

        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }

        $backupId = $backup->id;
        $label = $backup->label;
        $backup->delete();

        $this->audit->record(
            $actor,
            'Deleted Backup',
            AuditActionType::Backup,
            targetType: 'backup',
            targetId: $backupId,
            metadata: ['label' => $label],
        );
    }

    /**
     * @return array{total: int, latest: ?SystemBackup, storage_bytes: int, last_backup_at: ?\Illuminate\Support\Carbon, last_restore: null}
     */
    public function dashboardStats(): array
    {
        $latest = SystemBackup::query()->latest('completed_at')->first();

        return [
            'total' => SystemBackup::query()->count(),
            'latest' => $latest,
            'storage_bytes' => (int) SystemBackup::query()->sum('file_size'),
            'last_backup_at' => $latest?->completed_at,
            'last_restore' => null,
        ];
    }

    public function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 2).' GB';
        }

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2).' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }

    /**
     * @return array<string, mixed>
     */
    public function details(SystemBackup $backup): array
    {
        $manifest = is_array($backup->manifest) ? $backup->manifest : [];

        return [
            'label' => $backup->label,
            'type' => $backup->type,
            'type_label' => $backup->typeLabel(),
            'created_by' => $backup->creator?->name ?? '—',
            'created_at' => $backup->completed_at,
            'file_size' => $backup->file_size,
            'file_size_label' => $backup->formattedSize(),
            'database_bytes' => (int) ($manifest['database_bytes'] ?? 0),
            'database_size_label' => $this->formatBytes((int) ($manifest['database_bytes'] ?? 0)),
            'files_bytes' => (int) ($manifest['files_bytes'] ?? 0),
            'files_size_label' => $this->formatBytes((int) ($manifest['files_bytes'] ?? 0)),
            'tables' => $manifest['tables'] ?? [],
            'files' => $manifest['files'] ?? [],
            'status' => $backup->status,
            'version' => $manifest['version'] ?? null,
            'notes' => $manifest['notes'] ?? null,
        ];
    }

    protected function createFullSystemBackup(User $actor): SystemBackup
    {
        $timestamp = now()->format('Y-m-d_His');
        $filename = "backup_full_system_{$timestamp}.zip";
        $directory = storage_path('app/backups');

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $path = $directory.DIRECTORY_SEPARATOR.$filename;
        $tableExport = $this->exportTables();
        $mediaFiles = $this->collectMediaFiles();

        $databaseJson = json_encode([
            'exported_at' => now()->toIso8601String(),
            'tables' => $tableExport['data'],
        ], JSON_UNESCAPED_UNICODE);

        if ($databaseJson === false) {
            throw new \RuntimeException('Failed to encode database backup payload.');
        }

        $databaseBytes = strlen($databaseJson);
        $filesBytes = array_sum(array_column($mediaFiles, 'size'));

        $manifest = [
            'version' => 1,
            'type' => self::TYPE_FULL,
            'created_at' => now()->toIso8601String(),
            'created_by' => [
                'id' => $actor->id,
                'name' => $actor->name,
                'account_id' => $actor->account_id,
                'role' => $actor->role?->value ?? (string) $actor->role,
            ],
            'app' => config('app.name'),
            'notes' => 'Disaster recovery point — full application data and uploaded media.',
            'tables' => $tableExport['summary'],
            'files' => array_map(fn (array $f) => [
                'path' => $f['relative'],
                'size' => $f['size'],
            ], $mediaFiles),
            'database_bytes' => $databaseBytes,
            'files_bytes' => $filesBytes,
        ];

        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Unable to create backup archive.');
        }

        try {
            $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '{}');
            $zip->addFromString('database.json', $databaseJson);

            foreach ($mediaFiles as $file) {
                $zip->addFile($file['absolute'], 'files/'.$file['relative']);
            }

            $zip->close();
        } catch (Throwable $e) {
            $zip->close();
            if (File::exists($path)) {
                File::delete($path);
            }
            throw $e;
        }

        $backup = SystemBackup::query()->create([
            'label' => 'Full System Backup '.$timestamp,
            'type' => self::TYPE_FULL,
            'file_path' => "backups/{$filename}",
            'file_size' => File::size($path),
            'created_by' => $actor->id,
            'completed_at' => now(),
            'status' => 'completed',
            'manifest' => $manifest,
        ]);

        $this->audit->record(
            $actor,
            'Created Backup',
            AuditActionType::Backup,
            targetType: 'backup',
            targetId: $backup->id,
            metadata: ['type' => self::TYPE_FULL, 'label' => $backup->label],
        );

        app(\App\Services\Portal\PortalNotificationService::class)
            ->backupCompleted($backup->label, $actor, $backup->id);

        return $backup;
    }

    protected function createLegacyJsonBackup(User $actor, string $type): SystemBackup
    {
        $timestamp = now()->format('Y-m-d_His');
        $filename = "backup_{$type}_{$timestamp}.json";
        $directory = storage_path('app/backups');

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $payload = match ($type) {
            self::TYPE_ELECTION_RESULTS => $this->electionResultsPayload(),
            self::TYPE_STUDENT_DATA => $this->studentDataPayload(),
            self::TYPE_ADMIN_ACCOUNTS => $this->adminAccountsPayload(),
            default => ['exported_at' => now()->toIso8601String()],
        };

        $path = $directory.DIRECTORY_SEPARATOR.$filename;
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '{}';
        File::put($path, $json);

        $manifest = [
            'version' => 1,
            'type' => $type,
            'created_at' => now()->toIso8601String(),
            'notes' => 'Legacy partial backup (JSON export only).',
            'tables' => [],
            'files' => [],
            'database_bytes' => strlen($json),
            'files_bytes' => 0,
        ];

        $backup = SystemBackup::query()->create([
            'label' => str($type)->replace('_', ' ')->title().' Backup',
            'type' => $type,
            'file_path' => "backups/{$filename}",
            'file_size' => File::size($path),
            'created_by' => $actor->id,
            'completed_at' => now(),
            'status' => 'completed',
            'manifest' => $manifest,
        ]);

        $this->audit->record(
            $actor,
            'Created Backup',
            AuditActionType::Backup,
            targetType: 'backup',
            targetId: $backup->id,
            metadata: ['type' => $type, 'label' => $backup->label],
        );

        app(\App\Services\Portal\PortalNotificationService::class)
            ->backupCompleted($backup->label, $actor, $backup->id);

        return $backup;
    }

    /**
     * @return array{data: array<string, list<array<string, mixed>>>, summary: list<array{name: string, rows: int}>}
     */
    protected function exportTables(): array
    {
        $data = [];
        $summary = [];

        foreach (self::FULL_TABLES as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $rows = DB::table($table)->get()->map(fn ($row) => (array) $row)->all();
            $data[$table] = $rows;
            $summary[] = [
                'name' => $table,
                'rows' => count($rows),
            ];
        }

        return compact('data', 'summary');
    }

    /**
     * @return list<array{relative: string, absolute: string, size: int}>
     */
    protected function collectMediaFiles(): array
    {
        $files = [];
        $publicRoot = storage_path('app/public');

        foreach (self::MEDIA_DIRECTORIES as $directory) {
            $absoluteDir = $publicRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $directory);
            if (! File::isDirectory($absoluteDir)) {
                continue;
            }

            foreach (File::allFiles($absoluteDir) as $file) {
                $absolute = $file->getPathname();
                $relative = ltrim(str_replace('\\', '/', substr($absolute, strlen($publicRoot))), '/');
                $files[] = [
                    'relative' => $relative,
                    'absolute' => $absolute,
                    'size' => $file->getSize(),
                ];
            }
        }

        return $files;
    }

    protected function electionResultsPayload(): array
    {
        return \App\Models\Election::query()
            ->with(['votes.candidate', 'votes.category'])
            ->get()
            ->map(fn ($e) => [
                'election' => $e->only(['id', 'title', 'status', 'integrity_hash', 'results_locked']),
                'vote_totals_by_candidate' => $e->votes
                    ->groupBy('candidate_id')
                    ->map(fn ($votes, $candidateId) => [
                        'candidate_id' => (int) $candidateId,
                        'candidate' => $votes->first()?->candidate?->display_name,
                        'category' => $votes->first()?->category?->name,
                        'votes' => $votes->count(),
                    ])
                    ->values()
                    ->all(),
                'total_ballots' => $e->votes->count(),
                'anonymized' => true,
                'note' => 'Individual voter identities are excluded from election result backups.',
            ])
            ->all();
    }

    protected function studentDataPayload(): array
    {
        return User::query()
            ->where('role', UserRole::Student)
            ->get(['id', 'account_id', 'name', 'email', 'grade_level', 'section', 'student_status', 'is_active'])
            ->all();
    }

    protected function adminAccountsPayload(): array
    {
        return User::query()
            ->whereIn('role', [UserRole::Admin, UserRole::SuperAdmin])
            ->with('staffRole')
            ->get(['id', 'account_id', 'name', 'email', 'role', 'staff_role_id', 'is_active'])
            ->all();
    }
}

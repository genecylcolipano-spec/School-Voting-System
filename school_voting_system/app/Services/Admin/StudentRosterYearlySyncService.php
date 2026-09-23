<?php

namespace App\Services\Admin;

use App\Enums\StudentStatus;
use App\Enums\UserRole;
use App\Models\AllowedStudent;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StudentRosterYearlySyncService
{
    public const CACHE_TTL_SECONDS = 1800;

    public const SAMPLE_LIMIT = 8;

    public function __construct(
        protected InstitutionalRosterImportService $import,
        protected UserAccountLifecycleService $lifecycle,
    ) {}

    public static function suggestedSchoolYear(?Carbon $now = null): string
    {
        $now ??= now();
        $start = $now->month >= 6 ? $now->year : $now->year - 1;

        return $start.'-'.($start + 1);
    }

    public static function normalizeSchoolYear(string $value): string
    {
        $value = trim(str_replace(['–', '—'], '-', $value));

        if (! preg_match('/^(\d{4})-(\d{4})$/', $value, $matches)) {
            throw new InvalidArgumentException('School year must look like 2026-2027.');
        }

        $start = (int) $matches[1];
        $end = (int) $matches[2];

        if ($end !== $start + 1) {
            throw new InvalidArgumentException('School year end must be one year after the start.');
        }

        return $start.'-'.$end;
    }

    public function cacheKey(int $userId): string
    {
        return 'student-year-sync.'.$userId;
    }

    /**
     * @return array<string, mixed>
     */
    public function stashPreview(int $userId, UploadedFile $file, string $schoolYear, bool $archiveMissing): array
    {
        $schoolYear = self::normalizeSchoolYear($schoolYear);
        $incoming = $this->incomingRows($file);
        $plan = [
            'school_year' => $schoolYear,
            'archive_missing' => $archiveMissing,
            'incoming' => $incoming,
        ];

        Cache::put($this->cacheKey($userId), $plan, self::CACHE_TTL_SECONDS);

        return $this->previewFromPlan($plan);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function cachedPreview(int $userId): ?array
    {
        $plan = Cache::get($this->cacheKey($userId));

        if (! is_array($plan) || ! isset($plan['incoming'], $plan['school_year'])) {
            return null;
        }

        return $this->previewFromPlan($plan);
    }

    public function forget(int $userId): void
    {
        Cache::forget($this->cacheKey($userId));
    }

    /**
     * @return array<string, mixed>
     */
    public function applyCachedPlan(int $userId): array
    {
        $plan = Cache::get($this->cacheKey($userId));

        if (! is_array($plan) || ! isset($plan['incoming'], $plan['school_year'])) {
            throw new InvalidArgumentException('The yearly sync preview expired. Upload the roster again.');
        }

        $result = $this->apply($plan);
        $this->forget($userId);

        return $result;
    }

    /**
     * @param  array{school_year: string, archive_missing: bool, incoming: array<string, array<string, string|null>>}  $plan
     * @return array<string, mixed>
     */
    public function apply(array $plan): array
    {
        $preview = $this->previewFromPlan($plan);

        DB::transaction(function () use ($plan) {
            $now = now();
            $schoolYear = $plan['school_year'];

            foreach ($plan['incoming'] as $accountId => $row) {
                $roster = AllowedStudent::query()->firstOrNew(['account_id' => $accountId]);
                $roster->fill([
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'grade_level' => $row['grade_level'],
                    'section' => $row['section'],
                    'school_year' => $schoolYear,
                    'archived_at' => null,
                ]);
                $roster->save();

                $this->syncRegisteredUser($roster, restore: true);
            }

            if (! ($plan['archive_missing'] ?? false)) {
                return;
            }

            $keep = array_keys($plan['incoming']);

            AllowedStudent::query()
                ->whereNull('archived_at')
                ->whereNotIn('account_id', $keep)
                ->orderBy('account_id')
                ->each(function (AllowedStudent $roster) use ($now) {
                    $roster->forceFill(['archived_at' => $now])->save();

                    $user = $roster->registeredUser;
                    if ($user instanceof User && $user->isStudent() && ! $user->isArchived()) {
                        $this->lifecycle->archive($user);
                    }
                });
        });

        return $preview;
    }

    public function syncRegisteredUser(AllowedStudent $roster, bool $restore = false): void
    {
        $user = User::query()
            ->where('account_id', $roster->account_id)
            ->where('role', UserRole::Student)
            ->first();

        if (! $user) {
            return;
        }

        $fullName = trim($roster->first_name.' '.$roster->last_name);
        $shouldRestore = $restore && $user->isArchived();

        $user->fill([
            'name' => $fullName !== '' ? $fullName : $user->name,
            'grade_level' => $roster->grade_level,
            'section' => $roster->section,
            'school_year' => $roster->school_year,
        ]);

        if ($shouldRestore) {
            $this->lifecycle->restore($user);
            $user->student_status = StudentStatus::Enrolled;
        }

        $user->save();
    }

    /**
     * @return array<string, array{first_name: string, last_name: string, grade_level: ?string, section: ?string}>
     */
    protected function incomingRows(UploadedFile $file): array
    {
        $rows = $this->import->parseRosterRows($file, ['account_id', 'first_name', 'last_name', 'grade_level', 'section']);

        if ($rows === []) {
            throw new InvalidArgumentException('The CSV file is empty or has no data rows.');
        }

        if (count($rows) > InstitutionalRosterImportService::MAX_ROWS) {
            throw new InvalidArgumentException('The CSV file exceeds the maximum of '.InstitutionalRosterImportService::MAX_ROWS.' rows.');
        }

        $incoming = [];
        $seen = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $accountId = trim((string) ($row['account_id'] ?? ''));
            $firstName = trim((string) ($row['first_name'] ?? ''));
            $lastName = trim((string) ($row['last_name'] ?? ''));

            if ($accountId === '') {
                throw new InvalidArgumentException('Row '.$line.': Account ID is required.');
            }

            if (isset($seen[$accountId])) {
                throw new InvalidArgumentException('Duplicate account ID '.$accountId.' (first seen on row '.$seen[$accountId].').');
            }

            if ($firstName === '' || $lastName === '') {
                throw new InvalidArgumentException('Row '.$line.': First name and last name are required.');
            }

            $seen[$accountId] = $line;
            $incoming[$accountId] = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'grade_level' => $this->import->nullableString($row['grade_level'] ?? null),
                'section' => $this->import->nullableString($row['section'] ?? null),
            ];
        }

        return $incoming;
    }

    /**
     * @param  array{school_year: string, archive_missing?: bool, incoming: array<string, array<string, string|null>>}  $plan
     * @return array<string, mixed>
     */
    protected function previewFromPlan(array $plan): array
    {
        $schoolYear = $plan['school_year'];
        $archiveMissing = (bool) ($plan['archive_missing'] ?? false);
        $incoming = $plan['incoming'];

        $existing = AllowedStudent::query()
            ->with('registeredUser:id,account_id,role,email,archived_at')
            ->get()
            ->keyBy('account_id');

        $created = [];
        $updated = [];
        $restored = [];
        $unchanged = [];

        foreach ($incoming as $accountId => $row) {
            $roster = $existing->get($accountId);
            $sample = $this->sampleRow($accountId, $row, $roster, $schoolYear);

            if (! $roster) {
                $created[] = $sample;
                continue;
            }

            if ($roster->archived_at !== null) {
                $restored[] = $sample;
                continue;
            }

            if ($this->rosterNeedsUpdate($roster, $row, $schoolYear)) {
                $updated[] = $sample;
                continue;
            }

            $unchanged[] = $sample;
        }

        $archived = [];
        if ($archiveMissing) {
            foreach ($existing as $roster) {
                if ($roster->archived_at !== null || isset($incoming[$roster->account_id])) {
                    continue;
                }

                $archived[] = $this->sampleRow($roster->account_id, [
                    'first_name' => $roster->first_name,
                    'last_name' => $roster->last_name,
                    'grade_level' => $roster->grade_level,
                    'section' => $roster->section,
                ], $roster, $roster->school_year);
            }
        }

        return [
            'school_year' => $schoolYear,
            'archive_missing' => $archiveMissing,
            'counts' => [
                'incoming' => count($incoming),
                'created' => count($created),
                'updated' => count($updated),
                'restored' => count($restored),
                'unchanged' => count($unchanged),
                'archived' => count($archived),
            ],
            'created' => array_slice($created, 0, self::SAMPLE_LIMIT),
            'updated' => array_slice($updated, 0, self::SAMPLE_LIMIT),
            'restored' => array_slice($restored, 0, self::SAMPLE_LIMIT),
            'archived' => array_slice($archived, 0, self::SAMPLE_LIMIT),
        ];
    }

    /**
     * @param  array<string, string|null>  $row
     * @return array<string, mixed>
     */
    protected function sampleRow(string $accountId, array $row, ?AllowedStudent $roster, ?string $schoolYear): array
    {
        $user = $roster?->registeredUser;

        return [
            'account_id' => $accountId,
            'name' => trim(($row['first_name'] ?? '').' '.($row['last_name'] ?? '')),
            'from' => $roster ? $this->classLabel($roster->grade_level, $roster->section, $roster->school_year) : '—',
            'to' => $this->classLabel($row['grade_level'] ?? null, $row['section'] ?? null, $schoolYear),
            'has_account' => $user instanceof User && $user->isStudent(),
        ];
    }

    /**
     * @param  array<string, string|null>  $row
     */
    protected function rosterNeedsUpdate(AllowedStudent $roster, array $row, string $schoolYear): bool
    {
        return $roster->first_name !== $row['first_name']
            || $roster->last_name !== $row['last_name']
            || (string) $roster->grade_level !== (string) $row['grade_level']
            || (string) $roster->section !== (string) $row['section']
            || (string) $roster->school_year !== $schoolYear;
    }

    protected function classLabel(?string $grade, ?string $section, ?string $schoolYear): string
    {
        $parts = array_values(array_filter([
            $schoolYear ? 'SY '.$schoolYear : null,
            filled($grade) ? 'Grade '.$grade : null,
            filled($section) ? 'Section '.$section : null,
        ]));

        return $parts === [] ? 'Unassigned' : implode(' · ', $parts);
    }
}

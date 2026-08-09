<?php

namespace App\Services\Admin;

use App\Models\AllowedStudent;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;

class AllowedStudentImportService
{
    public const MAX_ROWS = 5000;

    /**
     * @var list<string>
     */
    public const COLUMNS = [
        'account_id',
        'first_name',
        'last_name',
        'grade_level',
        'section',
    ];

    /**
     * @return array{created: int, updated: int, errors: list<array{row: int, account_id: string, message: string}>}
     */
    public function import(UploadedFile $file): array
    {
        $rows = $this->parseCsv($file);

        if ($rows === []) {
            throw new InvalidArgumentException('The CSV file is empty or has no data rows.');
        }

        if (count($rows) > self::MAX_ROWS) {
            throw new InvalidArgumentException('The CSV file exceeds the maximum of '.self::MAX_ROWS.' rows.');
        }

        $created = 0;
        $updated = 0;
        $errors = [];
        $seenAccountIds = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $accountId = trim((string) ($row['account_id'] ?? ''));

            if ($accountId === '') {
                $errors[] = ['row' => $line, 'account_id' => '—', 'message' => 'Account ID is required.'];
                continue;
            }

            if (isset($seenAccountIds[$accountId])) {
                $errors[] = [
                    'row' => $line,
                    'account_id' => $accountId,
                    'message' => 'Duplicate account ID in this file (first seen on row '.$seenAccountIds[$accountId].').',
                ];
                continue;
            }

            $seenAccountIds[$accountId] = $line;

            $firstName = trim((string) ($row['first_name'] ?? ''));
            $lastName = trim((string) ($row['last_name'] ?? ''));

            if ($firstName === '' || $lastName === '') {
                $errors[] = [
                    'row' => $line,
                    'account_id' => $accountId,
                    'message' => 'First name and last name are required.',
                ];
                continue;
            }

            $attributes = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'grade_level' => $this->nullableString($row['grade_level'] ?? null),
                'section' => $this->nullableString($row['section'] ?? null),
            ];

            $existing = AllowedStudent::query()->where('account_id', $accountId)->first();

            if ($existing?->is_registered) {
                $errors[] = [
                    'row' => $line,
                    'account_id' => $accountId,
                    'message' => 'This student has already registered and cannot be overwritten.',
                ];
                continue;
            }

            AllowedStudent::query()->updateOrCreate(
                ['account_id' => $accountId],
                $attributes,
            );

            $existing ? $updated++ : $created++;
        }

        return compact('created', 'updated', 'errors');
    }

    /**
     * @return list<array<string, string|null>>
     */
    protected function parseCsv(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            throw new InvalidArgumentException('Unable to read the uploaded CSV file.');
        }

        $headerRow = fgetcsv($handle);

        if ($headerRow === false) {
            fclose($handle);

            return [];
        }

        $headerRow[0] = $this->stripBom((string) ($headerRow[0] ?? ''));
        $headers = $this->normalizeHeaders($headerRow);
        $rows = [];

        while (($data = fgetcsv($handle)) !== false) {
            if ($this->isBlankRow($data)) {
                continue;
            }

            $row = [];

            foreach ($headers as $index => $header) {
                $row[$header] = isset($data[$index]) ? trim((string) $data[$index]) : null;
            }

            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    /**
     * @param  list<string|null>  $headers
     * @return list<string>
     */
    protected function normalizeHeaders(array $headers): array
    {
        $map = [
            'student_id' => 'account_id',
            'id' => 'account_id',
            'firstname' => 'first_name',
            'first' => 'first_name',
            'lastname' => 'last_name',
            'last' => 'last_name',
            'grade' => 'grade_level',
        ];

        $normalized = [];

        foreach ($headers as $header) {
            $key = strtolower(trim((string) $header));
            $key = str_replace([' ', '-'], '_', $key);
            $normalized[] = $map[$key] ?? $key;
        }

        if (! in_array('account_id', $normalized, true)) {
            throw new InvalidArgumentException('CSV must include an account_id column.');
        }

        return $normalized;
    }

    /**
     * @param  list<string|null>  $row
     */
    protected function isBlankRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    protected function stripBom(string $value): string
    {
        return preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
    }

    protected function nullableString(mixed $value): ?string
    {
        $string = trim((string) ($value ?? ''));

        return $string === '' ? null : $string;
    }
}

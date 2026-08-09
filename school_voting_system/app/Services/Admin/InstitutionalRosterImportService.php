<?php

namespace App\Services\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;

class InstitutionalRosterImportService
{
    public const MAX_ROWS = 5000;

    /**
     * @param  class-string<Model>  $modelClass
     * @param  list<string>  $columns
     * @param  callable(array<string, mixed>): array<string, mixed>  $mapAttributes
     * @return array{created: int, updated: int, errors: list<array{row: int, account_id: string, message: string}>}
     */
    public function import(UploadedFile $file, string $modelClass, array $columns, callable $mapAttributes): array
    {
        $rows = $this->parseCsv($file, $columns);

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

            try {
                $attributes = $mapAttributes($row);
            } catch (InvalidArgumentException $exception) {
                $errors[] = [
                    'row' => $line,
                    'account_id' => $accountId,
                    'message' => $exception->getMessage(),
                ];
                continue;
            }

            /** @var Model|null $existing */
            $existing = $modelClass::query()->where('account_id', $accountId)->first();

            if ($existing?->getAttribute('is_registered')) {
                $errors[] = [
                    'row' => $line,
                    'account_id' => $accountId,
                    'message' => 'This record has already registered and cannot be overwritten.',
                ];
                continue;
            }

            $modelClass::query()->updateOrCreate(
                ['account_id' => $accountId],
                $attributes,
            );

            $existing ? $updated++ : $created++;
        }

        return compact('created', 'updated', 'errors');
    }

    /**
     * @param  list<string>  $columns
     * @return list<array<string, string|null>>
     */
    protected function parseCsv(UploadedFile $file, array $columns): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            throw new InvalidArgumentException('Unable to read the uploaded file.');
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);

            return [];
        }

        $header = array_map(fn ($value) => strtolower(trim((string) $value)), $header);
        $aliases = [
            'student_id' => 'account_id',
            'faculty_id' => 'account_id',
            'employee_id' => 'account_id',
            'id' => 'account_id',
            'firstname' => 'first_name',
            'lastname' => 'last_name',
            'grade' => 'grade_level',
            'office' => 'department',
            'office_department' => 'department',
            'job_title' => 'position',
            'title' => 'position',
        ];

        $map = [];
        foreach ($header as $index => $column) {
            $normalized = $aliases[$column] ?? $column;
            if (in_array($normalized, $columns, true)) {
                $map[$normalized] = $index;
            }
        }

        foreach (['account_id', 'first_name', 'last_name'] as $required) {
            if (! array_key_exists($required, $map)) {
                fclose($handle);
                throw new InvalidArgumentException('CSV must include columns: '.implode(', ', $columns));
            }
        }

        $rows = [];
        while (($data = fgetcsv($handle)) !== false) {
            if ($this->rowIsEmpty($data)) {
                continue;
            }

            $row = [];
            foreach ($columns as $column) {
                $row[$column] = isset($map[$column], $data[$map[$column]])
                    ? trim((string) $data[$map[$column]])
                    : null;
            }
            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    /**
     * @param  list<string|null>  $data
     */
    protected function rowIsEmpty(array $data): bool
    {
        foreach ($data as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    public function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}

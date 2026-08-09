<?php

namespace App\Services\Auth;

use App\Models\AllowedAdministrator;
use App\Models\AllowedFaculty;
use App\Models\AllowedStudent;
use App\Models\User;
use App\Support\RosterMatch;
use Illuminate\Validation\ValidationException;

class RosterVerificationService
{
    public const NOT_FOUND_MESSAGE = 'Account ID and Name combination not found in official records.';

    public const ALREADY_REGISTERED_MESSAGE = 'An account for this ID has already been registered.';

    public function findMatchingRoster(string $accountId, string $firstName, string $lastName): ?RosterMatch
    {
        $accountKey = $this->normalizeKey($accountId);
        $firstKey = $this->normalizeKey($firstName);
        $lastKey = $this->normalizeKey($lastName);

        $student = AllowedStudent::query()
            ->active()
            ->get()
            ->first(fn (AllowedStudent $row) => $this->matchesIdentity($row, $accountKey, $firstKey, $lastKey));

        if ($student) {
            return RosterMatch::fromStudent($student);
        }

        $faculty = AllowedFaculty::query()
            ->active()
            ->get()
            ->first(fn (AllowedFaculty $row) => $this->matchesIdentity($row, $accountKey, $firstKey, $lastKey));

        if ($faculty) {
            return RosterMatch::fromFaculty($faculty);
        }

        $administrator = AllowedAdministrator::query()
            ->active()
            ->get()
            ->first(fn (AllowedAdministrator $row) => $this->matchesIdentity($row, $accountKey, $firstKey, $lastKey));

        if ($administrator) {
            return RosterMatch::fromAdministrator($administrator);
        }

        return null;
    }

    public function assertEligibleForRegistration(
        string $accountId,
        string $firstName,
        string $lastName,
    ): RosterMatch {
        $match = $this->findMatchingRoster($accountId, $firstName, $lastName);

        if (! $match) {
            throw ValidationException::withMessages([
                'account_id' => [self::NOT_FOUND_MESSAGE],
            ]);
        }

        if ($match->record->is_registered) {
            throw ValidationException::withMessages([
                'account_id' => [self::ALREADY_REGISTERED_MESSAGE],
            ]);
        }

        if (User::query()->where('account_id', $match->accountId)->exists()) {
            throw ValidationException::withMessages([
                'account_id' => [self::ALREADY_REGISTERED_MESSAGE],
            ]);
        }

        return $match;
    }

    public function normalizeKey(?string $value): string
    {
        $collapsed = preg_replace('/\s+/', ' ', trim((string) $value));

        return mb_strtolower($collapsed ?? '');
    }

    protected function matchesIdentity(object $row, string $accountKey, string $firstKey, string $lastKey): bool
    {
        return $this->normalizeKey($row->account_id) === $accountKey
            && $this->normalizeKey($row->first_name) === $firstKey
            && $this->normalizeKey($row->last_name) === $lastKey;
    }
}

<?php

namespace App\Services\Election;

use App\Models\Election;

class ElectionIntegrityService
{
    /**
     * @return array{
     *     has_hash: bool,
     *     valid: bool,
     *     stored_hash: ?string,
     *     computed_hash: string,
     *     message: string
     * }
     */
    public function verify(Election $election): array
    {
        $computed = $election->computeIntegrityHash();
        $stored = $election->integrity_hash;

        if (! $stored) {
            return [
                'has_hash' => false,
                'valid' => false,
                'stored_hash' => null,
                'computed_hash' => $computed,
                'message' => 'No integrity hash has been recorded for this election yet.',
            ];
        }

        $valid = hash_equals($stored, $computed);

        return [
            'has_hash' => true,
            'valid' => $valid,
            'stored_hash' => $stored,
            'computed_hash' => $computed,
            'message' => $valid
                ? 'Vote records match the stored integrity hash.'
                : 'Integrity mismatch detected. Vote records may have changed since the hash was recorded.',
        ];
    }

    public function refreshAndStore(Election $election): string
    {
        return $election->refreshIntegrityHash();
    }
}

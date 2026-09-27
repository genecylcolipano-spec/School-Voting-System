<?php

namespace App\Casts;

use App\Enums\AuditActionType;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Reads known audit types as the enum. Unknown legacy values (e.g. "general") stay loadable.
 *
 * @implements CastsAttributes<AuditActionType|null, AuditActionType|string|null>
 */
class SafeAuditActionType implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?AuditActionType
    {
        if ($value === null || $value === '') {
            return null;
        }

        return AuditActionType::tryFrom((string) $value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value instanceof AuditActionType) {
            return $value->value;
        }

        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }
}

<?php

namespace App\Models;

use App\Enums\RosterRegistrationStatus;
use App\Models\Concerns\HasRosterRegistrationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AllowedFaculty extends Model
{
    use HasRosterRegistrationStatus;

    protected $table = 'allowed_faculty';

    protected $fillable = [
        'account_id',
        'first_name',
        'last_name',
        'department',
        'position',
        'is_registered',
        'registration_status',
        'enrollment_pending_at',
        'registered_at',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'is_registered' => 'boolean',
            'registration_status' => RosterRegistrationStatus::class,
            'enrollment_pending_at' => 'datetime',
            'registered_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function registeredUser(): HasOne
    {
        return $this->hasOne(User::class, 'account_id', 'account_id');
    }
}

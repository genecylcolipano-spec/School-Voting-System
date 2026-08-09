<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AllowedFaculty extends Model
{
    protected $table = 'allowed_faculty';

    protected $fillable = [
        'account_id',
        'first_name',
        'last_name',
        'department',
        'position',
        'is_registered',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'is_registered' => 'boolean',
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

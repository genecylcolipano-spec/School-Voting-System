<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = ['key', 'label', 'category'];

    public function staffRoles(): BelongsToMany
    {
        return $this->belongsToMany(StaffRole::class, 'staff_role_permission');
    }
}

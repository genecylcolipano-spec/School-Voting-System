<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AdminVerificationRequest extends Model
{
    protected $fillable = [
        'assigned_to',
        'subject_type',
        'subject_id',
        'title',
        'status',
        'notes',
    ];

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}

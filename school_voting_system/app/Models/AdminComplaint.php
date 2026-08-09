<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminComplaint extends Model
{
    protected $fillable = [
        'assigned_to',
        'election_id',
        'title',
        'description',
        'status',
        'priority',
    ];

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }
}

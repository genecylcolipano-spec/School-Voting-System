<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'election_id',
        'grade_levels',
        'sections',
        'strands',
        'turnout_target',
        'assigned_by',
    ];

    protected function casts(): array
    {
        return [
            'grade_levels' => 'array',
            'sections' => 'array',
            'strands' => 'array',
            'turnout_target' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function election(): BelongsTo
    {
        // Keep resolving soft-deleted elections so admin scope/assignment
        // does not silently become empty after an election is archived/deleted.
        return $this->belongsTo(Election::class)->withTrashed();
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}

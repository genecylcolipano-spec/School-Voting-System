<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TalentEventEntryView extends Model
{
    protected $fillable = [
        'talent_event_id',
        'talent_event_entry_id',
        'user_id',
        'watched_at',
    ];

    protected function casts(): array
    {
        return [
            'watched_at' => 'datetime',
        ];
    }

    public function talentEvent(): BelongsTo
    {
        return $this->belongsTo(TalentEvent::class);
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(TalentEventEntry::class, 'talent_event_entry_id');
    }

    public function viewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

<?php

namespace App\Models;

use App\Enums\EventStatus;
use App\Models\Concerns\HasEventImage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasEventImage;
    use SoftDeletes;
    protected $fillable = [
        'title',
        'slug',
        'description',
        'image_path',
        'image_variants',
        'event_date',
        'venue',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'datetime',
            'status' => EventStatus::class,
            'image_variants' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query
            ->where('status', EventStatus::Scheduled)
            ->where('event_date', '>=', now());
    }
}

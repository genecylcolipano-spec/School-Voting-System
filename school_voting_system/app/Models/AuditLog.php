<?php

namespace App\Models;

use App\Enums\AuditActionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'admin_name',
        'admin_role',
        'action',
        'action_type',
        'target_type',
        'target_id',
        'metadata',
        'ip_address',
        'user_agent',
        'device_name',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'action_type' => AuditActionType::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

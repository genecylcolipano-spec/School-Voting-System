<?php

namespace App\Models;

use App\Services\SuperAdmin\BackupService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemBackup extends Model
{
    protected $fillable = [
        'label',
        'type',
        'file_path',
        'file_size',
        'created_by',
        'completed_at',
        'status',
        'manifest',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'manifest' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function formattedSize(): string
    {
        $bytes = (int) $this->file_size;

        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 2).' GB';
        }

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2).' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            BackupService::TYPE_FULL => 'Full System',
            BackupService::TYPE_ELECTION_RESULTS => 'Election Results',
            BackupService::TYPE_STUDENT_DATA => 'Student Data',
            BackupService::TYPE_ADMIN_ACCOUNTS => 'Admin Accounts',
            default => str($this->type)->replace('_', ' ')->title()->toString(),
        };
    }

    public function isFullSystem(): bool
    {
        return $this->type === BackupService::TYPE_FULL;
    }

    public function includedTableCount(): int
    {
        return count($this->manifest['tables'] ?? []);
    }

    public function includedFileCount(): int
    {
        return count($this->manifest['files'] ?? []);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AnnouncementAttachment extends Model
{
    protected $fillable = [
        'announcement_id',
        'original_name',
        'path',
        'mime_type',
        'size_bytes',
        'download_count',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'download_count' => 'integer',
        ];
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function formattedSize(): string
    {
        $bytes = (int) $this->size_bytes;

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf'
            || str_ends_with(strtolower($this->original_name), '.pdf');
    }

    public function typeLabel(): string
    {
        if ($this->isPdf()) {
            return 'PDF';
        }

        if ($this->isImage()) {
            $ext = strtoupper(pathinfo($this->original_name, PATHINFO_EXTENSION) ?: 'IMG');

            return in_array($ext, ['JPG', 'JPEG', 'PNG'], true) ? $ext : 'Image';
        }

        return strtoupper(pathinfo($this->original_name, PATHINFO_EXTENSION) ?: 'File');
    }

    public function publicUrl(): ?string
    {
        if (! $this->path || ! Storage::disk('public')->exists($this->path)) {
            return null;
        }

        return asset('storage/'.$this->path);
    }

    public function deleteFile(): void
    {
        if ($this->path) {
            Storage::disk('public')->delete($this->path);
        }
    }
}

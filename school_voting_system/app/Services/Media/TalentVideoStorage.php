<?php

namespace App\Services\Media;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Throwable;

class TalentVideoStorage
{
    public const DISK = 'private';

    public const FALLBACK_DISK = 'local';

    public const DIRECTORY = 'talent/videos';

    public function store(?UploadedFile $file, string $directory = self::DIRECTORY): ?string
    {
        if ($file === null) {
            return null;
        }

        return $file->store($directory, self::DISK);
    }

    public function exists(?string $path): bool
    {
        return $this->resolveDisk($path) !== null;
    }

    public function delete(?string $path): void
    {
        if (! $this->isStoredPath($path)) {
            return;
        }

        Storage::disk(self::DISK)->delete($path);

        if (self::DISK !== self::FALLBACK_DISK) {
            Storage::disk(self::FALLBACK_DISK)->delete($path);
        }
    }

    public function respond(string $path, bool $download = false): Response
    {
        $diskName = $this->resolveDisk($path);
        abort_unless($diskName !== null, 404);

        $disk = Storage::disk($diskName);
        $filename = $this->downloadName($path);
        $absolutePath = $this->localAbsolutePath($disk, $path);

        if ($absolutePath !== null) {
            if ($download) {
                return response()->download($absolutePath, $filename);
            }

            $response = response()->file($absolutePath);
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $filename);

            return $response;
        }

        if ($this->diskProvidesTemporaryUrls($disk)) {
            try {
                return redirect()->away($disk->temporaryUrl(
                    $path,
                    now()->addMinutes(60),
                    $this->temporaryUrlOptions($filename, $download),
                ));
            } catch (Throwable) {
                // Some S3-compatible providers reject signed-URL options; stream instead.
            }
        }

        return $download
            ? $disk->download($path, $filename)
            : $disk->response($path, $filename, [], 'inline');
    }

    protected function resolveDisk(?string $path): ?string
    {
        if (! $this->isStoredPath($path)) {
            return null;
        }

        if (Storage::disk(self::DISK)->exists($path)) {
            return self::DISK;
        }

        if (self::DISK !== self::FALLBACK_DISK && Storage::disk(self::FALLBACK_DISK)->exists($path)) {
            return self::FALLBACK_DISK;
        }

        return null;
    }

    protected function isStoredPath(?string $path): bool
    {
        return filled($path) && ! str_starts_with($path, 'http://') && ! str_starts_with($path, 'https://');
    }

    protected function localAbsolutePath(Filesystem $disk, string $path): ?string
    {
        if (! method_exists($disk, 'path')) {
            return null;
        }

        try {
            $absolute = $disk->path($path);
        } catch (Throwable) {
            return null;
        }

        return is_string($absolute) && is_file($absolute) ? $absolute : null;
    }

    protected function diskProvidesTemporaryUrls(Filesystem $disk): bool
    {
        return method_exists($disk, 'providesTemporaryUrls') && $disk->providesTemporaryUrls();
    }

    /**
     * @return array<string, string>
     */
    protected function temporaryUrlOptions(string $filename, bool $download): array
    {
        $disposition = ($download ? 'attachment' : 'inline').'; filename="'.$filename.'"';

        return [
            'ResponseContentDisposition' => $disposition,
        ];
    }

    protected function downloadName(string $path): string
    {
        $name = basename($path);
        $safe = preg_replace('/[^\w.\-]+/', '_', $name) ?: 'performance.mp4';

        return $safe;
    }
}

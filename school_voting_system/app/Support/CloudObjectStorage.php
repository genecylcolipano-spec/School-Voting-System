<?php

namespace App\Support;

/**
 * Laravel Cloud injects FILESYSTEM_DISK + LARAVEL_CLOUD_DISK_CONFIG (not AWS_BUCKET).
 * This app stores photos on "public" and talent videos on "private".
 * Remap those names onto the injected Cloud disk so uploads survive deploys.
 */
class CloudObjectStorage
{
    public static function bindAppDisks(): void
    {
        $payload = $_SERVER['LARAVEL_CLOUD_DISK_CONFIG']
            ?? $_ENV['LARAVEL_CLOUD_DISK_CONFIG']
            ?? env('LARAVEL_CLOUD_DISK_CONFIG');

        if (! is_string($payload) || $payload === '') {
            return;
        }

        $disks = json_decode($payload, true);
        if (! is_array($disks) || $disks === []) {
            return;
        }

        $default = null;
        foreach ($disks as $disk) {
            if (! is_array($disk)) {
                continue;
            }

            if ($disk['is_default'] ?? false) {
                $default = $disk;
                break;
            }

            $default ??= $disk;
        }

        $sourceName = is_array($default) ? ($default['disk'] ?? null) : null;
        if (! is_string($sourceName) || $sourceName === '') {
            return;
        }

        $source = config('filesystems.disks.'.$sourceName);
        if (! is_array($source) || ($source['driver'] ?? null) !== 's3') {
            return;
        }

        $rootName = $sourceName;
        if (in_array($rootName, ['public', 'private'], true)) {
            $rootName = 'cloud';
            config(['filesystems.disks.cloud' => $source]);
        }

        config([
            'filesystems.disks.public' => $source,
            'filesystems.disks.private' => [
                'driver' => 'scoped',
                'disk' => $rootName,
                'prefix' => 'private',
            ],
        ]);
    }
}

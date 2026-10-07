<?php

namespace Tests\Unit\Support;

use App\Support\CloudObjectStorage;
use Tests\TestCase;

class CloudObjectStorageTest extends TestCase
{
    /** @var array<string, mixed> */
    protected array $originalDisks = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDisks = [
            'public' => config('filesystems.disks.public'),
            'private' => config('filesystems.disks.private'),
            'default' => config('filesystems.default'),
        ];
    }

    protected function tearDown(): void
    {
        unset($_SERVER['LARAVEL_CLOUD_DISK_CONFIG'], $_ENV['LARAVEL_CLOUD_DISK_CONFIG']);

        config([
            'filesystems.disks.public' => $this->originalDisks['public'],
            'filesystems.disks.private' => $this->originalDisks['private'],
            'filesystems.default' => $this->originalDisks['default'],
        ]);

        parent::tearDown();
    }

    public function test_local_disks_stay_local_without_cloud_config(): void
    {
        unset($_SERVER['LARAVEL_CLOUD_DISK_CONFIG'], $_ENV['LARAVEL_CLOUD_DISK_CONFIG']);

        CloudObjectStorage::bindAppDisks();

        $this->assertSame('local', config('filesystems.disks.public.driver'));
        $this->assertSame('local', config('filesystems.disks.private.driver'));
    }

    public function test_cloud_disk_config_remaps_public_and_private(): void
    {
        config()->set('filesystems.disks.r2', [
            'driver' => 's3',
            'key' => 'cloud-key',
            'secret' => 'cloud-secret',
            'bucket' => 'svs-uploads',
            'url' => 'https://uploads.example.test',
            'endpoint' => 'https://s3.example.test',
            'region' => 'auto',
        ]);
        config()->set('filesystems.default', 'r2');

        $_SERVER['LARAVEL_CLOUD_DISK_CONFIG'] = json_encode([
            [
                'disk' => 'r2',
                'is_default' => true,
                'access_key_id' => 'cloud-key',
                'access_key_secret' => 'cloud-secret',
                'bucket' => 'svs-uploads',
                'url' => 'https://uploads.example.test',
                'endpoint' => 'https://s3.example.test',
            ],
        ]);

        CloudObjectStorage::bindAppDisks();

        $this->assertSame('s3', config('filesystems.disks.public.driver'));
        $this->assertSame('svs-uploads', config('filesystems.disks.public.bucket'));
        $this->assertSame('s3', config('filesystems.disks.private.driver'));
        $this->assertSame('svs-uploads', config('filesystems.disks.private.bucket'));
        $this->assertSame('private', config('filesystems.disks.private.root'));
    }

    public function test_cloud_disk_named_public_still_prefixes_private_videos(): void
    {
        config()->set('filesystems.disks.public', [
            'driver' => 's3',
            'key' => 'cloud-key',
            'secret' => 'cloud-secret',
            'bucket' => 'svs-uploads',
            'region' => 'auto',
        ]);

        $_SERVER['LARAVEL_CLOUD_DISK_CONFIG'] = json_encode([
            [
                'disk' => 'public',
                'is_default' => true,
                'bucket' => 'svs-uploads',
            ],
        ]);

        CloudObjectStorage::bindAppDisks();

        $this->assertSame('s3', config('filesystems.disks.public.driver'));
        $this->assertSame('s3', config('filesystems.disks.private.driver'));
        $this->assertSame('private', config('filesystems.disks.private.root'));
    }
}

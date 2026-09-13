<?php

namespace Tests\Unit\Support;

use App\Support\ImageDimensions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageDimensionsTest extends TestCase
{
    public function test_from_storage_path_reads_bytes_without_local_path(): void
    {
        Storage::fake('public');

        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        Storage::disk('public')->put('banners/pixel.png', $png);

        $this->assertSame(
            ['width' => 1, 'height' => 1],
            ImageDimensions::fromStoragePath('public', 'banners/pixel.png'),
        );
        $this->assertNull(ImageDimensions::fromStoragePath('public', 'banners/missing.png'));
    }
}

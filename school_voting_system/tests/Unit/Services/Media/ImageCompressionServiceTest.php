<?php

namespace Tests\Unit\Services\Media;

use App\Services\Media\ImageCompressionService;
use Illuminate\Support\Facades\Storage;
use Tests\Support\TestImageFactory;
use Tests\TestCase;

class ImageCompressionServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is required for image compression tests.');
        }
    }

    public function test_store_optimized_set_compresses_large_png_under_two_megabytes(): void
    {
        Storage::fake('public');

        $file = TestImageFactory::uncompressedPngUploadedFile();
        $this->assertGreaterThan(ImageCompressionService::MAX_STORED_BYTES, $file->getSize());

        $set = app(ImageCompressionService::class)->storeOptimizedSet($file, 'campaign-banners', false);

        $this->assertTrue(Storage::disk('public')->exists($set['path']));
        $this->assertLessThanOrEqual(
            ImageCompressionService::MAX_STORED_BYTES,
            Storage::disk('public')->size($set['path']),
        );
    }
}

<?php

namespace Tests\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TestImageFactory
{
    public static function landscapeUploadedFile(): UploadedFile
    {
        return self::jpegUploadedFile('landscape.jpg', 1920, 600);
    }

    public static function portraitUploadedFile(): UploadedFile
    {
        return self::jpegUploadedFile('portrait.jpg', 600, 900);
    }

    public static function squareUploadedFile(): UploadedFile
    {
        return self::jpegUploadedFile('square.jpg', 800, 800);
    }

    public static function jpegUploadedFile(string $name, int $width, int $height): UploadedFile
    {
        $path = self::writeTempJpeg($width, $height);

        return new UploadedFile($path, $name, 'image/jpeg', null, true);
    }

    public static function uploadedFile(string $name, int $width, int $height): UploadedFile
    {
        return self::jpegUploadedFile($name, $width, $height);
    }

    public static function storeLandscapeBanner(string $directory = 'campaign-banners'): string
    {
        return self::storeJpeg("{$directory}/landscape.jpg", 1920, 600);
    }

    public static function storePortraitBanner(string $directory = 'campaign-banners'): string
    {
        return self::storeJpeg("{$directory}/portrait.jpg", 600, 900);
    }

    public static function storeJpeg(string $storagePath, int $width, int $height, string $disk = 'public'): string
    {
        $temp = self::writeTempJpeg($width, $height);
        Storage::disk($disk)->put($storagePath, file_get_contents($temp));
        @unlink($temp);

        return $storagePath;
    }

    protected static function writeTempJpeg(int $width, int $height): string
    {
        $path = tempnam(sys_get_temp_dir(), 'jpg-');
        rename($path, $path .= '.jpg');
        self::writeMinimalJpeg($path, $width, $height);

        return $path;
    }

    public static function writeMinimalJpeg(string $path, int $width, int $height): void
    {
        $scan = pack('C*', 0xFF, 0xDA, 0x00, 0x08, 0x01, 0x01, 0x00, 0x00, 0x3F, 0x00, 0x37);
        $scan .= pack('C*', 0xFF, 0xD9);

        $quant = pack('C*',
            0xFF, 0xDB, 0x00, 0x43, 0x00,
            0x08, 0x06, 0x06, 0x07, 0x06, 0x05, 0x08, 0x07, 0x07, 0x07, 0x09, 0x09, 0x08, 0x0A, 0x0C,
            0x14, 0x0D, 0x0C, 0x0B, 0x0B, 0x0C, 0x19, 0x12, 0x13, 0x0F, 0x14, 0x1D, 0x1A, 0x1F, 0x1E, 0x1D,
            0x1A, 0x1C, 0x1C, 0x20, 0x24, 0x2E, 0x27, 0x20, 0x22, 0x2C, 0x23, 0x1C, 0x1C, 0x28, 0x37, 0x29,
            0x2C, 0x30, 0x31, 0x34, 0x34, 0x34, 0x1F, 0x27, 0x39, 0x3D, 0x38, 0x32, 0x3C, 0x2E, 0x33, 0x34,
            0x32,
        );

        $sof = pack('C*', 0xFF, 0xC0, 0x00, 0x11, 0x08);
        $sof .= pack('n', $height);
        $sof .= pack('n', $width);
        $sof .= pack('C*', 0x03, 0x01, 0x11, 0x00, 0x02, 0x11, 0x01, 0x03, 0x11, 0x01);

        $app0 = pack('C*',
            0xFF, 0xD8, 0xFF, 0xE0, 0x00, 0x10, 0x4A, 0x46, 0x49, 0x46, 0x00, 0x01, 0x01, 0x00, 0x00, 0x01,
            0x00, 0x01, 0x00, 0x00,
        );

        file_put_contents($path, $app0.$quant.$sof.$scan);
    }

    public static function storeBitmap(string $storagePath, int $width, int $height, string $disk = 'public'): string
    {
        $temp = tempnam(sys_get_temp_dir(), 'bmp-');
        rename($temp, $temp .= '.bmp');
        self::writeBitmap($temp, $width, $height);
        Storage::disk($disk)->put($storagePath, file_get_contents($temp));
        @unlink($temp);

        return $storagePath;
    }

    public static function writeBitmap(string $path, int $width, int $height): void
    {
        $rowSize = (int) ceil(($width * 3) / 4) * 4;
        $pixelDataSize = $rowSize * $height;
        $fileSize = 54 + $pixelDataSize;

        $handle = fopen($path, 'wb');

        fwrite($handle, 'BM');
        fwrite($handle, pack('V', $fileSize));
        fwrite($handle, pack('v', 0));
        fwrite($handle, pack('v', 0));
        fwrite($handle, pack('V', 54));
        fwrite($handle, pack('V', 40));
        fwrite($handle, pack('V', $width));
        fwrite($handle, pack('V', $height));
        fwrite($handle, pack('v', 1));
        fwrite($handle, pack('v', 24));
        fwrite($handle, pack('V', 0));
        fwrite($handle, pack('V', $pixelDataSize));
        fwrite($handle, pack('V', 2835));
        fwrite($handle, pack('V', 2835));
        fwrite($handle, pack('V', 0));
        fwrite($handle, pack('V', 0));
        fwrite($handle, str_repeat("\0", $pixelDataSize));
        fclose($handle);
    }
}

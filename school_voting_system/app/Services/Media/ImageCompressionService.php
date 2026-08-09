<?php

namespace App\Services\Media;

use App\Support\ImageDimensions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageCompressionService
{
    public const MAX_STORED_BYTES = 2_097_152;

    public const MAX_DIMENSION = 1920;

    public const MEDIUM_DIMENSION = 1280;

    public const THUMB_DIMENSION = 480;

    public const MOBILE_DIMENSION = 768;

    public const AVATAR_SIZE = 400;

    /**
     * Store a single optimized image (existing API — backward compatible).
     */
    public function storeOptimized(UploadedFile $file, string $directory): string
    {
        return $this->storeOptimizedSet($file, $directory)['path'];
    }

    /**
     * Center-crop to a square avatar, resize, and optimize for profile photos.
     */
    public function storeSquareAvatar(UploadedFile $file, string $directory = 'avatars', int $size = self::AVATAR_SIZE): string
    {
        $directory = trim($directory, '/');

        if (! extension_loaded('gd')) {
            return $file->store($directory, 'public');
        }

        $mime = strtolower((string) $file->getMimeType());
        $image = $this->loadImage($file->getRealPath(), $mime);

        if ($image === false) {
            return $file->store($directory, 'public');
        }

        $image = $this->cropCenterSquare($image, $size);
        [$binary, $extension] = $this->compressToMaxBytes($image, $mime);
        imagedestroy($image);

        $path = $directory.'/'.Str::uuid().'.'.$extension;
        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    /**
     * @param  \GdImage  $image
     * @return \GdImage
     */
    protected function cropCenterSquare($image, int $size)
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $side = min($width, $height);
        $srcX = (int) floor(($width - $side) / 2);
        $srcY = (int) floor(($height - $side) / 2);

        $square = imagecreatetruecolor($size, $size);
        imagealphablending($square, false);
        imagesavealpha($square, true);
        imagecopyresampled($square, $image, 0, 0, $srcX, $srcY, $size, $size, $side, $side);
        imagedestroy($image);

        return $square;
    }

    /**
     * Optimize an upload and generate responsive variants.
     *
     * @return array{
     *     path: string,
     *     medium_path: ?string,
     *     mobile_path: ?string,
     *     thumb_path: ?string,
     *     orientation: string,
     *     width: int,
     *     height: int
     * }
     */
    public function storeOptimizedSet(UploadedFile $file, string $directory, bool $withVariants = true): array
    {
        $dimensions = ImageDimensions::fromUploadedFile($file) ?? ['width' => 0, 'height' => 0];
        $orientation = ImageDimensions::orientation($dimensions) ?? ImageDimensions::ORIENTATION_LANDSCAPE;

        $primary = $this->processAndStore($file, $directory, self::MAX_DIMENSION, 'banner');

        $medium = null;
        $mobile = null;
        $thumb = null;

        if ($withVariants && extension_loaded('gd')) {
            $medium = $this->processAndStore($file, $directory.'/medium', self::MEDIUM_DIMENSION, 'md');
            $mobile = $this->processAndStore($file, $directory.'/mobile', self::MOBILE_DIMENSION, 'sm');
            $thumb = $this->processAndStore($file, $directory.'/thumbs', self::THUMB_DIMENSION, 'thumb');
        }

        return [
            'path' => $primary,
            'medium_path' => $medium,
            'mobile_path' => $mobile,
            'thumb_path' => $thumb,
            'orientation' => $orientation,
            'width' => $dimensions['width'],
            'height' => $dimensions['height'],
        ];
    }

    protected function processAndStore(UploadedFile $file, string $directory, int $maxDimension, string $suffix): string
    {
        $directory = trim($directory, '/');

        if (! extension_loaded('gd')) {
            return $file->store($directory, 'public');
        }

        $mime = strtolower((string) $file->getMimeType());
        $image = $this->loadImage($file->getRealPath(), $mime);

        if ($image === false) {
            // WebP and other formats: store original when GD cannot decode.
            return $file->store($directory, 'public');
        }

        $image = $this->resizeToMaxDimension($image, $maxDimension);
        [$binary, $extension] = $this->compressToMaxBytes($image, $mime);
        imagedestroy($image);

        $path = $directory.'/'.Str::uuid().'-'.$suffix.'.'.$extension;
        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    protected function fitsDimensions(string $path, int $max): bool
    {
        $size = @getimagesize($path);

        if ($size === false) {
            return true;
        }

        return $size[0] <= $max && $size[1] <= $max;
    }

    /**
     * @return \GdImage|false
     */
    protected function loadImage(string $path, string $mime)
    {
        return match ($mime) {
            'image/png' => @imagecreatefrompng($path),
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };
    }

    /**
     * @param  \GdImage  $image
     * @return \GdImage
     */
    protected function resizeToMaxDimension($image, int $maxDimension)
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width <= $maxDimension && $height <= $maxDimension) {
            return $image;
        }

        $ratio = min($maxDimension / $width, $maxDimension / $height);
        $newWidth = max(1, (int) round($width * $ratio));
        $newHeight = max(1, (int) round($height * $ratio));

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);

        return $resized;
    }

    /**
     * @param  \GdImage  $image
     * @return array{0: string, 1: string}
     */
    protected function compressToMaxBytes($image, string $mime): array
    {
        $preferPng = $mime === 'image/png' && $this->imageHasTransparency($image);

        for ($attempt = 0; $attempt < 14; $attempt++) {
            if ($preferPng) {
                $binary = $this->encodePng($image, min(9, 2 + $attempt));
                if (strlen($binary) <= self::MAX_STORED_BYTES) {
                    return [$binary, 'png'];
                }
            } else {
                $quality = max(35, 92 - ($attempt * 4));
                $binary = $this->encodeJpeg($image, $quality);
                if (strlen($binary) <= self::MAX_STORED_BYTES) {
                    return [$binary, 'jpg'];
                }
            }

            if ($attempt >= 3) {
                $image = $this->scaleDown($image, 0.85);
            }
        }

        return [$this->encodeJpeg($image, 35), 'jpg'];
    }

    /**
     * @param  \GdImage  $image
     * @return \GdImage
     */
    protected function scaleDown($image, float $factor)
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $newWidth = max(1, (int) round($width * $factor));
        $newHeight = max(1, (int) round($height * $factor));

        $scaled = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($scaled, false);
        imagesavealpha($scaled, true);
        imagecopyresampled($scaled, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);

        return $scaled;
    }

    /**
     * @param  \GdImage  $image
     */
    protected function imageHasTransparency($image): bool
    {
        $width = imagesx($image);
        $height = imagesy($image);

        for ($x = 0; $x < min($width, 20); $x++) {
            for ($y = 0; $y < min($height, 20); $y++) {
                $rgba = imagecolorat($image, $x, $y);
                if (($rgba & 0x7F000000) >> 24 > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  \GdImage  $image
     */
    protected function encodeJpeg($image, int $quality): string
    {
        $canvas = imagecreatetruecolor(imagesx($image), imagesy($image));
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);
        imagecopy($canvas, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));

        ob_start();
        imagejpeg($canvas, null, $quality);
        $binary = (string) ob_get_clean();
        imagedestroy($canvas);

        return $binary;
    }

    /**
     * @param  \GdImage  $image
     */
    protected function encodePng($image, int $level): string
    {
        ob_start();
        imagepng($image, null, $level);

        return (string) ob_get_clean();
    }
}

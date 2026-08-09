<?php

namespace App\Services\Talent;

class VideoInspectionService
{
    /**
     * Return the duration of a video file in whole seconds, or null when it
     * cannot be determined (e.g. unsupported container).
     */
    public function durationSeconds(string $absolutePath): ?int
    {
        if (! is_file($absolutePath) || ! class_exists(\getID3::class)) {
            return null;
        }

        try {
            $getID3 = new \getID3();
            $info = $getID3->analyze($absolutePath);
        } catch (\Throwable) {
            return null;
        }

        $seconds = $info['playtime_seconds'] ?? null;

        if ($seconds === null || ! is_numeric($seconds)) {
            return null;
        }

        return (int) ceil((float) $seconds);
    }
}

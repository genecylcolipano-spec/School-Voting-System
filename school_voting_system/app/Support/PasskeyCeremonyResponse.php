<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

final class PasskeyCeremonyResponse
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public static function json(array $payload, int $status = 200): JsonResponse
    {
        return response()->json($payload, $status)->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, private',
            'Pragma' => 'no-cache',
        ]);
    }
}

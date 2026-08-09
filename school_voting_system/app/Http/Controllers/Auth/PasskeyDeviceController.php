<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Passkey;
use App\Models\User;
use App\Support\UserAgentParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Passkeys\Actions\DeletePasskey;
use Laravel\Passkeys\Contracts\PasskeyUser;

/**
 * Manage multiple passkey devices per user (list / rename / revoke).
 */
class PasskeyDeviceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $devices = $user->passkeys()
            ->select(['id', 'device_name', 'name', 'counter', 'last_used_at', 'created_at', 'status'])
            ->latest('created_at')
            ->get()
            ->map(function (Passkey $passkey) {
                $label = $passkey->device_name ?? $passkey->name ?? 'Device';

                return [
                    'id' => $passkey->id,
                    'device_name' => $label,
                    'device_type' => UserAgentParser::deviceTypeFromName($label),
                    'operating_system' => UserAgentParser::platformFromDeviceName($label),
                    'last_used_at' => $passkey->last_used_at?->toIso8601String(),
                    'last_used_human' => $passkey->last_used_at?->diffForHumans() ?? 'Never',
                    'created_at' => $passkey->created_at?->toIso8601String(),
                    'created_human' => $passkey->created_at?->format('M d, Y') ?? '—',
                ];
            });

        return response()->json(['devices' => $devices]);
    }

    public function update(Request $request, Passkey $passkey): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless((int) $passkey->user_id === (int) $user->getKey(), 403);

        $validated = $request->validate([
            'device_name' => ['required', 'string', 'max:120'],
        ]);

        $name = trim($validated['device_name']);

        $passkey->forceFill([
            'device_name' => $name,
            'name' => $name,
        ])->save();

        return response()->json([
            'message' => 'Device renamed successfully.',
            'device' => [
                'id' => $passkey->id,
                'device_name' => $name,
            ],
        ]);
    }

    public function destroy(Request $request, Passkey $passkey, DeletePasskey $deletePasskey): JsonResponse
    {
        /** @var User&PasskeyUser $user */
        $user = $request->user();

        abort_unless((int) $passkey->user_id === (int) $user->getKey(), 403);

        if ($user->passkeys()->count() <= 1) {
            return response()->json([
                'message' => 'You must keep at least one passkey on your account.',
            ], 422);
        }

        $deletePasskey($user, $passkey);

        return response()->json(['message' => 'Device removed successfully.']);
    }
}

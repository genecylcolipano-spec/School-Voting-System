<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Signed, passwordless first-time passkey enrollment for users without registered credentials.
 */
class PasskeyBootstrapController extends Controller
{
    public function show(Request $request, User $user): View
    {
        // Enrollment links are often opened while an admin is still logged in.
        // Clear that session so the passkey is registered for $user, not the admin.
        if ($request->user()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $request->session()->put('passkey.bootstrap_user_id', $user->id);

        return view('auth.enroll-passkey', [
            'user' => $user,
            'pending' => null,
            'registerOptionsUrl' => route('register.passkey.bootstrap.options'),
            'registerVerifyUrl' => route('register.passkey.bootstrap.verify'),
        ]);
    }
}

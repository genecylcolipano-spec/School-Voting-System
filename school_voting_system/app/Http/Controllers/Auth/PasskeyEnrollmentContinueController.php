<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\PasskeyEnrollmentLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PasskeyEnrollmentContinueController extends Controller
{
    public function __construct(
        protected PasskeyEnrollmentLinkService $enrollmentLinks,
    ) {}

    public function __invoke(Request $request, string $token): View|RedirectResponse
    {
        $link = $this->enrollmentLinks->findUsableByPlainToken($token);

        if (! $link) {
            return redirect()->route('login')->with(
                'error',
                'The enrollment link is no longer valid.',
            );
        }

        $user = $link->user;

        if (! $user) {
            return redirect()->route('login')->with('error', 'The enrollment link is no longer valid.');
        }

        if ($request->user()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $request->session()->put('passkey.bootstrap_user_id', $user->id);
        $request->session()->put(PasskeyEnrollmentLinkService::SESSION_LINK_ID, $link->id);

        return view('auth.enroll-passkey', [
            'user' => $user,
            'pending' => null,
            'registerOptionsUrl' => route('register.passkey.bootstrap.options'),
            'registerVerifyUrl' => route('register.passkey.bootstrap.verify'),
        ]);
    }
}

<?php

namespace App\Actions\Passkeys;

use Laravel\Passkeys\Actions\GenerateRegistrationOptions as BaseGenerateRegistrationOptions;
use Webauthn\AuthenticatorSelectionCriteria;

/**
 * Forces Windows Hello / platform authenticator for local portal registration.
 * This avoids Edge prompting users to "set up a security key" (cross-platform).
 */
class GeneratePlatformRegistrationOptions extends BaseGenerateRegistrationOptions
{
    public function authenticatorSelection(): AuthenticatorSelectionCriteria
    {
        // Platform authenticator = Windows Hello / Touch ID / Face ID.
        $platform = AuthenticatorSelectionCriteria::AUTHENTICATOR_ATTACHMENT_PLATFORM;

        // Discoverable credential enables username-less sign-in (passkey UX).
        $residentKey = AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_REQUIRED;

        // Require biometric/PIN verification.
        $userVerification = AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_REQUIRED;

        return AuthenticatorSelectionCriteria::create(
            authenticatorAttachment: $platform,
            userVerification: $userVerification,
            residentKey: $residentKey,
        );
    }
}


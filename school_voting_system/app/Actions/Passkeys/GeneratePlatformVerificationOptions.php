<?php

namespace App\Actions\Passkeys;

use Laravel\Passkeys\Actions\GenerateVerificationOptions as BaseGenerateVerificationOptions;
use Webauthn\AuthenticatorSelectionCriteria;

/**
 * Aligns login verification with registration (platform + preferred UV).
 */
class GeneratePlatformVerificationOptions extends BaseGenerateVerificationOptions
{
    public function __invoke(?\Laravel\Passkeys\Contracts\PasskeyUser $user = null): \Webauthn\PublicKeyCredentialRequestOptions
    {
        $options = parent::__invoke($user);

        return \Webauthn\PublicKeyCredentialRequestOptions::create(
            challenge: $options->challenge,
            rpId: $options->rpId,
            allowCredentials: $options->allowCredentials,
            userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED,
            timeout: $options->timeout,
            extensions: $options->extensions,
            hints: $options->hints,
        );
    }
}

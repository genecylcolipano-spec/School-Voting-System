<?php

namespace App\Services\Auth;

use App\Actions\Passkeys\GeneratePlatformRegistrationOptions;
use Laravel\Passkeys\Passkeys;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialUserEntity;

class PendingRegistrationOptionsFactory extends GeneratePlatformRegistrationOptions
{
    /**
     * @param  array{
     *     account_id: string,
     *     first_name: string,
     *     last_name: string
     * }  $pending
     */
    public function make(array $pending): PublicKeyCredentialCreationOptions
    {
        $displayName = trim($pending['first_name'].' '.$pending['last_name']);

        return PublicKeyCredentialCreationOptions::create(
            rp: $this->relyingParty(),
            user: PublicKeyCredentialUserEntity::create(
                name: $pending['account_id'],
                id: random_bytes(32),
                displayName: $displayName,
            ),
            challenge: random_bytes(32),
            pubKeyCredParams: $this->supportedAlgorithms(),
            authenticatorSelection: $this->authenticatorSelection(),
            attestation: PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
            excludeCredentials: [],
            timeout: Passkeys::timeout(),
        );
    }
}

<?php

namespace App\Actions\Passkeys;

use App\Models\Passkey;
use Illuminate\Support\Facades\Log;
use Laravel\Passkeys\Actions\StorePasskey as BaseStorePasskey;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Laravel\Passkeys\Support\WebAuthn;
use Webauthn\CredentialRecord;

class StorePasskeyCredential extends BaseStorePasskey
{
    public function createPasskey(
        PasskeyUser $user,
        string $name,
        CredentialRecord $source
    ): Passkey {
        /** @var Passkey $passkey */
        $passkey = parent::createPasskey($user, $name, $source);

        $credential = json_decode(WebAuthn::toJson($source), true, flags: JSON_THROW_ON_ERROR);
        $passkey->device_name = $name;
        $passkey->syncSecurityMetadataFromCredential($credential);
        $passkey->save();

        Log::info('Passkey credential stored.', [
            'passkey_id' => $passkey->id,
            'credential_id' => $passkey->credential_id,
            'user_id' => $passkey->user_id,
            'device_name' => $name,
            'has_public_key' => ! empty($passkey->public_key),
            'counter' => $passkey->counter,
        ]);

        return $passkey;
    }
}

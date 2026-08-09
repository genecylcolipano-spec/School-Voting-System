<?php

namespace App\Actions\Passkeys;

use App\Models\Passkey;
use Illuminate\Support\Facades\Log;
use Laravel\Passkeys\Actions\VerifyPasskey as BaseVerifyPasskey;
use Laravel\Passkeys\Exceptions\InvalidPasskeyException;
use Laravel\Passkeys\Passkey as BasePasskey;
use Laravel\Passkeys\Passkeys;
use Laravel\Passkeys\Support\WebAuthn;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Webauthn\CredentialRecord;
use Webauthn\PublicKeyCredential;

class VerifyPasskeyCredential extends BaseVerifyPasskey
{
    public function getPasskey(PublicKeyCredential $credential, bool $lock = false): Passkey
    {
        $candidates = $this->candidateCredentialIds($credential);

        Log::debug('Passkey credential lookup.', [
            'candidate_ids' => $candidates,
            'rp_id' => Passkeys::relyingPartyId(),
        ]);

        foreach ($candidates as $credentialId) {
            $query = Passkeys::passkeyModel()::where('credential_id', $credentialId);

            if ($lock) {
                $query->lockForUpdate();
            }

            $passkey = $query->first();

            if ($passkey) {
                Log::debug('Passkey credential matched in database.', [
                    'passkey_id' => $passkey->id,
                    'matched_credential_id' => $credentialId,
                    'user_id' => $passkey->user_id,
                ]);

                return $passkey;
            }
        }

        $allStored = Passkeys::passkeyModel()::query()
            ->select('id', 'user_id', 'credential_id')
            ->get()
            ->map(fn ($p) => [
                'passkey_id' => $p->id,
                'user_id' => $p->user_id,
                'credential_id' => $p->credential_id,
            ])
            ->all();

        Log::warning('Passkey credential not recognized.', [
            'candidate_ids' => $candidates,
            'stored_passkeys' => $allStored,
        ]);

        throw InvalidPasskeyException::make('Passkey not recognized. It may have been removed from your account.');
    }

    /**
     * @return list<string>
     */
    protected function candidateCredentialIds(PublicKeyCredential $credential): array
    {
        $candidates = [];

        try {
            $candidates[] = Base64UrlSafe::encodeUnpadded($credential->rawId);
        } catch (\Throwable) {
            // rawId may be empty or invalid.
        }

        return array_values(array_unique(array_filter($candidates)));
    }

    public function updatePasskey(BasePasskey $passkey, CredentialRecord $source): void
    {
        parent::updatePasskey($passkey, $source);

        if ($passkey instanceof Passkey) {
            $credential = json_decode(WebAuthn::toJson($source), true, flags: JSON_THROW_ON_ERROR);
            $passkey->syncSecurityMetadataFromCredential($credential);
            $passkey->save();

            Log::debug('Passkey counter updated after verification.', [
                'passkey_id' => $passkey->id,
                'user_id' => $passkey->user_id,
                'counter' => $passkey->counter,
            ]);
        }
    }
}

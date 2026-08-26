<?php

namespace App\Http\Requests\Admin;

use App\Models\PasskeyRecoveryRequest;

class EnrollPasskeyRecoveryRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        $recovery = $this->route('recoveryRequest');

        return parent::authorize()
            && $recovery instanceof PasskeyRecoveryRequest;
    }

    public function rules(): array
    {
        return [];
    }
}

<?php

namespace App\Http\Requests\Admin\Fundraiser;

use App\Models\Fundraiser;

class UpdateFundraiserRequest extends StoreFundraiserRequest
{
    public function authorize(): bool
    {
        $fundraiser = $this->route('fundraiser');

        return $fundraiser instanceof Fundraiser
            && ($this->user()?->can('update', $fundraiser) ?? false);
    }
}

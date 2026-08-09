<?php

namespace App\Http\Requests\Admin\Fundraiser;

use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\Fundraiser;

class DeleteFundraiserRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        $fundraiser = $this->route('fundraiser');

        return $fundraiser instanceof Fundraiser
            && ($this->user()?->can('delete', $fundraiser) ?? false);
    }

    public function rules(): array
    {
        return [];
    }
}

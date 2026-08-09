<?php

namespace App\Http\Requests\Admin\Campaign;

use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\Partylist;

class StoreCampaignPosterRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        $partylist = $this->route('partylist');

        return $partylist instanceof Partylist
            && ($this->user()?->can('update', $partylist) ?? false);
    }

    public function rules(): array
    {
        return [
            'poster_image' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }
}

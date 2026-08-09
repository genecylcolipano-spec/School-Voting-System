<?php

namespace App\Http\Requests\Admin\Campaign;

use App\Enums\CampaignStatus;
use App\Models\Partylist;
use Illuminate\Validation\Rule;

class StorePartylistRequest extends PartylistFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Partylist::class) ?? false;
    }

    public function rules(): array
    {
        return array_merge($this->partylistRules(), [
            'status' => ['nullable', Rule::enum(CampaignStatus::class)],
            'poster_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);
    }
}

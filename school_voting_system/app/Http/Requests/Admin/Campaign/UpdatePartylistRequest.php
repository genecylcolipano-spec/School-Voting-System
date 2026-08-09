<?php

namespace App\Http\Requests\Admin\Campaign;

use App\Enums\CampaignStatus;
use App\Models\Partylist;
use Illuminate\Validation\Rule;

class UpdatePartylistRequest extends PartylistFormRequest
{
    public function authorize(): bool
    {
        $partylist = $this->route('partylist');

        return $partylist instanceof Partylist
            && ($this->user()?->can('update', $partylist) ?? false);
    }

    public function rules(): array
    {
        return array_merge($this->partylistRules(), [
            'status' => ['required', Rule::enum(CampaignStatus::class)],
        ]);
    }
}

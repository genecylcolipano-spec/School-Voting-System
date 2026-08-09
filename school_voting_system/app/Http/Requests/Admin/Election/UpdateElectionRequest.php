<?php

namespace App\Http\Requests\Admin\Election;

use App\Models\Election;
use Illuminate\Validation\Rule;

class UpdateElectionRequest extends ElectionFormRequest
{
    public function authorize(): bool
    {
        $election = $this->route('election');

        return $election instanceof Election
            && ($this->user()?->can('update', $election) ?? false);
    }

    public function rules(): array
    {
        return array_merge(
            $this->electionDetailRules(),
            $this->positionRules('new_positions'),
            $this->participatingCampaignRules(),
            $this->candidateRules('new_candidates', useCategoryId: true),
            [
                'existing_candidates' => ['nullable', 'array'],
                'existing_candidates.*.display_name' => ['nullable', 'string', 'max:255'],
                'existing_candidates.*.election_category_id' => ['nullable', 'exists:election_categories,id'],
                'existing_candidates.*.party_or_group' => ['nullable', 'string', 'max:255'],
                'existing_candidates.*.partylist_id' => ['nullable', 'integer', Rule::in($this->submittedPartylistIds())],
                'existing_candidates.*.platform' => ['nullable', 'string'],
                'existing_candidates.*.is_active' => ['nullable', 'boolean'],
                'existing_candidates.*.photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'existing_candidates.*.remove_photo' => ['nullable', 'boolean'],
            ],
        );
    }
}

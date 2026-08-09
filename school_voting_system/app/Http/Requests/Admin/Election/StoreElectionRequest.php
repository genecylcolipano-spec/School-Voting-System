<?php

namespace App\Http\Requests\Admin\Election;

use App\Models\Election;

class StoreElectionRequest extends ElectionFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Election::class) ?? false;
    }

    public function rules(): array
    {
        return array_merge(
            $this->electionDetailRules(),
            $this->positionRules('positions'),
            $this->participatingCampaignRules(),
            $this->candidateRules('candidates', useCategoryId: false),
        );
    }
}

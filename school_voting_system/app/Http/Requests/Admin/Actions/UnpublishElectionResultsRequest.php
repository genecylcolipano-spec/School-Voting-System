<?php

namespace App\Http\Requests\Admin\Actions;

use App\Models\Election;

class UnpublishElectionResultsRequest extends ElectionScopedActionRequest
{
    public function authorize(): bool
    {
        if (! $this->scope()->canUnpublishElectionResults($this->user())) {
            return false;
        }

        if (! $this->electionInScope()) {
            return false;
        }

        $election = $this->route('election');

        return $election instanceof Election && $election->public_results_published;
    }

    public function rules(): array
    {
        return [];
    }
}

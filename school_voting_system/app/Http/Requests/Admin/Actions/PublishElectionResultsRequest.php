<?php

namespace App\Http\Requests\Admin\Actions;

use App\Models\Election;
use App\Services\Admin\ElectionResultsPublishingService;

class PublishElectionResultsRequest extends ElectionScopedActionRequest
{
    public function authorize(): bool
    {
        if (! $this->scope()->canPublishElectionResults($this->user())) {
            return false;
        }

        if (! $this->electionInScope()) {
            return false;
        }

        $election = $this->route('election');

        if (! $election instanceof Election) {
            return false;
        }

        return app(ElectionResultsPublishingService::class)->isReadyForReview($election);
    }

    public function rules(): array
    {
        return [];
    }
}

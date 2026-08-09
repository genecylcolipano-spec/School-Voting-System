<?php

namespace App\Http\Requests\Admin\Actions;

use App\Models\TalentEvent;

class PublishTalentResultsRequest extends OpenTalentVotingRequest
{
    public function authorize(): bool
    {
        if (! $this->scope()->canPublishTalentResults($this->user())) {
            return false;
        }

        $talentEvent = $this->route('talentEvent');

        if (! $talentEvent instanceof TalentEvent) {
            return false;
        }

        try {
            $this->scope()->assertTalentEventInScope($this->user(), $talentEvent);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException) {
            return false;
        }

        return true;
    }
}

<?php

namespace App\Http\Requests\Admin\Actions;

use App\Models\TalentEvent;

class UnpublishTalentResultsRequest extends PublishTalentResultsRequest
{
    public function authorize(): bool
    {
        if (! parent::authorize()) {
            return false;
        }

        $talentEvent = $this->route('talentEvent');

        return $talentEvent instanceof TalentEvent && $talentEvent->hasPublishedResults();
    }
}

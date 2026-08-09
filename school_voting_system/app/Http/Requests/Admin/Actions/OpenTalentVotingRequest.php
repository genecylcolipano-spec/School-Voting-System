<?php

namespace App\Http\Requests\Admin\Actions;

use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\TalentEvent;

class OpenTalentVotingRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        if (! $this->scope()->canManageTalentVoting($this->user())) {
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

    public function rules(): array
    {
        return [];
    }
}

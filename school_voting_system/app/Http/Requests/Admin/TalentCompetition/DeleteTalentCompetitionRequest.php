<?php

namespace App\Http\Requests\Admin\TalentCompetition;

use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\TalentEvent;

class DeleteTalentCompetitionRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        $talentEvent = $this->route('talentEvent');
        $user = $this->user();

        if (! $talentEvent instanceof TalentEvent || $user === null) {
            return false;
        }

        if (! $this->scope()->canCreateTalentEvents($user)) {
            return false;
        }

        $this->scope()->assertTalentEventInScope($user, $talentEvent);

        if ($user->isSuperAdmin()) {
            return true;
        }

        return (int) $talentEvent->created_by === (int) $user->id;
    }

    public function rules(): array
    {
        return [];
    }
}

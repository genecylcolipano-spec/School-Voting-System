<?php

namespace App\Http\Requests\Admin\Actions;

use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;

class ApproveEntryRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        if (! $this->scope()->canApproveTalentEntries($this->user())) {
            return false;
        }

        $entry = $this->route('entry');

        if (! $entry instanceof TalentEventEntry || ! $entry->isPending()) {
            return false;
        }

        try {
            $this->scope()->assertTalentEntryInScope($this->user(), $entry);
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

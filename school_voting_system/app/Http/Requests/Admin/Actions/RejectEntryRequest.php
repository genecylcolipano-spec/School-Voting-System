<?php

namespace App\Http\Requests\Admin\Actions;

use App\Models\TalentEventEntry;

class RejectEntryRequest extends ApproveEntryRequest
{
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    public function authorize(): bool
    {
        $entry = $this->route('entry');

        return parent::authorize()
            && $entry instanceof TalentEventEntry
            && $entry->isPending();
    }
}

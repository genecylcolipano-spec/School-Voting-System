<?php

namespace App\Http\Requests\Admin\Actions;

use App\Models\PartylistPoster;

class RejectPosterRequest extends ApprovePosterRequest
{
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    public function authorize(): bool
    {
        $poster = $this->route('poster');

        return parent::authorize()
            && $poster instanceof PartylistPoster
            && $poster->isPending();
    }
}

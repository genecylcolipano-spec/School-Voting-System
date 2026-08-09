<?php

namespace App\Http\Requests\Admin\Election;

use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\Election;

class DeleteElectionRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        $election = $this->route('election');

        return $election instanceof Election
            && ($this->user()?->can('delete', $election) ?? false);
    }

    public function rules(): array
    {
        return [];
    }
}

<?php

namespace App\Http\Requests\Admin\Candidate;

use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\Candidate;

class DeleteCandidateRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        $candidate = $this->route('candidate');

        return $candidate instanceof Candidate
            && ($this->user()?->can('delete', $candidate) ?? false);
    }

    public function rules(): array
    {
        return [];
    }
}

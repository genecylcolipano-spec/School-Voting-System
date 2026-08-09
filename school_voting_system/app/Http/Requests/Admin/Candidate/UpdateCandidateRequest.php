<?php

namespace App\Http\Requests\Admin\Candidate;

use App\Models\Candidate;

class UpdateCandidateRequest extends CandidateFormRequest
{
    public function authorize(): bool
    {
        $candidate = $this->route('candidate');

        return $candidate instanceof Candidate
            && ($this->user()?->can('update', $candidate) ?? false);
    }

    public function rules(): array
    {
        return $this->candidateRules();
    }
}

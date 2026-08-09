<?php

namespace App\Http\Requests\Admin\Candidate;

use App\Models\Candidate;

class StoreCandidateRequest extends CandidateFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Candidate::class) ?? false;
    }

    public function rules(): array
    {
        return $this->candidateRules();
    }
}

<?php

namespace App\Http\Requests\Admin\Actions;

class ResumeElectionRequest extends ElectionScopedActionRequest
{
    public function authorize(): bool
    {
        return $this->scope()->canPauseElection($this->user())
            && $this->electionInScope();
    }

    public function rules(): array
    {
        return [];
    }
}

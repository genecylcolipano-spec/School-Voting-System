<?php

namespace App\Http\Requests\Admin\Actions;

class PauseElectionRequest extends ElectionScopedActionRequest
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

<?php

namespace App\Http\Requests\Admin\Actions;

use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\Candidate;

class VerifyCandidateRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        if (! $this->scope()->canVerifyCandidates($this->user())) {
            return false;
        }

        $candidate = $this->route('candidate');

        if (! $candidate instanceof Candidate) {
            return false;
        }

        if ($this->user()?->isSuperAdmin()) {
            return true;
        }

        $election = $this->scope()->assignedElection($this->user());

        return $election && $candidate->election_id === $election->id;
    }

    public function rules(): array
    {
        return [];
    }
}

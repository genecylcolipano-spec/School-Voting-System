<?php

namespace App\Http\Requests\Admin\Actions;

use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\Election;

abstract class ElectionScopedActionRequest extends AdminFormRequest
{
    protected function electionInScope(): bool
    {
        $election = $this->route('election');

        if (! $election instanceof Election) {
            return false;
        }

        if ($this->user()?->isSuperAdmin()) {
            return true;
        }

        return $this->scope()->assignedElection($this->user())?->id === $election->id;
    }
}

<?php

namespace App\Http\Requests\Admin\Actions;

use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\AdminComplaint;

class ResolveComplaintRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        if ($this->scope()->isReadOnly($this->user()) || $this->scope()->isAuditor($this->user())) {
            return false;
        }

        $complaint = $this->route('complaint');

        if (! $complaint instanceof AdminComplaint || $complaint->status !== 'open') {
            return false;
        }

        return $this->user()?->isSuperAdmin()
            || $complaint->assigned_to === $this->user()?->id;
    }

    public function rules(): array
    {
        return [];
    }
}

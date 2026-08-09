<?php

namespace App\Http\Requests\Admin\SuperAdmin;

use App\Http\Requests\Admin\SuperAdminFormRequest;
use App\Models\Election;

class ElectionActionRequest extends SuperAdminFormRequest
{
    public function rules(): array
    {
        return [
            'action' => ['required', 'in:open,pause,resume,close,annul,rerun,lock,unlock,schedule,publish_results,unpublish_results'],
            'scheduled_open_at' => ['nullable', 'date'],
            'scheduled_close_at' => ['nullable', 'date', 'after_or_equal:scheduled_open_at'],
        ];
    }

    public function election(): Election
    {
        return $this->route('election');
    }
}

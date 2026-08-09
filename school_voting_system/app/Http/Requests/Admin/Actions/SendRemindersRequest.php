<?php

namespace App\Http\Requests\Admin\Actions;

use App\Http\Requests\Admin\AdminFormRequest;

class SendRemindersRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        return $this->scope()->canSendReminders($this->user());
    }

    public function rules(): array
    {
        return [];
    }
}

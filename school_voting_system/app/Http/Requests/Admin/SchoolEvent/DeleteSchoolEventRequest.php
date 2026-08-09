<?php

namespace App\Http\Requests\Admin\SchoolEvent;

use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\Event;

class DeleteSchoolEventRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        $event = $this->route('event');

        return $event instanceof Event
            && ($this->user()?->can('delete', $event) ?? false);
    }

    public function rules(): array
    {
        return [];
    }
}

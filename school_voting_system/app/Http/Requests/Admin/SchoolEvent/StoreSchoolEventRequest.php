<?php

namespace App\Http\Requests\Admin\SchoolEvent;

use App\Enums\EventStatus;
use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\Event;
use Illuminate\Validation\Rule;

class StoreSchoolEventRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Event::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
            'event_date' => ['required', 'date'],
            'venue' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::enum(EventStatus::class)],
        ];
    }
}

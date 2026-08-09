<?php

namespace App\Http\Requests\Admin\Announcement;

use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\Announcement;

class DeleteAnnouncementRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        $announcement = $this->route('announcement');

        return $announcement instanceof Announcement
            && ($this->user()?->can('delete', $announcement) ?? false);
    }

    public function rules(): array
    {
        return [];
    }
}

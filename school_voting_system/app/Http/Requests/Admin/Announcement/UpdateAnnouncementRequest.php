<?php

namespace App\Http\Requests\Admin\Announcement;

use App\Models\Announcement;

class UpdateAnnouncementRequest extends StoreAnnouncementRequest
{
    public function authorize(): bool
    {
        $announcement = $this->route('announcement');

        return $announcement instanceof Announcement
            && ($this->user()?->can('update', $announcement) ?? false);
    }
}

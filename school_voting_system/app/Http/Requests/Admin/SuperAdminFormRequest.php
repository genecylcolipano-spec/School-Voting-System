<?php

namespace App\Http\Requests\Admin;

abstract class SuperAdminFormRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }
}

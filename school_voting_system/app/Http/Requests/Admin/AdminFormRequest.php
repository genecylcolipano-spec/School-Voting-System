<?php

namespace App\Http\Requests\Admin;

use App\Services\Admin\AdminScopeService;
use Illuminate\Foundation\Http\FormRequest;

abstract class AdminFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && ($this->user()->isAdmin() || $this->user()->isSuperAdmin());
    }

    protected function scope(): AdminScopeService
    {
        return app(AdminScopeService::class);
    }

    protected function isSuperAdmin(): bool
    {
        return (bool) $this->user()?->isSuperAdmin();
    }
}

<?php

namespace App\Http\Requests\Admin\Actions;

use App\Http\Requests\Admin\AdminFormRequest;

class ExportPreliminaryResultsRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        return $this->scope()->canExportPreliminaryResults($this->user());
    }

    public function rules(): array
    {
        return [];
    }
}

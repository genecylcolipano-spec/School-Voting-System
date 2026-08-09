<?php

namespace App\Http\Requests\Admin\Results;

use App\Http\Requests\Admin\AdminFormRequest;

class ExportResultsRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        return $this->scope()->canExportPreliminaryResults($this->user());
    }

    public function rules(): array
    {
        return [
            'format' => ['sometimes', 'in:pdf,excel,csv,print'],
        ];
    }
}

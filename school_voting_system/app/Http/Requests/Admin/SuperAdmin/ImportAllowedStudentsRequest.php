<?php

namespace App\Http\Requests\Admin\SuperAdmin;

use App\Http\Requests\Admin\SuperAdminFormRequest;

class ImportAllowedStudentsRequest extends SuperAdminFormRequest
{
    public function rules(): array
    {
        return [
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'csv_file.required' => 'Please choose a CSV file to upload.',
            'csv_file.mimes' => 'The file must be a CSV (.csv or .txt).',
            'csv_file.max' => 'The CSV file may not be larger than 2 MB.',
        ];
    }
}

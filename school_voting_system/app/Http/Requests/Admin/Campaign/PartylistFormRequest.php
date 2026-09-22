<?php

namespace App\Http\Requests\Admin\Campaign;

use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\Partylist;
use App\Services\Media\ImageCompressionService;
use Illuminate\Validation\Rule;

abstract class PartylistFormRequest extends AdminFormRequest
{
    protected function partylistRules(): array
    {
        $partylist = $this->route('partylist');
        $ignoreId = $partylist instanceof Partylist ? $partylist->id : null;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('partylists', 'name')->ignore($ignoreId),
            ],
            'acronym' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:20'],
            'motto' => ['nullable', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:5000'],
            'description' => ['nullable', 'string', 'max:5000'],
            'leader' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'banner' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:'.ImageCompressionService::MAX_UPLOAD_KILOBYTES,
            ],
        ];
    }

    public function messages(): array
    {
        $uploadMb = (int) (ImageCompressionService::MAX_UPLOAD_KILOBYTES / 1024);

        return [
            'acronym.max' => 'The acronym may not be longer than 50 characters. Put the full party name in Partylist name.',
            'name.unique' => 'A campaign with this name already exists.',
            'banner.image' => 'The campaign banner must be a JPG, PNG, or WEBP image.',
            'banner.mimes' => 'The campaign banner must be a JPG, PNG, or WEBP image.',
            'banner.max' => "This banner is too large to upload. Choose a JPG, PNG, or WEBP up to {$uploadMb} MB. It will be resized and compressed automatically.",
        ];
    }
}

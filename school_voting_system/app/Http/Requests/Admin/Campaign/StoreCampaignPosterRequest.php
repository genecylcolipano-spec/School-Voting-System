<?php

namespace App\Http\Requests\Admin\Campaign;

use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\Partylist;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\RedirectResponse;

class StoreCampaignPosterRequest extends AdminFormRequest
{
    public const UPLOAD_FAILED_MESSAGE = 'Could not upload this poster. Use a JPG or PNG up to 2 MB, and attach the campaign to an election first.';

    public function authorize(): bool
    {
        $partylist = $this->route('partylist');

        return $partylist instanceof Partylist
            && ($this->user()?->can('update', $partylist) ?? false);
    }

    public function rules(): array
    {
        return [
            'poster_image' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'poster_image.required' => self::UPLOAD_FAILED_MESSAGE,
            'poster_image.image' => self::UPLOAD_FAILED_MESSAGE,
            'poster_image.mimes' => self::UPLOAD_FAILED_MESSAGE,
            'poster_image.max' => self::UPLOAD_FAILED_MESSAGE,
        ];
    }

    /**
     * @return array{error: string, poster_upload_failed_partylist_id: int}
     */
    public static function failureFlash(int $partylistId): array
    {
        return [
            'error' => self::UPLOAD_FAILED_MESSAGE,
            'poster_upload_failed_partylist_id' => $partylistId,
        ];
    }

    public static function failureRedirect(int $partylistId): RedirectResponse
    {
        return back()->with(self::failureFlash($partylistId));
    }

    protected function failedValidation(Validator $validator): void
    {
        $partylist = $this->route('partylist');
        if ($partylist instanceof Partylist) {
            session()->flash('error', self::UPLOAD_FAILED_MESSAGE);
            session()->flash('poster_upload_failed_partylist_id', $partylist->id);
        }

        parent::failedValidation($validator);
    }
}

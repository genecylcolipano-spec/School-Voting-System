<?php

namespace App\Http\Requests\Admin\Actions;

use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\PartylistPoster;

class ApprovePosterRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        if (! $this->scope()->canApprovePosters($this->user())) {
            return false;
        }

        $poster = $this->route('poster');

        if (! $poster instanceof PartylistPoster || ! $poster->isPending()) {
            return false;
        }

        try {
            $this->scope()->assertPosterInScope($this->user(), $poster);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [];
    }
}

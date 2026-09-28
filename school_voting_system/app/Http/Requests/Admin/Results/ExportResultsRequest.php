<?php

namespace App\Http\Requests\Admin\Results;

use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\Election;
use App\Models\TalentEvent;

class ExportResultsRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        $election = $this->route('election');
        if ($election instanceof Election) {
            return $this->scope()->canExportElectionResults($user, $election);
        }

        $talentEvent = $this->route('talentEvent');
        if ($talentEvent instanceof TalentEvent) {
            return $this->scope()->canExportTalentResults($user, $talentEvent);
        }

        return $this->scope()->canExportPreliminaryResults($user);
    }

    public function rules(): array
    {
        return [
            'format' => ['sometimes', 'in:pdf,excel,csv,print'],
        ];
    }
}

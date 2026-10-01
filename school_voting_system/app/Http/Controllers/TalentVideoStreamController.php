<?php

namespace App\Http\Controllers;

use App\Models\TalentEventEntry;
use App\Services\Admin\AdminScopeService;
use App\Services\Media\TalentVideoStorage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TalentVideoStreamController extends Controller
{
    public function __construct(
        protected AdminScopeService $scope,
        protected TalentVideoStorage $videos,
    ) {
    }

    public function __invoke(Request $request, TalentEventEntry $entry): Response
    {
        $entry->loadMissing('talentEvent');

        abort_unless(filled($entry->video_path), 404);

        $user = $request->user();
        abort_unless($user !== null, 403);

        $isAdmin = $user->isSuperAdmin() || $user->isAdmin();
        $isFacultyJudge = $user->isFaculty()
            && $entry->talentEvent
            && $entry->talentEvent->judges()->where('user_id', $user->id)->exists();

        if ($isAdmin) {
            $this->scope->assertTalentEntryInScope($user, $entry);
        } elseif ($isFacultyJudge) {
            abort_unless($entry->isApproved(), 404);
        } else {
            // Students may stream approved published entries, or their own submission
            // (including pending/rejected) so they can review what they uploaded.
            $ownsEntry = (int) $entry->user_id === (int) $user->id;
            $canViewPublished = $entry->isApproved()
                && (bool) ($entry->talentEvent?->published_to_students);

            abort_unless($ownsEntry || $canViewPublished, 404);
        }

        abort_unless($this->videos->exists($entry->video_path), 404);

        // Downloads are restricted to admins; students get an inline stream.
        return $this->videos->respond(
            $entry->video_path,
            download: $isAdmin && $request->boolean('download'),
        );
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditActionType;
use App\Enums\UserRole;
use App\Http\Controllers\Admin\Concerns\LogsAdminActions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Candidate\DeleteCandidateRequest;
use App\Http\Requests\Admin\Candidate\StoreCandidateRequest;
use App\Http\Requests\Admin\Candidate\UpdateCandidateRequest;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\ElectionCategory;
use App\Models\Partylist;
use App\Models\User;
use App\Services\Admin\AdminScopeService;
use App\Services\Media\ImageCompressionService;
use App\Support\AdminPortal;
use App\Support\EventImageUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminCandidateController extends Controller
{
    use LogsAdminActions;

    public function __construct(
        protected AdminScopeService $scope,
        protected ImageCompressionService $images,
    ) {}

    public function store(StoreCandidateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        unset($validated['photo'], $validated['remove_photo']);
        $validated['party_or_group'] = $this->partyLabel($validated['partylist_id'] ?? null, $validated['party_or_group'] ?? null);

        $candidate = Candidate::query()->create([
            ...$validated,
            'photo_path' => $request->file('photo') ? $this->storePhoto($request->file('photo')) : null,
            'eligibility_status' => 'verified',
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        $this->logAdminAction('Created candidate: '.$validated['display_name'], AuditActionType::Election, 'candidate', $candidate->id);

        return redirect()->route('admin.elections.edit', $candidate->election_id)->with('success', 'Candidate created.');
    }

    public function show(Request $request, Candidate $candidate): View
    {
        $this->authorize('view', $candidate);

        $candidate->load(['election', 'category', 'user']);

        return view('admin.candidates.show', [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => AdminPortal::notificationCount($request->user()),
            'candidate' => $candidate,
            'photoUrl' => EventImageUrl::hasUploadedImage($candidate->photo_path)
                ? EventImageUrl::resolve($candidate->photo_path)
                : null,
        ]);
    }

    public function edit(Request $request, Candidate $candidate): View
    {
        $this->authorize('view', $candidate);

        [$elections, $categories, $campaigns] = $this->formOptions($request);

        return view('admin.candidates.edit', [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => AdminPortal::notificationCount($request->user()),
            'candidate' => $candidate,
            'elections' => $elections,
            'categories' => $categories,
            'campaigns' => $campaigns,
            'students' => User::query()->where('role', UserRole::Student)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateCandidateRequest $request, Candidate $candidate): RedirectResponse
    {
        $validated = $request->validated();
        $removePhoto = (bool) ($validated['remove_photo'] ?? false);
        unset($validated['photo'], $validated['remove_photo']);
        $validated['party_or_group'] = $this->partyLabel($validated['partylist_id'] ?? null, $validated['party_or_group'] ?? null);

        if ($request->file('photo')) {
            if ($candidate->photo_path) {
                Storage::disk('public')->delete($candidate->photo_path);
            }
            $validated['photo_path'] = $this->storePhoto($request->file('photo'));
        } elseif ($removePhoto && $candidate->photo_path) {
            Storage::disk('public')->delete($candidate->photo_path);
            $validated['photo_path'] = null;
        }

        $candidate->update([
            ...$validated,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        $this->logAdminAction('Updated candidate: '.$candidate->display_name, AuditActionType::Election, 'candidate', $candidate->id);

        return redirect()->route('admin.elections.edit', $candidate->election_id)->with('success', 'Candidate updated.');
    }

    public function destroy(DeleteCandidateRequest $request, Candidate $candidate): RedirectResponse
    {
        $name = $candidate->display_name;
        $candidateId = $candidate->id;
        $electionId = $candidate->election_id;

        if ($candidate->photo_path) {
            Storage::disk('public')->delete($candidate->photo_path);
        }

        $candidate->delete();

        $this->logAdminAction('Deleted candidate: '.$name, AuditActionType::Election, 'candidate', $candidateId);

        if ($electionId) {
            return redirect()->route('admin.elections.edit', $electionId)->with('success', 'Candidate removed.');
        }

        return redirect()->route('admin.elections.index')->with('success', 'Candidate removed.');
    }

    protected function storePhoto(UploadedFile $file): string
    {
        return $this->images->storeOptimized($file, 'candidate-photos');
    }

    protected function partyLabel(mixed $partylistId, ?string $fallback): ?string
    {
        $partylistId = $partylistId !== null && $partylistId !== '' ? (int) $partylistId : null;

        if ($partylistId === null) {
            return $fallback;
        }

        return Partylist::query()->whereKey($partylistId)->value('name') ?? $fallback;
    }

    /**
     * @return array{0: \Illuminate\Support\Collection, 1: \Illuminate\Support\Collection, 2: \Illuminate\Support\Collection}
     */
    protected function formOptions(Request $request): array
    {
        $electionQuery = Election::query()->orderBy('title');

        if (! $request->user()->isSuperAdmin()) {
            $assignedId = $this->scope->assignment($request->user())?->election_id;
            $electionQuery->when($assignedId, fn ($q) => $q->whereKey($assignedId), fn ($q) => $q->whereRaw('0 = 1'));
        }

        $elections = $electionQuery->with('partylists:id,name,acronym')->get();

        $categories = ElectionCategory::query()
            ->with('election')
            ->whereIn('election_id', $elections->pluck('id'))
            ->orderBy('name')
            ->get();

        // Flatten campaign/election pivot rows so the form can filter the
        // campaign dropdown by the selected election.
        $campaigns = $elections->flatMap(
            fn (Election $election) => $election->partylists->map(fn (Partylist $partylist) => [
                'election_id' => $election->id,
                'id' => $partylist->id,
                'name' => $partylist->name,
                'acronym' => $partylist->acronym,
            ]),
        )->values();

        return [$elections, $categories, $campaigns];
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditActionType;
use App\Enums\ElectionStatus;
use App\Http\Controllers\Admin\Concerns\LogsAdminActions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Election\DeleteElectionRequest;
use App\Http\Requests\Admin\Election\StoreElectionRequest;
use App\Http\Requests\Admin\Election\UpdateElectionRequest;
use App\Models\Election;
use App\Models\Partylist;
use App\Services\Admin\AdminScopeService;
use App\Services\Admin\ElectionSetupService;
use App\Services\Portal\AnnouncementService;
use App\Services\Portal\PortalNotificationService;
use App\Services\SuperAdmin\ElectionLifecycleService;
use App\Support\AdminPortal;
use App\Support\SlugGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminElectionController extends Controller
{
    use LogsAdminActions;

    public function __construct(
        protected AdminScopeService $scope,
        protected ElectionSetupService $setup,
        protected PortalNotificationService $notifications,
        protected AnnouncementService $announcements,
        protected ElectionLifecycleService $elections,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Election::class);

        $query = Election::query()
            ->withCount(['categories', 'candidates', 'partylists', 'votes'])
            ->latest();

        if (! $request->user()->isSuperAdmin()) {
            $assignedId = $this->scope->assignment($request->user())?->election_id;
            $query->when($assignedId, fn ($q) => $q->whereKey($assignedId), fn ($q) => $q->whereRaw('0 = 1'));
        }

        return view('admin.elections.index', [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => AdminPortal::notificationCount($request->user()),
            'elections' => $query->paginate(15),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Election::class);

        return view('admin.elections.create', [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => AdminPortal::notificationCount($request->user()),
            'statuses' => ElectionStatus::cases(),
            'campaigns' => Partylist::query()->selectableForElections()->orderBy('name')->get(),
            'selectedPartylistIds' => collect(old('partylists', []))->map(fn ($id) => (int) $id)->all(),
        ]);
    }

    public function store(StoreElectionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $requestedStatus = $validated['status'] instanceof ElectionStatus
            ? $validated['status']
            : ElectionStatus::from((string) $validated['status']);
        $initialStatus = $requestedStatus === ElectionStatus::Active
            ? ElectionStatus::Draft
            : $requestedStatus;

        $election = Election::query()->create([
            'title' => $validated['title'],
            'slug' => SlugGenerator::unique($validated['title'], Election::class),
            'description' => $validated['description'] ?? null,
            'voting_starts_at' => $validated['voting_starts_at'] ?? null,
            'voting_ends_at' => $validated['voting_ends_at'] ?? null,
            'status' => $initialStatus,
            'created_by' => $request->user()->id,
            ...Election::scheduleAttributes(
                $initialStatus,
                $validated['voting_starts_at'] ?? null,
                $validated['voting_ends_at'] ?? null,
            ),
        ]);

        $this->setup->syncOnCreate($election, $validated);

        $this->logAdminAction(
            "Created election: {$election->title}",
            AuditActionType::Election,
            'election',
            $election->id,
        );

        // Assign before notifying so the creating admin is included in electionAdmins().
        if (! $request->user()->isSuperAdmin()) {
            $this->scope->assignElectionToAdmin($request->user(), $election, $request->user()->id);
        }

        $actor = $request->user();

        if ($requestedStatus === ElectionStatus::Active) {
            $this->elections->open($election->fresh(), $actor);
            $opened = $election->fresh();
            $opened->forceFill(Election::scheduleAttributes(
                ElectionStatus::Active,
                $opened->voting_starts_at,
                $validated['voting_ends_at'] ?? $opened->voting_ends_at,
            ))->save();
        } else {
            $this->notifications->electionCreated($election, $actor);
        }

        $this->announcements->generateForElectionCreated($election->fresh(), $actor);

        return redirect()->route('admin.elections.edit', $election)->with('success', 'Election created with positions and candidates.');
    }

    public function edit(Request $request, Election $election): View
    {
        $this->authorize('update', $election);

        $election->load(['candidates.category', 'candidates.partylist', 'partylists']);
        $election->loadCount(['votes']);
        $election->load(['categories' => fn ($query) => $query->withCount('votes')]);

        $attachedIds = $election->partylists->pluck('id');

        // Show Active campaigns plus any already attached (even if since deactivated).
        $campaigns = Partylist::query()
            ->where(fn ($q) => $q->selectableForElections()->orWhereIn('id', $attachedIds))
            ->orderBy('name')
            ->get();

        return view('admin.elections.edit', [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => AdminPortal::notificationCount($request->user()),
            'election' => $election,
            'statuses' => ElectionStatus::cases(),
            'campaigns' => $campaigns,
            'selectedPartylistIds' => collect(old('partylists', $attachedIds->all()))->map(fn ($id) => (int) $id)->all(),
        ]);
    }

    public function update(UpdateElectionRequest $request, Election $election): RedirectResponse
    {
        $validated = $request->validated();
        $previousStatus = $election->status;
        $newStatus = $validated['status'] instanceof ElectionStatus
            ? $validated['status']
            : ElectionStatus::from((string) $validated['status']);

        $election->update([
            'title' => $validated['title'],
            'slug' => $election->title !== $validated['title']
                ? SlugGenerator::unique($validated['title'], Election::class, $election->id)
                : $election->slug,
            'description' => $validated['description'] ?? null,
            'voting_starts_at' => $validated['voting_starts_at'] ?? null,
            'voting_ends_at' => $validated['voting_ends_at'] ?? null,
        ]);

        $this->setup->syncOnUpdate($election, $validated);

        $this->logAdminAction(
            "Updated election: {$election->title}",
            AuditActionType::Election,
            'election',
            $election->id,
        );

        $actor = $request->user();
        $election = $election->fresh();

        // Status transitions that open/close voting must go through the
        // lifecycle gateway so students receive the same notifications.
        if ($previousStatus !== ElectionStatus::Active && $newStatus === ElectionStatus::Active) {
            $this->elections->open($election, $actor);
        } elseif ($previousStatus !== ElectionStatus::Closed && $newStatus === ElectionStatus::Closed) {
            $this->elections->close($election, $actor);
        } else {
            $election->forceFill(['status' => $newStatus])->save();
            $this->notifications->electionUpdated($election->fresh(), $actor);
        }

        $election = $election->fresh();
        $election->forceFill(Election::scheduleAttributes(
            $election->status,
            $election->voting_starts_at,
            $election->voting_ends_at,
        ))->save();

        return back()->with('success', 'Election updated.');
    }

    public function destroy(DeleteElectionRequest $request, Election $election): RedirectResponse
    {
        $title = $election->title;
        $electionId = $election->id;

        $this->notifications->electionDeleted($election, $request->user());

        $election->delete();

        $this->logAdminAction(
            "Deleted election: {$title}",
            AuditActionType::Election,
            'election',
            $electionId,
        );

        return redirect()->route('admin.elections.index')->with('success', 'Election deleted successfully.');
    }
}

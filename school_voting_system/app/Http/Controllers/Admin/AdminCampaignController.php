<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditActionType;
use App\Enums\CampaignStatus;
use App\Http\Controllers\Admin\Concerns\LogsAdminActions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Campaign\StoreCampaignPosterRequest;
use App\Http\Requests\Admin\Campaign\StorePartylistRequest;
use App\Http\Requests\Admin\Campaign\UpdatePartylistRequest;
use App\Models\Partylist;
use App\Models\PartylistPoster;
use App\Services\Admin\AdminScopeService;
use App\Services\Media\ImageCompressionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminCampaignController extends Controller
{
    use LogsAdminActions;

    public function __construct(
        protected AdminScopeService $scope,
        protected ImageCompressionService $images,
    ) {}

    public function index(Request $request): View
    {
        // Campaigns are an independent, reusable pool shared across elections.
        $partylists = Partylist::query()
            ->withCount(['posters', 'candidates', 'elections'])
            ->with(['posters' => fn ($q) => $q->latest()])
            ->orderBy('name')
            ->get();

        return view('admin.campaigns.index', [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => $this->recoveryCount(),
            'partylists' => $partylists,
            'canManage' => $request->user()->can('create', Partylist::class),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Partylist::class);

        return view('admin.campaigns.create', [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => $this->recoveryCount(),
        ]);
    }

    public function store(StorePartylistRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $partylist = Partylist::query()->create([
            'name' => $validated['name'],
            'acronym' => $validated['acronym'] ?? null,
            'color' => $validated['color'] ?? null,
            'motto' => $validated['motto'] ?? null,
            'platform' => $validated['platform'] ?? null,
            'description' => $validated['description'] ?? null,
            'leader' => $validated['leader'] ?? null,
            'logo_path' => ($logo = $request->file('logo')) instanceof UploadedFile
                ? $this->images->storeOptimized($logo, 'campaign-logos')
                : null,
            'banner_path' => null,
            'banner_variants' => null,
            'status' => $validated['status'] ?? CampaignStatus::Draft->value,
        ]);

        if (($banner = $request->file('banner')) instanceof UploadedFile) {
            $set = $this->storeCampaignBannerSet($banner);
            $partylist->forceFill([
                'banner_path' => $set['path'],
                'banner_variants' => $set['variants'],
            ])->save();
        }

        // Optional legacy poster upload only makes sense once attached to an election.
        if (($poster = $request->file('poster_image')) instanceof UploadedFile && $partylist->elections()->exists()) {
            $this->createPosterForPartylist($partylist, $poster, $request->user());
        }

        $this->logAdminAction(
            "Created campaign: {$partylist->name}",
            AuditActionType::Election,
            'partylist',
            $partylist->id,
        );

        return redirect()->route('admin.campaigns.index')->with('success', 'Campaign created and available for elections.');
    }

    public function edit(Request $request, Partylist $partylist): View
    {
        $this->authorize('view', $partylist);
        $this->scope->assertPartylistInScope($request->user(), $partylist);

        return view('admin.campaigns.edit', [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => $this->recoveryCount(),
            'partylist' => $partylist,
        ]);
    }

    public function update(UpdatePartylistRequest $request, Partylist $partylist): RedirectResponse
    {
        $this->scope->assertPartylistInScope($request->user(), $partylist);

        $validated = $request->validated();

        $attributes = [
            'name' => $validated['name'],
            'acronym' => $validated['acronym'] ?? null,
            'color' => $validated['color'] ?? null,
            'motto' => $validated['motto'] ?? null,
            'platform' => $validated['platform'] ?? null,
            'description' => $validated['description'] ?? null,
            'leader' => $validated['leader'] ?? null,
            'status' => $validated['status'],
        ];

        if (($logo = $request->file('logo')) instanceof UploadedFile) {
            if ($partylist->logo_path) {
                Storage::disk('public')->delete($partylist->logo_path);
            }
            $attributes['logo_path'] = $this->images->storeOptimized($logo, 'campaign-logos');
        }

        if (($banner = $request->file('banner')) instanceof UploadedFile) {
            $this->deleteCampaignBannerFiles($partylist);
            $set = $this->storeCampaignBannerSet($banner);
            $attributes['banner_path'] = $set['path'];
            $attributes['banner_variants'] = $set['variants'];
        }

        $partylist->update($attributes);

        $this->logAdminAction(
            "Updated campaign: {$partylist->name}",
            AuditActionType::Election,
            'partylist',
            $partylist->id,
        );

        return redirect()->route('admin.campaigns.index')->with('success', 'Campaign updated.');
    }

    public function storePoster(StoreCampaignPosterRequest $request, Partylist $partylist): RedirectResponse
    {
        $this->scope->assertPartylistInScope($request->user(), $partylist);

        $electionId = $partylist->election_id ?? $partylist->elections()->value('elections.id');
        abort_unless($electionId, 422, 'Attach this campaign to an election before uploading posters.');

        $this->createPosterForPartylist($partylist, $request->file('poster_image'), $request->user(), $electionId);

        $this->logAdminAction(
            "Uploaded poster for campaign: {$partylist->name}",
            AuditActionType::Election,
            'partylist_poster',
            $partylist->id,
        );

        return back()->with('success', 'Poster uploaded and displayed on dashboards.');
    }

    public function destroy(Request $request, Partylist $partylist): RedirectResponse
    {
        $this->authorize('delete', $partylist);
        $this->scope->assertPartylistInScope($request->user(), $partylist);

        foreach ($partylist->posters as $poster) {
            $this->deletePosterFile($poster);
        }

        if ($partylist->logo_path) {
            Storage::disk('public')->delete($partylist->logo_path);
        }
        $this->deleteCampaignBannerFiles($partylist);

        $name = $partylist->name;
        $partylist->delete();

        $this->logAdminAction(
            "Deleted campaign: {$name}",
            AuditActionType::Election,
            'partylist',
        );

        return redirect()->route('admin.campaigns.index')->with('success', 'Campaign removed.');
    }

    protected function createPosterForPartylist(Partylist $partylist, UploadedFile $file, $user, ?int $electionId = null): PartylistPoster
    {
        return PartylistPoster::query()->create([
            'partylist_id' => $partylist->id,
            'election_id' => $electionId ?? $partylist->election_id,
            'title' => "{$partylist->name} Poster",
            'file_path' => $this->storePosterImage($file),
            'status' => PartylistPoster::STATUS_APPROVED,
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'submitted_at' => now(),
        ]);
    }

    protected function storePosterImage(UploadedFile $file): string
    {
        return $this->images->storeOptimized($file, 'campaign-posters');
    }

    /**
     * @return array{path: string, variants: array<string, mixed>}
     */
    protected function storeCampaignBannerSet(UploadedFile $file): array
    {
        $set = $this->images->storeOptimizedSet($file, 'campaign-banners', true);

        return [
            'path' => $set['path'],
            'variants' => [
                'medium_path' => $set['medium_path'],
                'mobile_path' => $set['mobile_path'],
                'thumb_path' => $set['thumb_path'],
                'orientation' => $set['orientation'],
                'width' => $set['width'],
                'height' => $set['height'],
            ],
        ];
    }

    protected function deleteCampaignBannerFiles(Partylist $partylist): void
    {
        if ($partylist->banner_path) {
            Storage::disk('public')->delete($partylist->banner_path);
        }

        foreach (($partylist->banner_variants ?? []) as $key => $path) {
            if (in_array($key, ['medium_path', 'mobile_path', 'thumb_path'], true) && filled($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    protected function deletePosterFile(PartylistPoster $poster): void
    {
        if ($poster->file_path) {
            Storage::disk('public')->delete($poster->file_path);
        }
    }
}

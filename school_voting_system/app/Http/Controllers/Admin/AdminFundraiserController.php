<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditActionType;
use App\Enums\FundraiserCategory;
use App\Enums\FundraiserStatus;
use App\Enums\FundraiserVisibility;
use App\Http\Controllers\Admin\Concerns\LogsAdminActions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Fundraiser\DeleteFundraiserRequest;
use App\Http\Requests\Admin\Fundraiser\StoreFundraiserRequest;
use App\Http\Requests\Admin\Fundraiser\UpdateFundraiserRequest;
use App\Models\Donation;
use App\Models\Fundraiser;
use App\Services\Media\ImageCompressionService;
use App\Services\Portal\AnnouncementService;
use App\Services\Portal\PortalNotificationService;
use App\Support\SlugGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminFundraiserController extends Controller
{
    use LogsAdminActions;

    public function __construct(
        protected PortalNotificationService $notifications,
        protected AnnouncementService $announcements,
        protected ImageCompressionService $images,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Fundraiser::class);

        $fundraisers = Fundraiser::query()
            ->withCount('donations')
            ->latest()
            ->paginate(15);

        return view('admin.fundraisers.index', [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => $this->recoveryCount(),
            'fundraisers' => $fundraisers,
        ]);
    }

    public function donations(Request $request): View
    {
        $this->authorize('viewAny', Fundraiser::class);

        $selectedFundraiser = $request->query('fundraiser');

        $donations = Donation::query()
            ->with(['fundraiser:id,title', 'donor:id,name'])
            ->when($selectedFundraiser, fn ($q) => $q->where('fundraiser_id', $selectedFundraiser))
            ->latest('donated_at')
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'total_raised' => (float) Donation::query()->sum('amount'),
            'total_donations' => Donation::query()->count(),
            'unique_donors' => Donation::query()->distinct('user_id')->count('user_id'),
            'active_fundraisers' => Fundraiser::query()->where('status', FundraiserStatus::Active)->count(),
        ];

        return view('admin.fundraisers.donations', [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => $this->recoveryCount(),
            'donations' => $donations,
            'summary' => $summary,
            'fundraisers' => Fundraiser::query()->orderBy('title')->get(['id', 'title']),
            'selectedFundraiser' => $selectedFundraiser,
        ]);
    }

    public function transactions(Request $request): View
    {
        $this->authorize('viewAny', Fundraiser::class);

        $selectedFundraiser = $request->query('fundraiser');

        $transactions = Donation::query()
            ->with(['fundraiser:id,title', 'donor:id,name'])
            ->when($selectedFundraiser, fn ($q) => $q->where('fundraiser_id', $selectedFundraiser))
            ->latest('donated_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.fundraisers.transactions', [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => $this->recoveryCount(),
            'transactions' => $transactions,
            'fundraisers' => Fundraiser::query()->orderBy('title')->get(['id', 'title']),
            'selectedFundraiser' => $selectedFundraiser,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Fundraiser::class);

        return view('admin.fundraisers.create', $this->formData($request));
    }

    public function store(StoreFundraiserRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $banner = $this->storeBanner($request->file('banner'));

        $fundraiser = Fundraiser::query()->create([
            ...$this->payloadFromValidated($validated),
            'slug' => SlugGenerator::unique($validated['title'], Fundraiser::class),
            'amount_raised' => 0,
            'banner_path' => $banner['path'] ?? null,
            'banner_variants' => $banner['variants'] ?? null,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        $this->logAdminAction('Created fundraiser: '.$fundraiser->title, AuditActionType::Election, 'fundraiser', $fundraiser->id);

        $this->notifications->fundraiserCreated(
            $fundraiser->title,
            $request->user(),
            $fundraiser->id,
            $fundraiser->isAcceptingDonations(),
        );
        $this->announcements->generateForFundraiserStarted($fundraiser, $request->user());

        return redirect()->route('admin.fundraisers.edit', $fundraiser)->with('success', 'Fundraising campaign created.');
    }

    public function edit(Request $request, Fundraiser $fundraiser): View
    {
        $this->authorize('view', $fundraiser);

        $fundraiser->loadCount('donations');
        $fundraiser->load(['creator:id,name', 'updater:id,name']);

        return view('admin.fundraisers.edit', [
            ...$this->formData($request),
            'fundraiser' => $fundraiser,
            'donationStats' => $fundraiser->donationStatistics(),
        ]);
    }

    public function preview(Request $request, Fundraiser $fundraiser): View
    {
        $this->authorize('view', $fundraiser);

        $fundraiser->loadMissing('donations');

        return view('student.fundraising.show', [
            'user' => $request->user()->loadCount('passkeys'),
            'fundraiser' => $fundraiser,
            'preview' => true,
        ]);
    }

    public function update(UpdateFundraiserRequest $request, Fundraiser $fundraiser): RedirectResponse
    {
        $validated = $request->validated();
        $payload = $this->payloadFromValidated($validated);
        $payload['slug'] = SlugGenerator::unique($validated['title'], Fundraiser::class, $fundraiser->id);
        $payload['updated_by'] = $request->user()->id;

        if ($request->hasFile('banner')) {
            $this->deleteBannerFiles($fundraiser);
            $banner = $this->storeBanner($request->file('banner'));
            $payload['banner_path'] = $banner['path'] ?? null;
            $payload['banner_variants'] = $banner['variants'] ?? null;
        }

        $wasAccepting = $fundraiser->isAcceptingDonations();

        $fundraiser->update($payload);
        $fundraiser->refresh();

        $this->logAdminAction('Updated fundraiser: '.$fundraiser->title, AuditActionType::Election, 'fundraiser', $fundraiser->id);

        $becameVisibleToStudents = ! $wasAccepting && $fundraiser->isAcceptingDonations();

        $this->notifications->fundraiserUpdated(
            $fundraiser->title,
            $request->user(),
            $fundraiser->id,
            $becameVisibleToStudents,
        );

        return redirect()->route('admin.fundraisers.edit', $fundraiser)->with('success', 'Fundraising campaign updated.');
    }

    public function destroy(DeleteFundraiserRequest $request, Fundraiser $fundraiser): RedirectResponse
    {
        $title = $fundraiser->title;
        $fundraiserId = $fundraiser->id;

        // Soft delete — keep banner files for possible restore.
        $fundraiser->delete();

        $this->logAdminAction('Deleted fundraiser: '.$title, AuditActionType::Election, 'fundraiser', $fundraiserId);

        return redirect()->route('admin.fundraisers.index')->with('success', 'Activity deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function formData(Request $request): array
    {
        return [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => $this->recoveryCount(),
            'statuses' => FundraiserStatus::manualCases(),
            'categories' => FundraiserCategory::cases(),
            'visibilities' => FundraiserVisibility::cases(),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function payloadFromValidated(array $validated): array
    {
        return [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'] ?? null,
            'beneficiary' => $validated['beneficiary'] ?? null,
            'purpose' => $validated['purpose'] ?? null,
            'expected_beneficiaries' => $validated['expected_beneficiaries'] ?? null,
            'goal_amount' => $validated['goal_amount'],
            'min_donation' => $validated['min_donation'] ?? null,
            'max_donation' => $validated['max_donation'] ?? null,
            'allow_anonymous' => (bool) ($validated['allow_anonymous'] ?? true),
            'generate_receipt' => (bool) ($validated['generate_receipt'] ?? true),
            'accept_cash' => (bool) ($validated['accept_cash'] ?? true),
            'accept_gcash' => (bool) ($validated['accept_gcash'] ?? true),
            'accept_maya' => (bool) ($validated['accept_maya'] ?? true),
            'accept_bank_transfer' => (bool) ($validated['accept_bank_transfer'] ?? true),
            'visibility' => $validated['visibility'] ?? FundraiserVisibility::Public->value,
            'is_featured' => (bool) ($validated['is_featured'] ?? false),
            'accept_donations' => (bool) ($validated['accept_donations'] ?? true),
            'starts_on' => $validated['starts_on'] ?? null,
            'ends_on' => $validated['ends_on'] ?? null,
            'status' => $validated['status'],
        ];
    }

    /**
     * @return array{path: ?string, variants: ?array<string, mixed>}
     */
    protected function storeBanner(?UploadedFile $file): array
    {
        if (! $file) {
            return ['path' => null, 'variants' => null];
        }

        $set = $this->images->storeOptimizedSet($file, 'fundraisers/banners', true);

        return [
            'path' => $set['path'],
            'variants' => [
                'medium_path' => $set['medium_path'] ?? null,
                'mobile_path' => $set['mobile_path'] ?? null,
                'thumb_path' => $set['thumb_path'] ?? null,
                'orientation' => $set['orientation'] ?? null,
                'width' => $set['width'] ?? null,
                'height' => $set['height'] ?? null,
            ],
        ];
    }

    protected function deleteBannerFiles(Fundraiser $fundraiser): void
    {
        $paths = array_filter([
            $fundraiser->banner_path,
            ...array_values(array_filter([
                ($fundraiser->banner_variants ?? [])['medium_path'] ?? null,
                ($fundraiser->banner_variants ?? [])['mobile_path'] ?? null,
                ($fundraiser->banner_variants ?? [])['thumb_path'] ?? null,
            ])),
        ]);

        if ($paths !== []) {
            Storage::disk('public')->delete($paths);
        }
    }
}

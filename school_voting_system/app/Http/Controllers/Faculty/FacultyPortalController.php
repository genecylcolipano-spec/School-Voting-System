<?php

namespace App\Http\Controllers\Faculty;

use App\Enums\FundraiserStatus;
use App\Enums\FundraiserVisibility;
use App\Exceptions\DonationIntegrityException;
use App\Exceptions\PayMongoException;
use App\Http\Controllers\Concerns\ManagesPortalNotifications;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\DonateToFundraiserRequest;
use App\Models\Announcement;
use App\Models\AnnouncementAttachment;
use App\Models\Donation;
use App\Models\Election;
use App\Models\Event;
use App\Models\Fundraiser;
use App\Services\Payments\DonationCheckoutService;
use App\Services\Portal\AnnouncementService;
use App\Support\AdminPortal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FacultyPortalController extends Controller
{
    use ManagesPortalNotifications;

    public function __construct(
        protected AnnouncementService $announcements,
        protected DonationCheckoutService $donationCheckout,
    ) {}

    public function elections(Request $request): View
    {
        $showOpenOnly = $request->string('filter')->toString() === 'open';

        $elections = Election::query()
            ->visibleToCampus()
            ->when($showOpenOnly, fn ($query) => $query->acceptingVotes())
            ->orderByDesc('voting_starts_at')
            ->paginate(10)
            ->withQueryString();

        return view('faculty.elections.index', [
            ...$this->portalData($request),
            'elections' => $elections,
            'showOpenOnly' => $showOpenOnly,
            'openCount' => Election::query()->visibleToCampus()->acceptingVotes()->count(),
            'allCount' => Election::query()->visibleToCampus()->count(),
        ]);
    }

    public function electionShow(Request $request, Election $election): View
    {
        abort_unless($election->isVisibleToCampus(), 404);

        $election->loadMissing([
            'categories',
            'activeCandidates',
            'activeCandidates.user',
        ]);

        return view('faculty.elections.show', [
            ...$this->portalData($request),
            'election' => $election,
        ]);
    }

    public function events(Request $request): View
    {
        Event::markOverdueAsCompleted();

        $showUpcomingOnly = $request->string('filter')->toString() === 'upcoming';

        $events = Event::query()
            ->when(
                $showUpcomingOnly,
                fn ($query) => $query->upcoming()->orderBy('event_date'),
                fn ($query) => $query->campusListing(),
            )
            ->paginate(12)
            ->withQueryString();

        return view('faculty.events.index', [
            ...$this->portalData($request),
            'events' => $events,
            'showUpcomingOnly' => $showUpcomingOnly,
            'upcomingCount' => Event::query()->upcoming()->count(),
            'allCount' => Event::query()->visibleToCampus()->count(),
        ]);
    }

    public function eventShow(Request $request, Event $event): View
    {
        abort_unless($event->isVisibleToCampus(), 404);
        Event::markOverdueAsCompleted();
        $event->refresh();

        return view('faculty.events.show', [
            ...$this->portalData($request),
            'event' => $event,
        ]);
    }

    public function announcements(Request $request): View
    {
        $user = $request->user();

        $announcements = Announcement::query()
            ->published()
            ->visibleToUser($user)
            ->with('attachments')
            ->paginate(12);

        return view('faculty.announcements.index', [
            ...$this->portalData($request),
            'announcements' => $announcements,
        ]);
    }

    public function announcementShow(Request $request, Announcement $announcement): View
    {
        abort_unless($announcement->isLive(), 404);
        abort_unless($this->announcements->userCanView($announcement, $request->user()), 403);

        $announcement->load('attachments');
        $this->announcements->recordView($announcement, $request->user());

        return view('faculty.announcements.show', [
            ...$this->portalData($request),
            'announcement' => $announcement,
        ]);
    }

    public function downloadAnnouncementAttachment(
        Request $request,
        Announcement $announcement,
        AnnouncementAttachment $attachment,
    ): StreamedResponse {
        abort_unless($announcement->isLive(), 404);
        abort_unless($attachment->announcement_id === $announcement->id, 404);
        abort_unless($this->announcements->userCanView($announcement, $request->user()), 403);
        abort_unless(Storage::disk('public')->exists($attachment->path), 404);

        $attachment->increment('download_count');

        return Storage::disk('public')->download($attachment->path, $attachment->original_name);
    }

    public function fundraising(Request $request): View
    {
        $fundraisers = Fundraiser::query()
            ->visibleToStudents()
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('faculty.fundraising.index', [
            ...$this->portalData($request),
            'fundraisers' => $fundraisers,
        ]);
    }

    public function fundraiserShow(Request $request, Fundraiser $fundraiser): View
    {
        abort_if(
            in_array($fundraiser->status, [
                FundraiserStatus::Draft,
                FundraiserStatus::Archived,
                FundraiserStatus::Cancelled,
            ], true)
            || $fundraiser->visibility === FundraiserVisibility::Hidden,
            404
        );

        $fundraiser->loadMissing('donations');

        return view('faculty.fundraising.show', [
            ...$this->portalData($request),
            'fundraiser' => $fundraiser,
            'paymentMethods' => $fundraiser->acceptedPaymentMethods(),
            'paymongoConfigured' => $this->donationCheckout->isOnlinePaymentsConfigured(),
        ]);
    }

    public function donate(DonateToFundraiserRequest $request, Fundraiser $fundraiser): RedirectResponse
    {
        try {
            $result = $this->donationCheckout->start(
                donor: $request->user(),
                fundraiser: $fundraiser,
                amount: $request->donationAmount(),
                method: $request->paymentMethod(),
                message: $request->donationMessage(),
                anonymous: $request->isAnonymous(),
            );
        } catch (DonationIntegrityException|PayMongoException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        if (filled($result['checkout_url'])) {
            return redirect()->away($result['checkout_url']);
        }

        return back()->with('success', $result['message']);
    }

    public function donateReturn(Request $request, Fundraiser $fundraiser): RedirectResponse
    {
        $donation = $this->ownedDonation($request, $fundraiser, $request->query('donation'));

        if ($donation && $donation->payment_method?->isOnline()) {
            $this->donationCheckout->syncFromPayMongo($donation);
            $donation->refresh();
        }

        if ($donation?->isPaid()) {
            return redirect()
                ->route('faculty.fundraising.show', $fundraiser)
                ->with('success', 'Thank you! Your donation of ₱'.number_format((float) $donation->amount, 2).' was received.');
        }

        return redirect()
            ->route('faculty.fundraising.show', $fundraiser)
            ->with('success', 'If your payment went through, it will appear here after PayMongo confirms it. This can take a few seconds.');
    }

    public function donateCancel(Request $request, Fundraiser $fundraiser): RedirectResponse
    {
        $donation = $this->ownedDonation($request, $fundraiser, $request->query('donation'));

        if ($donation?->isPending()) {
            $donation->markCancelled();
        }

        return redirect()
            ->route('faculty.fundraising.show', $fundraiser)
            ->with('error', 'Payment was cancelled. No amount was charged.');
    }

    public function notifications(Request $request): View
    {
        return $this->notificationIndexView(
            $request,
            'faculty.notifications.index',
            route('faculty.notifications.index'),
        );
    }

    public function notificationsFeed(Request $request): JsonResponse
    {
        return $this->feedJson($request);
    }

    protected function ownedDonation(Request $request, Fundraiser $fundraiser, mixed $donationId): ?Donation
    {
        if (! is_numeric($donationId)) {
            return null;
        }

        return Donation::query()
            ->whereKey((int) $donationId)
            ->where('fundraiser_id', $fundraiser->id)
            ->where('user_id', $request->user()->id)
            ->first();
    }

    /**
     * @return array{user: \App\Models\User, notificationsCount: int}
     */
    protected function portalData(Request $request): array
    {
        $user = $request->user()->loadCount('passkeys');

        return [
            'user' => $user,
            'notificationsCount' => AdminPortal::notificationCount($user),
        ];
    }
}

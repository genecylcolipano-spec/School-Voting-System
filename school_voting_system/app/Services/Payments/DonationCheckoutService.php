<?php

namespace App\Services\Payments;

use App\Enums\DonationPaymentMethod;
use App\Enums\DonationStatus;
use App\Exceptions\DonationIntegrityException;
use App\Exceptions\PayMongoException;
use App\Models\Donation;
use App\Models\Fundraiser;
use App\Models\User;
use App\Services\Portal\PortalNotificationService;
use Illuminate\Support\Facades\Log;

class DonationCheckoutService
{
    public function __construct(
        protected PayMongoClient $paymongo,
        protected PortalNotificationService $notifications,
    ) {}

    public function onlineMinimumAmount(): float
    {
        return max(1.0, (float) config('services.paymongo.min_amount', 20));
    }

    public function isOnlinePaymentsConfigured(): bool
    {
        return $this->paymongo->isConfigured();
    }

    /**
     * @return array{donation: Donation, checkout_url: ?string, message: string}
     */
    public function start(
        User $donor,
        Fundraiser $fundraiser,
        float|string $amount,
        DonationPaymentMethod $method,
        ?string $message = null,
        bool $anonymous = false,
    ): array {
        if (! $method->isAcceptedBy($fundraiser)) {
            throw new DonationIntegrityException('This campaign does not accept '.$method->label().' payments.');
        }

        $amount = round((float) $amount, 2);

        if ($method->isOnline()) {
            $minOnline = $this->onlineMinimumAmount();
            if ($amount < $minOnline) {
                throw new DonationIntegrityException(
                    'Online payments require a minimum of ₱'.number_format($minOnline, 2).'.'
                );
            }

            if (! $this->paymongo->isConfigured()) {
                throw new PayMongoException('Online payments are not configured yet. Please contact the administrator.');
            }
        }

        $donation = Donation::record($donor, $fundraiser, $amount, [
            'message' => $message,
            'is_anonymous' => $anonymous,
            'currency' => 'PHP',
            'payment_method' => $method,
            'status' => DonationStatus::Pending,
        ]);

        if (! $method->isOnline()) {
            return [
                'donation' => $donation,
                'checkout_url' => null,
                'message' => 'Your '.$method->label().' donation of ₱'.number_format($amount, 2).' is pending confirmation by the campaign organizer.',
            ];
        }

        try {
            $session = $this->paymongo->createCheckoutSession($this->checkoutAttributes($donation, $fundraiser, $donor, $method));
        } catch (PayMongoException $exception) {
            $donation->markFailed();

            throw $exception;
        }

        $sessionId = is_string($session['id'] ?? null) ? $session['id'] : null;
        $checkoutUrl = data_get($session, 'attributes.checkout_url');

        if (! is_string($sessionId) || ! is_string($checkoutUrl) || $checkoutUrl === '') {
            $donation->markFailed();

            throw new PayMongoException('PayMongo did not return a checkout URL.');
        }

        $donation->forceFill([
            'paymongo_checkout_session_id' => $sessionId,
        ])->save();

        return [
            'donation' => $donation->fresh(),
            'checkout_url' => $checkoutUrl,
            'message' => 'Redirecting to PayMongo to complete your donation.',
        ];
    }

    /**
     * Confirm a paid PayMongo session. Idempotent.
     *
     * @param  array<string, mixed>  $resource
     */
    public function fulfillFromCheckoutSession(array $resource, bool $assumePaid = false): ?Donation
    {
        $donation = $this->donationFromCheckoutSession($resource);

        if (! $donation) {
            Log::warning('PayMongo checkout could not be matched to a donation.', [
                'checkout_session_id' => $resource['id'] ?? null,
                'reference' => data_get($resource, 'attributes.reference_number'),
            ]);

            return null;
        }

        if (! $assumePaid && ! $this->sessionHasPaidPayment($resource, $donation)) {
            return $donation;
        }

        $paymentId = $this->paidPaymentId($resource);
        $newlyPaid = $donation->markPaid($paymentId, is_string($resource['id'] ?? null) ? $resource['id'] : null);

        if ($newlyPaid) {
            $donation->refresh();
            $this->notifyPaid($donation);
        }

        return $donation->fresh();
    }

    public function syncFromPayMongo(Donation $donation): Donation
    {
        if ($donation->isPaid() || ! filled($donation->paymongo_checkout_session_id)) {
            return $donation;
        }

        try {
            $session = $this->paymongo->retrieveCheckoutSession($donation->paymongo_checkout_session_id);
        } catch (PayMongoException $exception) {
            Log::info('PayMongo checkout retrieve failed.', [
                'donation_id' => $donation->id,
                'message' => $exception->getMessage(),
            ]);

            return $donation;
        }

        return $this->fulfillFromCheckoutSession($session) ?? $donation->fresh();
    }

    /**
     * @param  array<string, mixed>  $event
     */
    public function handleWebhookEvent(array $event): ?Donation
    {
        $type = $this->eventType($event);

        if (in_array($type, ['checkout_session.payment.paid', 'checkout_session.payment.failed'], true)) {
            $resource = $this->eventResource($event);
            if ($resource === []) {
                return null;
            }

            if ($type === 'checkout_session.payment.failed') {
                $donation = $this->donationFromCheckoutSession($resource);
                $donation?->markFailed();

                return $donation;
            }

            return $this->fulfillFromCheckoutSession($resource, assumePaid: true);
        }

        if (in_array($type, ['payment.paid', 'payment.failed'], true)) {
            $resource = $this->eventResource($event);
            $donation = $this->donationFromPayment($resource);

            if (! $donation) {
                return null;
            }

            if ($type === 'payment.failed') {
                $donation->markFailed();

                return $donation;
            }

            $amount = (int) data_get($resource, 'attributes.amount');
            if ($amount > 0 && $amount !== $this->amountInCentavos((float) $donation->amount)) {
                Log::warning('PayMongo payment amount did not match donation.', [
                    'donation_id' => $donation->id,
                    'expected' => $this->amountInCentavos((float) $donation->amount),
                    'received' => $amount,
                ]);

                return $donation;
            }

            $paymentId = is_string($resource['id'] ?? null) ? $resource['id'] : null;
            $newlyPaid = $donation->markPaid($paymentId);

            if ($newlyPaid) {
                $this->notifyPaid($donation->fresh());
            }

            return $donation->fresh();
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function checkoutAttributes(
        Donation $donation,
        Fundraiser $fundraiser,
        User $donor,
        DonationPaymentMethod $method,
    ): array {
        $amount = $this->amountInCentavos((float) $donation->amount);
        $title = $fundraiser->title;
        $successUrl = route('student.fundraising.donate.return', [
            'fundraiser' => $fundraiser,
            'donation' => $donation->id,
        ]);
        $cancelUrl = route('student.fundraising.donate.cancel', [
            'fundraiser' => $fundraiser,
            'donation' => $donation->id,
        ]);

        return [
            'billing' => [
                'name' => $donor->name,
                'email' => $donor->email,
            ],
            'send_email_receipt' => (bool) $fundraiser->generate_receipt,
            'show_description' => true,
            'show_line_items' => true,
            'description' => 'Donation to '.$title,
            'line_items' => [[
                'currency' => 'PHP',
                'amount' => $amount,
                'name' => 'Donation — '.$title,
                'description' => 'School fundraising donation',
                'quantity' => 1,
            ]],
            'payment_method_types' => [$method->paymongoType()],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'reference_number' => 'DON-'.$donation->id,
            'metadata' => [
                'donation_id' => (string) $donation->id,
                'fundraiser_id' => (string) $fundraiser->id,
                'user_id' => (string) $donor->id,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $resource
     */
    protected function donationFromCheckoutSession(array $resource): ?Donation
    {
        $sessionId = is_string($resource['id'] ?? null) ? $resource['id'] : null;
        $donationId = data_get($resource, 'attributes.metadata.donation_id');
        $reference = data_get($resource, 'attributes.reference_number');

        $query = Donation::query();

        if ($sessionId) {
            $bySession = (clone $query)->where('paymongo_checkout_session_id', $sessionId)->first();
            if ($bySession) {
                return $bySession;
            }
        }

        if (is_numeric($donationId)) {
            return Donation::query()->find((int) $donationId);
        }

        if (is_string($reference) && preg_match('/^DON-(\d+)$/', $reference, $matches)) {
            return Donation::query()->find((int) $matches[1]);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $resource
     */
    protected function donationFromPayment(array $resource): ?Donation
    {
        $paymentId = is_string($resource['id'] ?? null) ? $resource['id'] : null;
        $donationId = data_get($resource, 'attributes.metadata.donation_id');

        if ($paymentId) {
            $byPayment = Donation::query()->where('paymongo_payment_id', $paymentId)->first();
            if ($byPayment) {
                return $byPayment;
            }
        }

        if (is_numeric($donationId)) {
            return Donation::query()->find((int) $donationId);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $resource
     */
    protected function sessionHasPaidPayment(array $resource, Donation $donation): bool
    {
        $payments = data_get($resource, 'attributes.payments', []);
        if (! is_array($payments) || $payments === []) {
            $paymentIntentStatus = data_get($resource, 'attributes.payment_intent.attributes.status');

            return in_array($paymentIntentStatus, ['succeeded', 'paid'], true);
        }

        $expected = $this->amountInCentavos((float) $donation->amount);

        foreach ($payments as $payment) {
            $status = data_get($payment, 'attributes.status') ?? data_get($payment, 'status');
            $amount = (int) (data_get($payment, 'attributes.amount') ?? data_get($payment, 'amount') ?? 0);

            if (in_array($status, ['paid', 'succeeded'], true) && ($amount === 0 || $amount === $expected)) {
                return true;
            }

            if (in_array($status, ['paid', 'succeeded'], true) && $amount !== $expected) {
                Log::warning('PayMongo checkout payment amount did not match donation.', [
                    'donation_id' => $donation->id,
                    'expected' => $expected,
                    'received' => $amount,
                ]);
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $resource
     */
    protected function paidPaymentId(array $resource): ?string
    {
        $payments = data_get($resource, 'attributes.payments', []);
        if (! is_array($payments)) {
            return null;
        }

        foreach ($payments as $payment) {
            $status = data_get($payment, 'attributes.status') ?? data_get($payment, 'status');
            $id = $payment['id'] ?? data_get($payment, 'attributes.id');

            if (in_array($status, ['paid', 'succeeded'], true) && is_string($id)) {
                return $id;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $event
     */
    protected function eventType(array $event): ?string
    {
        $type = data_get($event, 'data.attributes.type')
            ?? data_get($event, 'data.type')
            ?? data_get($event, 'type');

        return is_string($type) ? $type : null;
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<string, mixed>
     */
    protected function eventResource(array $event): array
    {
        $resource = data_get($event, 'data.attributes.data')
            ?? data_get($event, 'data.data')
            ?? data_get($event, 'data');

        return is_array($resource) ? $resource : [];
    }

    protected function notifyPaid(Donation $donation): void
    {
        $donation->loadMissing(['fundraiser', 'donor']);

        $this->notifications->donationReceived(
            $donation->fundraiser?->title ?? 'Fundraising campaign',
            (float) $donation->amount,
            $donation->donor,
            $donation->donor,
            $donation->id,
        );
    }

    protected function amountInCentavos(float $amount): int
    {
        return (int) round($amount * 100);
    }
}

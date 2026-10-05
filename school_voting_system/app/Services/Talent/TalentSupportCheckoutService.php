<?php

namespace App\Services\Talent;

use App\Enums\DonationPaymentMethod;
use App\Enums\DonationStatus;
use App\Exceptions\PayMongoException;
use App\Exceptions\VoteIntegrityException;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\TalentEventEntryView;
use App\Models\TalentEventVote;
use App\Models\TalentVoteOrder;
use App\Models\User;
use App\Services\Payments\PayMongoClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TalentSupportCheckoutService
{
    public function __construct(
        protected PayMongoClient $paymongo,
    ) {}

    public function onlineMinimumAmount(): float
    {
        return max(20.0, (float) config('services.paymongo.min_amount', 20));
    }

    /**
     * @return array{order: TalentVoteOrder, checkout_url: string, message: string}
     */
    public function start(User $student, TalentEventEntry $entry, int $quantity): array
    {
        $entry->loadMissing('talentEvent');
        $event = $entry->talentEvent;

        $this->assertCanPurchase($student, $entry, $event, $quantity);

        $unitPrice = $event->supportVotePrice();
        $amount = round($unitPrice * $quantity, 2);
        $minOnline = $this->onlineMinimumAmount();

        if ($amount < $minOnline) {
            throw new VoteIntegrityException(
                'QR payments require a minimum of ₱'.number_format($minOnline, 2).'.'
            );
        }

        if (! $this->paymongo->isConfigured()) {
            throw new PayMongoException('Online payments are not configured yet. Please contact the administrator.');
        }

        $order = TalentVoteOrder::query()->create([
            'talent_event_id' => $event->id,
            'talent_event_entry_id' => $entry->id,
            'user_id' => $student->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'amount' => $amount,
            'currency' => 'PHP',
            'status' => DonationStatus::Pending,
            'payment_method' => DonationPaymentMethod::Qrph,
        ]);

        try {
            $session = $this->paymongo->createCheckoutSession($this->checkoutAttributes($order, $event, $entry, $student));
        } catch (PayMongoException $exception) {
            $order->forceFill(['status' => DonationStatus::Failed])->save();

            throw $exception;
        }

        $sessionId = is_string($session['id'] ?? null) ? $session['id'] : null;
        $checkoutUrl = data_get($session, 'attributes.checkout_url');

        if (! is_string($sessionId) || ! is_string($checkoutUrl) || $checkoutUrl === '') {
            $order->forceFill(['status' => DonationStatus::Failed])->save();

            throw new PayMongoException('PayMongo did not return a checkout URL.');
        }

        $order->forceFill(['paymongo_checkout_session_id' => $sessionId])->save();

        return [
            'order' => $order->fresh(),
            'checkout_url' => $checkoutUrl,
            'message' => 'Redirecting to PayMongo to complete your support votes.',
        ];
    }

    public function syncFromPayMongo(TalentVoteOrder $order): TalentVoteOrder
    {
        if ($order->isPaid() || $order->votesWereCredited() || ! filled($order->paymongo_checkout_session_id)) {
            return $order;
        }

        try {
            $session = $this->paymongo->retrieveCheckoutSession($order->paymongo_checkout_session_id);
        } catch (PayMongoException $exception) {
            Log::info('PayMongo talent support checkout retrieve failed.', [
                'order_id' => $order->id,
                'message' => $exception->getMessage(),
            ]);

            return $order;
        }

        return $this->fulfillFromCheckoutSession($session) ?? $order->fresh();
    }

    /**
     * @param  array<string, mixed>  $event
     */
    public function handleWebhookEvent(array $event): ?TalentVoteOrder
    {
        $type = $this->eventType($event);
        $resource = $this->eventResource($event);

        if ($resource === []) {
            return null;
        }

        if (in_array($type, ['checkout_session.payment.paid', 'checkout_session.payment.failed'], true)) {
            if (! $this->isTalentResource($resource)) {
                return null;
            }

            if ($type === 'checkout_session.payment.failed') {
                $order = $this->orderFromCheckoutSession($resource);
                if ($order?->isPending()) {
                    $order->forceFill(['status' => DonationStatus::Failed])->save();
                }

                return $order?->fresh();
            }

            return $this->fulfillFromCheckoutSession($resource, assumePaid: true);
        }

        if (in_array($type, ['payment.paid', 'payment.failed'], true)) {
            $order = $this->orderFromPayment($resource);
            if (! $order) {
                return null;
            }

            if ($type === 'payment.failed') {
                if ($order->isPending()) {
                    $order->forceFill(['status' => DonationStatus::Failed])->save();
                }

                return $order->fresh();
            }

            $this->fulfillPaidOrder($order, is_string($resource['id'] ?? null) ? $resource['id'] : null);

            return $order->fresh();
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $resource
     */
    public function fulfillFromCheckoutSession(array $resource, bool $assumePaid = false): ?TalentVoteOrder
    {
        $order = $this->orderFromCheckoutSession($resource);

        if (! $order) {
            return null;
        }

        if (! $assumePaid && ! $this->sessionHasPaidPayment($resource, $order)) {
            return $order;
        }

        $this->fulfillPaidOrder(
            $order,
            $this->paidPaymentId($resource),
            is_string($resource['id'] ?? null) ? $resource['id'] : null,
        );

        return $order->fresh();
    }

    public function cancelUnpaidOrders(TalentEvent $event): int
    {
        $orders = TalentVoteOrder::query()
            ->where('talent_event_id', $event->id)
            ->where('status', DonationStatus::Pending)
            ->get();

        foreach ($orders as $order) {
            $this->cancelUnpaidOrder($order);
        }

        return $orders->count();
    }

    public function cancelUnpaidOrder(TalentVoteOrder $order): void
    {
        if (! $order->isPending()) {
            return;
        }

        if (filled($order->paymongo_checkout_session_id)) {
            try {
                $this->paymongo->expireCheckoutSession($order->paymongo_checkout_session_id);
            } catch (PayMongoException) {
                // Local cancel still stands if PayMongo already expired the session.
            }
        }

        $order->forceFill([
            'status' => DonationStatus::Cancelled,
            'cancelled_at' => now(),
        ])->save();
    }

    protected function fulfillPaidOrder(TalentVoteOrder $order, ?string $paymentId = null, ?string $checkoutSessionId = null): void
    {
        DB::transaction(function () use ($order, $paymentId, $checkoutSessionId) {
            $locked = TalentVoteOrder::query()->whereKey($order->id)->lockForUpdate()->first();

            if (! $locked || $locked->votesWereCredited()) {
                return;
            }

            if ($checkoutSessionId) {
                $locked->paymongo_checkout_session_id = $checkoutSessionId;
            }
            if ($paymentId) {
                $locked->paymongo_payment_id = $paymentId;
            }

            $event = TalentEvent::withTrashed()->whereKey($locked->talent_event_id)->lockForUpdate()->first();

            if (! $event || $event->trashed() || ! $event->usesPaidSupport() || ! $event->isAcceptingVotes()) {
                $this->rejectLatePayment($locked, $event);

                return;
            }

            if ($locked->status !== DonationStatus::Paid) {
                $locked->forceFill([
                    'status' => DonationStatus::Paid,
                    'paid_at' => now(),
                ]);
            }

            $this->creditVotes($locked);
            $event->increment('support_amount_raised', (float) $locked->amount);
            $locked->votes_credited_at = now();
            $locked->save();
        });
    }

    protected function rejectLatePayment(TalentVoteOrder $order, ?TalentEvent $event): void
    {
        if ($order->votesWereCredited() || $order->cancelled_at) {
            return;
        }

        $refunded = false;
        $paymentId = $order->paymongo_payment_id;

        if (! $paymentId && filled($order->paymongo_checkout_session_id)) {
            try {
                $session = $this->paymongo->retrieveCheckoutSession($order->paymongo_checkout_session_id);
                $paymentId = $this->paidPaymentId($session);
                if ($paymentId) {
                    $order->paymongo_payment_id = $paymentId;
                }
            } catch (PayMongoException) {
                $paymentId = null;
            }
        }

        if ($paymentId) {
            try {
                $this->paymongo->createRefund(
                    $paymentId,
                    $this->amountInCentavos((float) $order->amount),
                );
                $refunded = true;
            } catch (PayMongoException $exception) {
                Log::warning('Talent support refund after close failed.', [
                    'order_id' => $order->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        $order->forceFill([
            'status' => DonationStatus::Cancelled,
            'cancelled_at' => now(),
            'votes_credited_at' => null,
        ])->save();

        if (! $refunded && $paymentId && $event && ! $event->trashed()) {
            $event->increment('support_amount_raised', (float) $order->amount);
        }
    }

    protected function creditVotes(TalentVoteOrder $order): void
    {
        $now = now();
        $rows = [];

        for ($i = 0; $i < $order->quantity; $i++) {
            $rows[] = [
                'talent_event_id' => $order->talent_event_id,
                'talent_event_entry_id' => $order->talent_event_entry_id,
                'user_id' => $order->user_id,
                'talent_vote_order_id' => $order->id,
                'voted_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows !== []) {
            TalentEventVote::query()->insert($rows);
        }
    }

    protected function assertCanPurchase(User $student, TalentEventEntry $entry, ?TalentEvent $event, int $quantity): void
    {
        if ($quantity < 1) {
            throw new VoteIntegrityException('Choose at least 1 support vote.');
        }

        if ($quantity > 10000) {
            throw new VoteIntegrityException('That quantity is too large for one checkout.');
        }

        if (! $student->canVote()) {
            throw new VoteIntegrityException('Only eligible students can support talent contestants.');
        }

        if (! $entry->isApproved()) {
            throw new VoteIntegrityException('This entry is not approved for voting.');
        }

        if (! $event || ! $event->isPublishedToStudents()) {
            throw new VoteIntegrityException('This talent event is not available to students.');
        }

        if (! $event->usesPaidSupport()) {
            throw new VoteIntegrityException('This competition uses one free vote per student.');
        }

        if (! $event->isAcceptingVotes()) {
            throw new VoteIntegrityException('This talent event is not currently accepting votes.');
        }

        if ($entry->hasVideo() && ! TalentEventEntryView::query()
            ->where('user_id', $student->id)
            ->where('talent_event_entry_id', $entry->id)
            ->exists()) {
            throw new VoteIntegrityException('Watch the performance before supporting this entry.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function checkoutAttributes(
        TalentVoteOrder $order,
        TalentEvent $event,
        TalentEventEntry $entry,
        User $student,
    ): array {
        $amount = $this->amountInCentavos((float) $order->amount);
        $successUrl = route('student.talent-voting.support.return', [
            'talentEvent' => $event,
            'order' => $order->id,
        ]);
        $cancelUrl = route('student.talent-voting.support.cancel', [
            'talentEvent' => $event,
            'order' => $order->id,
        ]);

        return [
            'billing' => [
                'name' => $student->name,
                'email' => $student->email,
            ],
            'send_email_receipt' => false,
            'show_description' => true,
            'show_line_items' => true,
            'description' => $order->quantity.' support vote(s) for '.$entry->display_name,
            'line_items' => [[
                'currency' => 'PHP',
                'amount' => $amount,
                'name' => 'Support votes — '.$event->title,
                'description' => $order->quantity.' × ₱'.number_format((float) $order->unit_price, 2).' for '.$entry->display_name,
                'quantity' => 1,
            ]],
            'payment_method_types' => [DonationPaymentMethod::Qrph->paymongoType()],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'reference_number' => 'TVS-'.$order->id,
            'metadata' => [
                'talent_vote_order_id' => (string) $order->id,
                'talent_event_id' => (string) $event->id,
                'user_id' => (string) $student->id,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $resource
     */
    protected function isTalentResource(array $resource): bool
    {
        return filled(data_get($resource, 'attributes.metadata.talent_vote_order_id'))
            || (is_string(data_get($resource, 'attributes.reference_number'))
                && str_starts_with((string) data_get($resource, 'attributes.reference_number'), 'TVS-'));
    }

    /**
     * @param  array<string, mixed>  $resource
     */
    protected function orderFromCheckoutSession(array $resource): ?TalentVoteOrder
    {
        $sessionId = is_string($resource['id'] ?? null) ? $resource['id'] : null;
        $orderId = data_get($resource, 'attributes.metadata.talent_vote_order_id');
        $reference = data_get($resource, 'attributes.reference_number');

        if ($sessionId) {
            $bySession = TalentVoteOrder::query()->where('paymongo_checkout_session_id', $sessionId)->first();
            if ($bySession) {
                return $bySession;
            }
        }

        if (is_numeric($orderId)) {
            return TalentVoteOrder::query()->find((int) $orderId);
        }

        if (is_string($reference) && preg_match('/^TVS-(\d+)$/', $reference, $matches)) {
            return TalentVoteOrder::query()->find((int) $matches[1]);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $resource
     */
    protected function orderFromPayment(array $resource): ?TalentVoteOrder
    {
        $paymentId = is_string($resource['id'] ?? null) ? $resource['id'] : null;
        $orderId = data_get($resource, 'attributes.metadata.talent_vote_order_id');

        if ($paymentId) {
            $byPayment = TalentVoteOrder::query()->where('paymongo_payment_id', $paymentId)->first();
            if ($byPayment) {
                return $byPayment;
            }
        }

        if (is_numeric($orderId)) {
            return TalentVoteOrder::query()->find((int) $orderId);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $resource
     */
    protected function sessionHasPaidPayment(array $resource, TalentVoteOrder $order): bool
    {
        $payments = data_get($resource, 'attributes.payments', []);
        if (! is_array($payments) || $payments === []) {
            $paymentIntentStatus = data_get($resource, 'attributes.payment_intent.attributes.status');

            return in_array($paymentIntentStatus, ['succeeded', 'paid'], true);
        }

        $expected = $this->amountInCentavos((float) $order->amount);

        foreach ($payments as $payment) {
            $status = data_get($payment, 'attributes.status') ?? data_get($payment, 'status');
            $amount = (int) (data_get($payment, 'attributes.amount') ?? data_get($payment, 'amount') ?? 0);

            if (in_array($status, ['paid', 'succeeded'], true) && ($amount === 0 || $amount === $expected)) {
                return true;
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

        return is_string($resource['id'] ?? null) && ($resource['type'] ?? '') === 'payment'
            ? $resource['id']
            : null;
    }

    /**
     * @param  array<string, mixed>  $event
     */
    protected function eventType(array $event): string
    {
        return (string) (data_get($event, 'data.attributes.type') ?? data_get($event, 'type') ?? '');
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<string, mixed>
     */
    protected function eventResource(array $event): array
    {
        $resource = data_get($event, 'data.attributes.data');

        return is_array($resource) ? $resource : [];
    }

    protected function amountInCentavos(float $amount): int
    {
        return (int) round($amount * 100);
    }
}

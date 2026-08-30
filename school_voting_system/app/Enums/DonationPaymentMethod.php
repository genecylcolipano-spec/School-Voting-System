<?php

namespace App\Enums;

use App\Models\Fundraiser;

enum DonationPaymentMethod: string
{
    case Gcash = 'gcash';
    case Maya = 'maya';
    case Qrph = 'qrph';
    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';

    public function label(): string
    {
        return match ($this) {
            self::Gcash => 'GCash',
            self::Maya => 'Maya',
            self::Qrph => 'QR Ph',
            self::Cash => 'Cash',
            self::BankTransfer => 'Bank transfer',
        };
    }

    public function isOnline(): bool
    {
        return in_array($this, [self::Gcash, self::Maya, self::Qrph], true);
    }

    /**
     * Methods students can choose for a new donation.
     * GCash, Maya, and bank transfer stay on historical records only.
     *
     * @return list<self>
     */
    public static function offeredForCheckout(): array
    {
        return [self::Qrph, self::Cash];
    }

    public function isOfferedForCheckout(): bool
    {
        return in_array($this, self::offeredForCheckout(), true);
    }

    public function donateSubmitLabel(): string
    {
        return $this->isOnline() ? 'Continue to payment' : 'Submit donation';
    }

    /**
     * PayMongo payment_method_types value, or null for offline methods.
     */
    public function paymongoType(): ?string
    {
        return match ($this) {
            self::Gcash => 'gcash',
            self::Maya => 'paymaya',
            self::Qrph => 'qrph',
            default => null,
        };
    }

    public function isAcceptedBy(Fundraiser $fundraiser): bool
    {
        return match ($this) {
            self::Gcash => $fundraiser->accept_gcash !== false,
            self::Maya => $fundraiser->accept_maya !== false,
            self::Qrph => $fundraiser->accept_qrph !== false,
            self::Cash => $fundraiser->accept_cash !== false,
            self::BankTransfer => $fundraiser->accept_bank_transfer !== false,
        };
    }
}

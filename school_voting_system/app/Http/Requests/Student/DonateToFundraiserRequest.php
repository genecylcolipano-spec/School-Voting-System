<?php

namespace App\Http\Requests\Student;

use App\Enums\DonationPaymentMethod;
use App\Models\Fundraiser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DonateToFundraiserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user
            && $user->is_active
            && $user->canDonate();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $fundraiser = $this->fundraiser();
        $min = $fundraiser->minimumDonationAmount();
        $max = $fundraiser->maximumDonationAmount();
        $accepted = array_map(
            fn (DonationPaymentMethod $method) => $method->value,
            $fundraiser->acceptedPaymentMethods(),
        );

        $amountRules = ['required', 'numeric', 'min:'.$min];
        if ($max !== null) {
            $amountRules[] = 'max:'.$max;
        }

        return [
            'amount' => $amountRules,
            'message' => ['nullable', 'string', 'max:255'],
            'is_anonymous' => ['nullable', 'boolean'],
            'payment_method' => ['required', Rule::in($accepted)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'amount' => 'donation amount',
            'payment_method' => 'payment method',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $min = $this->fundraiser()->minimumDonationAmount();
        $max = $this->fundraiser()->maximumDonationAmount();

        $messages = [
            'amount.min' => 'The donation amount must be at least ₱'.number_format($min, 2).'.',
            'payment_method.in' => 'Please choose a payment method this campaign accepts.',
        ];

        if ($max !== null) {
            $messages['amount.max'] = 'The donation amount may not be greater than ₱'.number_format($max, 2).'.';
        }

        return $messages;
    }

    public function paymentMethod(): DonationPaymentMethod
    {
        return DonationPaymentMethod::from($this->validated('payment_method'));
    }

    public function donationAmount(): float
    {
        return round((float) $this->validated('amount'), 2);
    }

    public function donationMessage(): ?string
    {
        $message = $this->validated('message');

        return filled($message) ? (string) $message : null;
    }

    public function isAnonymous(): bool
    {
        return (bool) ($this->validated('is_anonymous') ?? false);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_anonymous' => $this->boolean('is_anonymous'),
        ]);
    }

    protected function fundraiser(): Fundraiser
    {
        /** @var Fundraiser $fundraiser */
        $fundraiser = $this->route('fundraiser');

        return $fundraiser;
    }
}

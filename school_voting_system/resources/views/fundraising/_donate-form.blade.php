@php
    $donateAction = $donateAction ?? route('student.fundraising.donate', $fundraiser);
    $accent = $accent ?? 'cyan';
    $focusBorder = $accent === 'teal' ? 'focus:border-teal-500 focus:ring-teal-500/30' : 'focus:border-cyan-500 focus:ring-cyan-500/30';
    $radioAccent = $accent === 'teal' ? 'text-teal-500 focus:ring-teal-500/30' : 'text-cyan-500 focus:ring-cyan-500/30';
    $selectedBorder = $accent === 'teal' ? 'border-teal-500/50 bg-teal-500/5' : 'border-cyan-500/50 bg-cyan-500/5';
    $buttonGradient = $accent === 'teal' ? 'from-teal-500 to-emerald-400' : 'from-cyan-500 to-sky-400';
@endphp

@if ($paymentMethods === [])
    <p class="mt-3 text-sm text-slate-400">This campaign has no payment methods enabled.</p>
@elseif ($hasOnlineMethod && ! $paymongoConfigured)
    <p class="mt-3 text-sm text-amber-200">Online payments are not configured yet. Cash can still be submitted for confirmation if this campaign accepts it.</p>
@endif
<form
    method="POST"
    action="{{ $donateAction }}"
    class="mt-4 space-y-4"
    x-data="{
        method: @js(old('payment_method', $defaultPaymentMethod)),
        labels: @js($submitLabels),
        get submitLabel() {
            return this.labels[this.method] ?? 'Submit donation';
        }
    }"
>
    @csrf

    <div>
        <label class="block text-sm font-medium text-slate-300">Amount (PHP)</label>
        <input
            name="amount"
            type="number"
            step="0.01"
            min="{{ $minDonation }}"
            @if ($maxDonation) max="{{ $maxDonation }}" @endif
            required
            value="{{ old('amount', $minDonation) }}"
            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100 placeholder:text-slate-500 {{ $focusBorder }} focus:outline-none focus:ring-2"
        />
        <p class="mt-1 text-xs text-slate-400">
            Minimum ₱{{ number_format($minDonation, 2) }}
            @if ($maxDonation)
                · Maximum ₱{{ number_format($maxDonation, 2) }}
            @endif
        </p>
        @error('amount')
            <p class="mt-1 text-sm text-rose-300">{{ $message }}</p>
        @enderror
    </div>

    <fieldset>
        <legend class="block text-sm font-medium text-slate-300">Payment method</legend>
        <div class="mt-2 grid gap-2 sm:grid-cols-2">
            @foreach ($paymentMethods as $method)
                @php
                    $disabled = $method->isOnline() && ! $paymongoConfigured;
                    $selected = old('payment_method', $defaultPaymentMethod) === $method->value;
                @endphp
                <label
                    class="flex items-center gap-2 rounded-xl border px-3 py-2.5 text-sm transition {{ $disabled ? 'cursor-not-allowed border-slate-800 bg-slate-950/40 text-slate-500' : ($selected ? $selectedBorder.' text-white' : 'border-slate-800 bg-slate-950/40 text-slate-300') }}"
                    @unless ($disabled)
                        :class="method === '{{ $method->value }}'
                            ? '{{ $selectedBorder }} text-white'
                            : 'border-slate-800 bg-slate-950/40 text-slate-300'"
                    @endunless
                >
                    <input
                        type="radio"
                        name="payment_method"
                        value="{{ $method->value }}"
                        x-model="method"
                        @checked($selected)
                        @disabled($disabled)
                        required
                        class="border-slate-700 bg-slate-950/50 {{ $radioAccent }}"
                    />
                    <span>
                        {{ $method->label() }}
                        @if ($method->isOnline())
                            <span class="text-xs text-slate-400">via PayMongo</span>
                        @else
                            <span class="text-xs text-slate-400">pending confirmation</span>
                        @endif
                    </span>
                </label>
            @endforeach
        </div>
        @error('payment_method')
            <p class="mt-1 text-sm text-rose-300">{{ $message }}</p>
        @enderror
        <p class="mt-2 text-xs text-slate-400">QR Ph opens a PayMongo checkout page. Cash stays on this page until an organizer confirms. Donations are counted only after payment succeeds or is confirmed.</p>
    </fieldset>

    <div>
        <label class="block text-sm font-medium text-slate-300">Message (optional)</label>
        <input
            name="message"
            type="text"
            maxlength="255"
            value="{{ old('message') }}"
            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100 placeholder:text-slate-500 {{ $focusBorder }} focus:outline-none focus:ring-2"
        />
        @error('message')
            <p class="mt-1 text-sm text-rose-300">{{ $message }}</p>
        @enderror
    </div>

    @if ($fundraiser->allow_anonymous !== false)
        <label class="flex items-center gap-2 text-sm text-slate-300">
            <input type="checkbox" name="is_anonymous" value="1" @checked(old('is_anonymous')) class="rounded border-slate-700 bg-slate-950/50 {{ $radioAccent }}" />
            Donate anonymously
        </label>
    @endif

    <button type="submit" class="inline-flex rounded-xl bg-gradient-to-r {{ $buttonGradient }} px-5 py-2.5 text-sm font-semibold text-slate-950" x-text="submitLabel">
        {{ $submitLabel }}
    </button>
</form>

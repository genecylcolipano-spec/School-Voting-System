<x-app-layout>
    @php
        $minDonation = $fundraiser->minimumDonationAmount();
        $maxDonation = $fundraiser->maximumDonationAmount();
        $accepting = $fundraiser->isAcceptingDonations();
        $paymentMethods = $paymentMethods ?? $fundraiser->acceptedPaymentMethods();
        $paymongoConfigured = $paymongoConfigured ?? filled(config('services.paymongo.secret_key'));
        $hasOnlineMethod = collect($paymentMethods)->contains(fn ($method) => $method->isOnline());
        $defaultPaymentMethod = collect($paymentMethods)
            ->first(fn ($method) => ! ($method->isOnline() && ! $paymongoConfigured))
            ?->value;
        $selectedPaymentMethod = \App\Enums\DonationPaymentMethod::tryFrom((string) old('payment_method', $defaultPaymentMethod));
        $submitLabel = $selectedPaymentMethod?->donateSubmitLabel() ?? 'Submit donation';
        $submitLabels = collect($paymentMethods)
            ->mapWithKeys(fn ($method) => [$method->value => $method->donateSubmitLabel()])
            ->all();
    @endphp
    <x-faculty-portal :title="$fundraiser->title" :user="$user" :notifications-count="$notificationsCount">
        <div class="mb-2 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('faculty.fundraising.index') }}" class="text-sm font-semibold text-teal-300 hover:text-teal-200">← Back to fundraising</a>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                {{ session('error') }}
            </div>
        @endif

        <article class="overflow-hidden rounded-2xl border border-teal-500/15 bg-slate-900/70">
            @if ($fundraiser->hasUploadedBanner())
                <x-event-image
                    :src="$fundraiser->bannerUrl()"
                    :src-medium="$fundraiser->bannerMediumUrl()"
                    :src-mobile="$fundraiser->bannerMobileUrl()"
                    :orientation="$fundraiser->bannerOrientation()"
                    :contain="$fundraiser->bannerNeedsContainLayout()"
                    :alt="$fundraiser->title"
                    class="rounded-none"
                />
            @endif
            <div class="p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0 flex-1">
                        <h1 class="break-words text-2xl font-bold text-white">{{ $fundraiser->title }}</h1>
                        @if ($fundraiser->category)
                            <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-teal-300">{{ $fundraiser->category->label() }}</p>
                        @endif
                        @if ($fundraiser->description)
                            <p class="mt-2 whitespace-pre-line text-justify text-sm leading-relaxed text-slate-300">{{ $fundraiser->description }}</p>
                        @endif
                        @if ($fundraiser->beneficiary || $fundraiser->purpose)
                            <dl class="mt-3 space-y-1 text-sm text-slate-400">
                                @if ($fundraiser->beneficiary)
                                    <div><span class="text-slate-400">Beneficiary:</span> {{ $fundraiser->beneficiary }}</div>
                                @endif
                                @if ($fundraiser->purpose)
                                    <div><span class="text-slate-400">Purpose:</span> {{ $fundraiser->purpose }}</div>
                                @endif
                            </dl>
                        @endif
                    </div>
                    <div class="shrink-0 sm:text-right">
                        <span class="inline-flex rounded-full bg-slate-800 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-300">
                            {{ $fundraiser->displayStatusLabel() }}
                        </span>
                        <p class="mt-2 text-lg font-semibold tabular-nums text-white">₱{{ number_format((float) $fundraiser->amount_raised, 2) }}</p>
                        <p class="text-xs text-slate-400">Raised</p>
                        <p class="mt-1 text-sm text-slate-300">Goal ₱{{ number_format((float) $fundraiser->goal_amount, 2) }}</p>
                    </div>
                </div>

                <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-800">
                    <div class="h-full rounded-full bg-gradient-to-r from-teal-500 to-emerald-400" style="width: {{ $fundraiser->progressPercent() }}%"></div>
                </div>
                <p class="mt-2 text-xs text-slate-400">{{ number_format($fundraiser->progressPercent(), 1) }}% of goal · Remaining ₱{{ number_format($fundraiser->remainingAmount(), 2) }}</p>
            </div>
        </article>

        <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-6">
            <h2 class="text-lg font-semibold text-white">Make a donation</h2>
            @if ($accepting)
                @include('fundraising._donate-form', [
                    'donateAction' => route('faculty.fundraising.donate', $fundraiser),
                    'accent' => 'teal',
                ])
            @else
                <p class="mt-3 text-sm text-slate-400">This campaign is not currently accepting donations.</p>
            @endif
        </section>
    </x-faculty-portal>
</x-app-layout>

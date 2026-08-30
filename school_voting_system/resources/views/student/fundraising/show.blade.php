<x-app-layout>
    @php
        $preview = $preview ?? false;
        $minDonation = $fundraiser->minimumDonationAmount();
        $maxDonation = $fundraiser->maximumDonationAmount();
        $accepting = ! $preview && $fundraiser->isAcceptingDonations();
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
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between gap-4">
                @if ($preview)
                    <a href="{{ route('admin.fundraisers.edit', $fundraiser) }}" class="text-sm font-semibold text-cyan-300 hover:text-cyan-200">← Back to campaign editor</a>
                    <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-200">Admin preview</span>
                @else
                    <a href="{{ route('student.fundraising.index') }}" class="text-sm font-semibold text-cyan-300 hover:text-cyan-200">← Back to fundraising</a>
                    <a href="{{ route('student.dashboard') }}" class="text-sm text-slate-300 hover:text-white">Dashboard</a>
                @endif
            </div>

            @if ($preview)
                <div class="mb-4 rounded-xl border border-amber-500/20 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                    This is a read-only preview of the student donation page. Donations cannot be submitted from preview mode.
                </div>
            @endif

            @if (session('success'))
                <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                    {{ session('error') }}
                </div>
            @endif

            <article class="overflow-hidden rounded-2xl border border-cyan-500/15 bg-slate-900/70">
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
                            <h1 class="text-2xl font-bold text-white break-words">{{ $fundraiser->title }}</h1>
                            @if ($fundraiser->category)
                                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-cyan-300">{{ $fundraiser->category->label() }}</p>
                            @endif
                            @if ($fundraiser->description)
                                <p class="mt-2 text-sm leading-relaxed text-slate-300 text-justify whitespace-pre-line">{{ $fundraiser->description }}</p>
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
                        <div class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-sky-400" style="width: {{ $fundraiser->progressPercent() }}%"></div>
                    </div>
                    <p class="mt-2 text-xs text-slate-400">{{ number_format($fundraiser->progressPercent(), 1) }}% of goal · Remaining ₱{{ number_format($fundraiser->remainingAmount(), 2) }}</p>
                </div>
            </article>

            <section class="mt-6 rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-6">
                <h2 class="text-lg font-semibold text-white">Make a donation</h2>
                @if ($preview)
                    <p class="mt-3 text-sm text-slate-400">
                        @if ($fundraiser->isAcceptingDonations())
                            Students and faculty can donate here when this campaign is published and visible.
                        @else
                            This campaign is not currently accepting donations.
                        @endif
                    </p>
                @elseif ($accepting)
                    @include('fundraising._donate-form', [
                        'donateAction' => route('student.fundraising.donate', $fundraiser),
                        'accent' => 'cyan',
                    ])
                @else
                    <p class="mt-3 text-sm text-slate-400">This campaign is not currently accepting donations.</p>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>

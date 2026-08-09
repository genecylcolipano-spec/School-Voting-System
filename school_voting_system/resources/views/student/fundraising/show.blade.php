<x-app-layout>
    @php
        $preview = $preview ?? false;
        $minDonation = $fundraiser->minimumDonationAmount();
        $maxDonation = $fundraiser->maximumDonationAmount();
        $accepting = ! $preview && $fundraiser->isAcceptingDonations();
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
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h1 class="truncate text-2xl font-bold text-white">{{ $fundraiser->title }}</h1>
                            @if ($fundraiser->category)
                                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-cyan-300">{{ $fundraiser->category->label() }}</p>
                            @endif
                            @if ($fundraiser->description)
                                <p class="mt-2 text-sm text-slate-300">{{ $fundraiser->description }}</p>
                            @endif
                            @if ($fundraiser->beneficiary || $fundraiser->purpose)
                                <dl class="mt-3 space-y-1 text-sm text-slate-400">
                                    @if ($fundraiser->beneficiary)
                                        <div><span class="text-slate-500">Beneficiary:</span> {{ $fundraiser->beneficiary }}</div>
                                    @endif
                                    @if ($fundraiser->purpose)
                                        <div><span class="text-slate-500">Purpose:</span> {{ $fundraiser->purpose }}</div>
                                    @endif
                                </dl>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="text-xs uppercase tracking-wide text-slate-500">{{ $fundraiser->displayStatusLabel() }}</p>
                            <p class="mt-1 text-xs text-slate-400">
                                Raised ₱{{ number_format((float) $fundraiser->amount_raised, 2) }}
                            </p>
                            <p class="text-xs text-slate-400">
                                Goal ₱{{ number_format((float) $fundraiser->goal_amount, 2) }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-800">
                        <div class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-sky-400" style="width: {{ $fundraiser->progressPercent() }}%"></div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">{{ number_format($fundraiser->progressPercent(), 1) }}% of goal · Remaining ₱{{ number_format($fundraiser->remainingAmount(), 2) }}</p>
                </div>
            </article>

            <section class="mt-6 rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-6">
                <h2 class="text-lg font-semibold text-white">Make a donation</h2>
                @if ($preview)
                    <p class="mt-3 text-sm text-slate-400">
                        @if ($fundraiser->isAcceptingDonations())
                            Students can donate here when this campaign is published and visible.
                        @else
                            This campaign is not currently accepting donations.
                        @endif
                    </p>
                @elseif ($accepting)
                    <form method="POST" action="{{ route('student.fundraising.donate', $fundraiser) }}" class="mt-4 space-y-4">
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
                                value="{{ old('amount') }}"
                                class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/30"
                            />
                            <p class="mt-1 text-xs text-slate-500">
                                Minimum ₱{{ number_format($minDonation, 2) }}
                                @if ($maxDonation)
                                    · Maximum ₱{{ number_format($maxDonation, 2) }}
                                @endif
                            </p>
                            @error('amount')
                                <p class="mt-1 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300">Message (optional)</label>
                            <input
                                name="message"
                                type="text"
                                maxlength="255"
                                value="{{ old('message') }}"
                                class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/30"
                            />
                            @error('message')
                                <p class="mt-1 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        @if ($fundraiser->allow_anonymous !== false)
                            <label class="flex items-center gap-2 text-sm text-slate-300">
                                <input type="checkbox" name="is_anonymous" value="1" class="rounded border-slate-700 bg-slate-950/50 text-cyan-500 focus:ring-cyan-500/30" />
                                Donate anonymously
                            </label>
                        @endif

                        <button type="submit" class="inline-flex rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-5 py-2.5 text-sm font-semibold text-slate-950">
                            Donate
                        </button>
                    </form>
                @else
                    <p class="mt-3 text-sm text-slate-400">This campaign is not currently accepting donations.</p>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>

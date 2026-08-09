<x-app-layout>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-white">Fundraising</h1>
                    <p class="mt-1 text-sm text-slate-400">Support school initiatives and campaigns.</p>
                </div>
                <a href="{{ route('student.dashboard') }}" class="rounded-xl border border-cyan-500/25 bg-slate-900 px-4 py-2 text-sm font-semibold text-cyan-300 hover:bg-slate-800">
                    Back to dashboard
                </a>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                @forelse ($fundraisers as $fundraiser)
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
                        <div class="p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <h2 class="truncate text-lg font-semibold text-white">{{ $fundraiser->title }}</h2>
                                    @if ($fundraiser->description)
                                        <p class="mt-2 text-sm text-slate-300 line-clamp-2">{{ $fundraiser->description }}</p>
                                    @endif
                                </div>
                                <span class="shrink-0 rounded-full bg-slate-800 px-2 py-0.5 text-[10px] font-semibold uppercase text-slate-300">
                                    {{ $fundraiser->displayStatusLabel() }}
                                </span>
                            </div>

                            <div class="mt-4 text-xs text-slate-400">
                                Raised ₱{{ number_format((float) $fundraiser->amount_raised, 2) }} · Goal ₱{{ number_format((float) $fundraiser->goal_amount, 2) }}
                            </div>
                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-800">
                                <div class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-sky-400" style="width: {{ $fundraiser->progressPercent() }}%"></div>
                            </div>

                            <a href="{{ route('student.fundraising.show', $fundraiser) }}" class="mt-4 inline-block rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-4 py-2 text-sm font-semibold text-slate-950">
                                {{ $fundraiser->isAcceptingDonations() ? 'Donate' : 'View Campaign' }}
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-slate-800 bg-slate-900/50 p-6 text-slate-300 md:col-span-2">
                        No fundraisers found.
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $fundraisers->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

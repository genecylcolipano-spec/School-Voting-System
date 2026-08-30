<x-app-layout>
    <x-faculty-portal title="Fundraising" :user="$user" :notifications-count="$notificationsCount">
        <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
            <h2 class="text-lg font-semibold text-white">Fundraising</h2>
            <p class="mt-1 text-sm text-slate-400">Support school campaigns. You can donate here; campaign management stays with administrators.</p>
        </section>

        <div class="grid gap-4 md:grid-cols-2">
            @forelse ($fundraisers as $fundraiser)
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
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <h2 class="truncate text-lg font-semibold text-white">{{ $fundraiser->title }}</h2>
                                @if ($fundraiser->description)
                                    <p class="mt-2 line-clamp-2 text-sm text-slate-300">{{ $fundraiser->description }}</p>
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
                            <div class="h-full rounded-full bg-gradient-to-r from-teal-500 to-emerald-400" style="width: {{ $fundraiser->progressPercent() }}%"></div>
                        </div>

                        <a href="{{ route('faculty.fundraising.show', $fundraiser) }}" class="mt-4 inline-flex min-h-10 items-center justify-center rounded-xl bg-gradient-to-r from-teal-500 to-emerald-400 px-4 py-2 text-sm font-semibold text-slate-950">
                            {{ $fundraiser->isAcceptingDonations() ? 'Donate' : 'View Campaign' }}
                        </a>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-700 px-4 py-8 text-center text-sm text-slate-500 md:col-span-2">
                    No fundraisers found.
                </div>
            @endforelse
        </div>

        <div class="overflow-x-auto">{{ $fundraisers->links() }}</div>
    </x-faculty-portal>
</x-app-layout>

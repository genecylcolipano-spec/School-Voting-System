@if ($canManageFundraisers)
    <section id="fundraisers" class="scroll-mt-28 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
        <x-admin-section-header
            title="Fundraisers"
            description="School fundraising campaigns — visible to students when active."
            :badge="$statistics['active_fundraisers'] > 0 ? $statistics['active_fundraisers'].' active' : null"
            badge-tone="emerald"
        >
            <x-slot:actions>
                <a href="{{ route('admin.fundraisers.index') }}" class="rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-200 hover:bg-violet-500/10">View all</a>
                @if ($canCreateFundraiser)
                    <a href="{{ route('admin.fundraisers.create') }}" class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-violet-500">Create Fundraiser</a>
                @endif
            </x-slot:actions>
        </x-admin-section-header>

        @if ($fundraisers->isEmpty())
            <p class="mt-4 rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-6 text-center text-sm text-slate-400">
                No fundraisers yet.
                @if ($canCreateFundraiser)
                    <a href="{{ route('admin.fundraisers.create') }}" class="ml-1 text-violet-300 hover:text-violet-200">Create one</a>
                @endif
            </p>
        @else
            <div id="dashboard-fundraisers-grid" class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($fundraisers as $fundraiser)
                    <article data-fundraiser-id="{{ $fundraiser->id }}" class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                        <div class="flex items-start justify-between gap-2">
                            <h4 class="font-semibold text-white">{{ $fundraiser->title }}</h4>
                            <x-admin-status-badge
                                :status="$fundraiser->resolvedStatus()->value"
                                :label="$fundraiser->displayStatusLabel()"
                                data-live-fundraiser-status
                            />
                        </div>

                        @if ($fundraiser->description)
                            <p class="mt-2 line-clamp-2 text-sm text-slate-400">{{ $fundraiser->description }}</p>
                        @endif

                        <div class="mt-3 flex justify-between text-xs text-slate-400">
                            <span data-live-fundraiser-raised>Raised ₱{{ number_format((float) $fundraiser->amount_raised, 2) }}</span>
                            <span>Goal ₱{{ number_format((float) $fundraiser->goal_amount, 2) }}</span>
                        </div>

                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-800">
                            <div data-live-fundraiser-progress class="h-full rounded-full bg-gradient-to-r from-violet-600 to-indigo-400" style="width: {{ $fundraiser->progressPercent() }}%"></div>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
                            <span data-live-fundraiser-donations>{{ $fundraiser->donations_count }} donation(s)</span>
                            @if ($fundraiser->ends_on)
                                <span>Ends {{ $fundraiser->ends_on->format('M d, Y') }}</span>
                            @endif
                        </div>

                        @can('update', $fundraiser)
                            <a href="{{ route('admin.fundraisers.edit', $fundraiser) }}" class="mt-3 inline-block text-xs font-semibold text-violet-300 hover:text-violet-200">Edit fundraiser →</a>
                        @endcan
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endif

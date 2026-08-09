@php
    $fundraiser = $fundraiser ?? null;
    $isEdit = $fundraiser !== null;
    $donationStats = $donationStats ?? [
        'total' => 0,
        'successful' => 0,
        'pending' => 0,
        'cancelled' => 0,
        'average' => 0,
        'largest' => 0,
    ];
    $resolved = $isEdit ? $fundraiser->resolvedStatus() : null;
    $progress = $isEdit ? $fundraiser->progressPercent() : 0;
    $remaining = $isEdit ? $fundraiser->remainingAmount() : 0;
    $daysLeft = $isEdit ? $fundraiser->daysRemaining() : null;
    $donorCount = $isEdit ? $fundraiser->donorCount() : 0;
    $bannerSrc = $isEdit && $fundraiser->hasUploadedBanner()
        ? $fundraiser->bannerUrl()
        : \App\Support\EventImageUrl::placeholder();
@endphp
<x-app-layout>
    <x-admin-portal :title="$isEdit ? 'Edit Fundraising Campaign' : 'Create Fundraising Campaign'" :user="$user" :notifications-count="$notificationsCount">
        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
        @endif

        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-white">{{ $isEdit ? 'Edit Campaign' : 'Create Campaign' }}</h1>
                <p class="mt-1 text-sm text-slate-400">Organize campaign details, goals, donation settings, and visibility.</p>
            </div>
            <a href="{{ route('admin.fundraisers.index') }}" class="text-sm font-semibold text-violet-300 hover:text-violet-200">← Back to campaigns</a>
        </div>

        @if ($isEdit)
            {{-- Campaign Overview Dashboard --}}
            <section class="mb-4 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:p-5" aria-label="Campaign overview">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-violet-200">Campaign Overview</h2>
                    <span class="rounded-full border border-violet-500/30 bg-violet-500/10 px-3 py-1 text-xs font-semibold text-violet-100">
                        {{ $resolved?->label() ?? 'Draft' }}
                    </span>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-6">
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Goal</p>
                        <p class="mt-1 text-sm font-bold text-white">₱{{ number_format((float) $fundraiser->goal_amount, 2) }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Raised</p>
                        <p class="mt-1 text-sm font-bold text-emerald-300">₱{{ number_format((float) $fundraiser->amount_raised, 2) }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Remaining</p>
                        <p class="mt-1 text-sm font-bold text-amber-200">₱{{ number_format($remaining, 2) }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Progress</p>
                        <p class="mt-1 text-sm font-bold text-white">{{ number_format($progress, 1) }}%</p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Donors</p>
                        <p class="mt-1 text-sm font-bold text-white">{{ number_format($donorCount) }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Days Left</p>
                        <p class="mt-1 text-sm font-bold text-white">{{ $daysLeft === null ? '—' : number_format($daysLeft) }}</p>
                    </div>
                </div>
                <div class="mt-4 h-2.5 overflow-hidden rounded-full bg-slate-800">
                    <div class="h-full rounded-full bg-gradient-to-r from-violet-500 to-cyan-400 transition-all" style="width: {{ $progress }}%"></div>
                </div>
            </section>
        @endif

        <form
            method="POST"
            action="{{ $isEdit ? route('admin.fundraisers.update', $fundraiser) : route('admin.fundraisers.store') }}"
            enctype="multipart/form-data"
            class="space-y-4"
        >
            @csrf
            @if ($isEdit) @method('PUT') @endif

            {{-- Campaign Information --}}
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:p-5">
                <h2 class="text-sm font-bold uppercase tracking-wide text-violet-200">Campaign Information</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-300">Campaign Title</label>
                        <input type="text" name="title" required value="{{ old('title', $fundraiser?->title) }}"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/30">
                        @error('title')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-300">Description</label>
                        <textarea name="description" rows="5" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">{{ old('description', $fundraiser?->description) }}</textarea>
                        @error('description')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Campaign Category</label>
                        <select name="category" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                            <option value="">Select category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->value }}" @selected(old('category', $fundraiser?->category?->value) === $category->value)>{{ $category->label() }}</option>
                            @endforeach
                        </select>
                        @error('category')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
            <div>
                        <label class="block text-sm font-medium text-slate-300">Base Status</label>
                        <select name="status" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                    @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" @selected(old('status', $fundraiser?->status?->value ?? 'draft') === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-500">Lifecycle display auto-updates to Scheduled / Active / Goal Reached / Completed from dates and donations. Use Cancelled or Archived to override.</p>
                        @error('status')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            {{-- Beneficiary --}}
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:p-5">
                <h2 class="text-sm font-bold uppercase tracking-wide text-violet-200">Beneficiary Information</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-300">Beneficiary</label>
                        <input type="text" name="beneficiary" value="{{ old('beneficiary', $fundraiser?->beneficiary) }}" placeholder="e.g. College Library"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                        @error('beneficiary')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Purpose</label>
                        <input type="text" name="purpose" value="{{ old('purpose', $fundraiser?->purpose) }}" placeholder="e.g. Purchase updated reference books"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                        @error('purpose')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Expected Beneficiaries</label>
                        <input type="text" name="expected_beneficiaries" value="{{ old('expected_beneficiaries', $fundraiser?->expected_beneficiaries) }}" placeholder="e.g. 450 Students"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                        @error('expected_beneficiaries')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            {{-- Fundraising Goal --}}
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:p-5">
                <h2 class="text-sm font-bold uppercase tracking-wide text-violet-200">Fundraising Goal</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Goal Amount (₱)</label>
                        <input type="number" name="goal_amount" required min="1" step="0.01" value="{{ old('goal_amount', $fundraiser?->goal_amount) }}"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                        @error('goal_amount')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500">Current Raised</label>
                        <input type="text" disabled value="₱{{ number_format((float) ($fundraiser?->amount_raised ?? 0), 2) }}"
                            class="mt-1 w-full rounded-xl border border-slate-800 bg-slate-950/30 px-4 py-2 text-slate-400">
                        <p class="mt-1 text-[11px] text-slate-600">From donation transactions only</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500">Remaining</label>
                        <input type="text" disabled value="₱{{ number_format($isEdit ? $remaining : 0, 2) }}"
                            class="mt-1 w-full rounded-xl border border-slate-800 bg-slate-950/30 px-4 py-2 text-slate-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500">Progress</label>
                        <input type="text" disabled value="{{ number_format($progress, 1) }}%"
                            class="mt-1 w-full rounded-xl border border-slate-800 bg-slate-950/30 px-4 py-2 text-slate-400">
                    </div>
                </div>
            </section>

            {{-- Schedule --}}
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:p-5">
                <h2 class="text-sm font-bold uppercase tracking-wide text-violet-200">Campaign Schedule</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Start Date</label>
                        <input type="date" name="starts_on" value="{{ old('starts_on', optional($fundraiser?->starts_on)->format('Y-m-d')) }}"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                        @error('starts_on')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">End Date</label>
                        <input type="date" name="ends_on" value="{{ old('ends_on', optional($fundraiser?->ends_on)->format('Y-m-d')) }}"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                        @error('ends_on')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500">Days Remaining</label>
                        <input type="text" disabled value="{{ $daysLeft === null ? '—' : $daysLeft.' day(s)' }}"
                            class="mt-1 w-full rounded-xl border border-slate-800 bg-slate-950/30 px-4 py-2 text-slate-400">
                    </div>
                </div>
            </section>

            {{-- Donation Settings --}}
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:p-5">
                <h2 class="text-sm font-bold uppercase tracking-wide text-violet-200">Donation Settings</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Minimum Donation (₱)</label>
                        <input type="number" name="min_donation" min="1" step="0.01" value="{{ old('min_donation', $fundraiser?->min_donation) }}" placeholder="Default 1.00"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                        @error('min_donation')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Maximum Donation (₱)</label>
                        <input type="number" name="max_donation" min="1" step="0.01" value="{{ old('max_donation', $fundraiser?->max_donation) }}" placeholder="Optional"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                        @error('max_donation')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ([
                        ['allow_anonymous', 'Allow Anonymous Donations', $fundraiser?->allow_anonymous ?? true],
                        ['generate_receipt', 'Generate Donation Receipt', $fundraiser?->generate_receipt ?? true],
                        ['accept_cash', 'Accept Cash', $fundraiser?->accept_cash ?? true],
                        ['accept_gcash', 'Accept GCash', $fundraiser?->accept_gcash ?? true],
                        ['accept_maya', 'Accept Maya', $fundraiser?->accept_maya ?? true],
                        ['accept_bank_transfer', 'Accept Bank Transfer', $fundraiser?->accept_bank_transfer ?? true],
                    ] as [$name, $label, $default])
                        <label class="inline-flex items-center gap-2 rounded-xl border border-slate-800 bg-slate-950/40 px-3 py-2 text-sm text-slate-300">
                            <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $default))
                                class="rounded border-slate-600 bg-slate-900 text-violet-500 focus:ring-violet-500/40">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </section>

            {{-- Banner --}}
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:p-5">
                <h2 class="text-sm font-bold uppercase tracking-wide text-violet-200">Campaign Banner</h2>
                <div class="mt-4">
                    <x-event-image-field
                        :src="$bannerSrc"
                        :has-uploaded="$isEdit && $fundraiser->hasUploadedBanner()"
                        :contain="$isEdit && $fundraiser->bannerNeedsContainLayout()"
                        :orientation="$isEdit ? $fundraiser->bannerOrientation() : null"
                        :warn-portrait="$isEdit && $fundraiser->bannerNeedsContainLayout()"
                        label="Campaign Banner"
                        input-id="event-image-input"
                        preview-id="event-image-preview"
                    >
                        <input id="event-image-input" type="file" name="banner" accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100 file:mr-4 file:rounded-lg file:border-0 file:bg-violet-500/20 file:px-3 file:py-1.5 file:text-sm file:text-violet-200">
                        <div class="mt-2 rounded-xl border border-slate-800 bg-slate-950/40 px-3 py-2 text-xs text-slate-400">
                            <p class="font-semibold text-slate-300">Upload guidelines</p>
                            <ul class="mt-1 list-inside list-disc space-y-0.5">
                                <li>Recommended: <span class="text-slate-200">1600 × 900 px</span> · 16:9 landscape</li>
                                <li>Formats: JPG / PNG · Maximum 2 MB</li>
                                <li>Portrait uploads stay fully visible over a blurred backdrop</li>
                            </ul>
                        </div>
                        @error('banner')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
                    </x-event-image-field>
                </div>
            </section>

            {{-- Visibility --}}
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:p-5">
                <h2 class="text-sm font-bold uppercase tracking-wide text-violet-200">Campaign Visibility</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Visibility</label>
                        <select name="visibility" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                            @foreach ($visibilities as $visibility)
                                <option value="{{ $visibility->value }}" @selected(old('visibility', $fundraiser?->visibility?->value ?? 'public') === $visibility->value)>{{ $visibility->label() }}</option>
                    @endforeach
                </select>
                        @error('visibility')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
                    <label class="mt-6 inline-flex items-center gap-2 text-sm text-slate-300">
                        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $fundraiser?->is_featured ?? false))
                            class="rounded border-slate-600 bg-slate-900 text-violet-500 focus:ring-violet-500/40">
                        Featured Campaign
                    </label>
                    <label class="mt-6 inline-flex items-center gap-2 text-sm text-slate-300">
                        <input type="checkbox" name="accept_donations" value="1" @checked(old('accept_donations', $fundraiser?->accept_donations ?? true))
                            class="rounded border-slate-600 bg-slate-900 text-violet-500 focus:ring-violet-500/40">
                        Accept Donations
                    </label>
                </div>
            </section>

            @if ($isEdit)
                {{-- Donation Statistics --}}
                <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:p-5">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-violet-200">Donation Statistics</h2>
                    <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-6">
                        @foreach ([
                            ['Total Donations', number_format($donationStats['total'])],
                            ['Successful', number_format($donationStats['successful'])],
                            ['Pending', number_format($donationStats['pending'])],
                            ['Cancelled', number_format($donationStats['cancelled'])],
                            ['Average', '₱'.number_format($donationStats['average'], 2)],
                            ['Largest', '₱'.number_format($donationStats['largest'], 2)],
                        ] as [$label, $value])
                            <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-3 py-2.5">
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</p>
                                <p class="mt-1 truncate text-sm font-bold text-white">{{ $value }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- Audit --}}
                <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:p-5">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-violet-200">Audit Information</h2>
                    <dl class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 text-sm">
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Created By</dt>
                            <dd class="mt-0.5 text-slate-200">{{ $fundraiser->creator?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Created Date</dt>
                            <dd class="mt-0.5 text-slate-200">{{ optional($fundraiser->created_at)->format('M d, Y g:i A') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Last Updated By</dt>
                            <dd class="mt-0.5 text-slate-200">{{ $fundraiser->updater?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Last Updated</dt>
                            <dd class="mt-0.5 text-slate-200">{{ optional($fundraiser->updated_at)->format('M d, Y g:i A') ?? '—' }}</dd>
            </div>
                    </dl>
                </section>
            @endif

            {{-- Buttons --}}
            <div class="flex flex-wrap gap-3 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <a href="{{ route('admin.fundraisers.index') }}" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm font-semibold text-slate-300 hover:bg-slate-800">Cancel</a>
                @if ($isEdit)
                    <a href="{{ route('admin.fundraisers.preview', $fundraiser) }}" target="_blank" rel="noopener noreferrer"
                        class="rounded-xl border border-cyan-500/30 px-5 py-2.5 text-sm font-semibold text-cyan-200 hover:bg-cyan-500/10">
                        Preview Campaign
                    </a>
                @endif
                <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90">
                    {{ $isEdit ? 'Save Changes' : 'Create Campaign' }}
                </button>
            </div>
        </form>
    </x-admin-portal>

    @vite('resources/js/event-image-preview.js')
</x-app-layout>

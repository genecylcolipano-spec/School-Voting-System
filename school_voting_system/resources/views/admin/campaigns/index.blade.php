<x-app-layout>
    <x-admin-portal title="Campaigns & Partylists" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'Campaigns & Partylists',
            'description' => 'Create reusable campaigns and attach them to elections during setup.',
            'action' => route('admin.campaigns.create'),
            'actionLabel' => 'Add campaign',
            'showAction' => $canManage,
        ])

        <div class="grid gap-4 lg:grid-cols-2">
            @forelse ($partylists as $partylist)
                <article class="overflow-hidden rounded-2xl border border-violet-500/15 bg-slate-900/70">
                    @if ($partylist->bannerUrl())
                        <div @class([
                            'relative overflow-hidden bg-slate-950',
                            'aspect-[16/5] w-full' => ! $partylist->isPortraitBanner(),
                            'aspect-[3/4] max-h-40 w-full' => $partylist->isPortraitBanner(),
                        ])>
                            @include('student.campaigns._banner-media', [
                                'url' => $partylist->bannerUrl(),
                                'alt' => $partylist->name.' banner',
                                'portrait' => $partylist->isPortraitBanner(),
                            ])
                        </div>
                    @else
                        <div class="aspect-[16/5] w-full bg-gradient-to-br from-slate-900 via-slate-950 to-violet-950/40"></div>
                    @endif
                    <div class="p-5">
                        @php
                            $uploadedPosters = $partylist->posters->filter->hasUploadedFile();
                            $brokenPosters = $partylist->posters->reject->hasUploadedFile();
                            $motto = trim((string) $partylist->motto);
                            $platform = trim((string) $partylist->platform);
                            $showPlatform = $platform !== '' && strcasecmp($platform, $motto) !== 0;
                            $electionTitles = $partylist->elections->pluck('title')->filter()->values();
                        @endphp
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                @if ($partylist->logo_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($partylist->logo_path) }}" alt="{{ $partylist->name }} logo" class="h-12 w-12 rounded-lg border border-slate-700 object-cover">
                                @endif
                                <div>
                                    <h3 class="text-lg font-semibold text-white">{{ $partylist->name }}</h3>
                                    @if ($partylist->acronym)
                                        <p class="text-sm text-violet-300">{{ $partylist->acronym }}</p>
                                    @endif
                                    @if ($motto !== '')
                                        <p class="mt-1 text-xs italic text-slate-500">"{{ $motto }}"</p>
                                    @endif
                                </div>
                            </div>
                            <x-admin-status-badge :status="$partylist->status->value" />
                        </div>

                        @if ($showPlatform)
                            <p class="mt-3 text-sm text-slate-400">{{ \Illuminate\Support\Str::limit($platform, 160) }}</p>
                        @endif

                        <div class="mt-3 flex flex-wrap gap-3 text-xs text-slate-500">
                            <span>{{ $partylist->elections_count }} election(s)</span>
                            <span>{{ $partylist->candidates_count }} candidate(s)</span>
                            <span>{{ $uploadedPosters->count() }} poster(s)</span>
                            @if ($partylist->leader)
                                <span>Leader: {{ $partylist->leader }}</span>
                            @endif
                        </div>
                        @if ($electionTitles->isNotEmpty())
                            <p class="mt-1 text-xs text-slate-500">
                                Attached to:
                                {{ $electionTitles->take(2)->implode(', ') }}{{ $electionTitles->count() > 2 ? ' +'.($electionTitles->count() - 2).' more' : '' }}
                            </p>
                        @endif

                        <div class="mt-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Posters</p>

                            @if ($uploadedPosters->isNotEmpty())
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($uploadedPosters as $poster)
                                        <div class="group relative overflow-hidden rounded-lg border border-slate-800">
                                            <a
                                                href="{{ $poster->file_url }}"
                                                target="_blank"
                                                rel="noopener"
                                                title="{{ ucfirst($poster->status) }}"
                                            >
                                                <img src="{{ $poster->file_url }}" alt="{{ $partylist->name }} poster" class="h-24 w-auto max-w-[6rem] rounded object-contain transition group-hover:opacity-90">
                                                <span class="absolute bottom-1 right-1 rounded bg-black/70 px-1.5 py-0.5 text-[10px] text-white">{{ $poster->status }}</span>
                                            </a>
                                            @can('update', $partylist)
                                                <form method="POST" action="{{ route('admin.campaigns.poster.destroy', [$partylist, $poster]) }}" class="absolute right-1 top-1" onsubmit="return confirm('Remove this poster?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded bg-black/70 px-1.5 py-0.5 text-[10px] text-rose-200 hover:text-white">Remove</button>
                                                </form>
                                            @endcan
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-2 text-sm text-slate-500">No poster uploaded yet.</p>
                            @endif

                            @if ($brokenPosters->isNotEmpty())
                                <div class="mt-2 space-y-2">
                                    @foreach ($brokenPosters as $poster)
                                        <div class="flex items-center justify-between gap-3 rounded-lg border border-amber-500/20 bg-amber-500/5 px-3 py-2 text-xs text-amber-100">
                                            <span>A poster record is missing its image file.</span>
                                            @can('update', $partylist)
                                                <form method="POST" action="{{ route('admin.campaigns.poster.destroy', [$partylist, $poster]) }}" onsubmit="return confirm('Remove this missing poster record?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="font-semibold text-amber-50 underline decoration-amber-300/60 underline-offset-2 hover:text-white">Remove</button>
                                                </form>
                                            @endcan
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @can('update', $partylist)
                                @php
                                    $posterUploadFailed = (int) session('poster_upload_failed_partylist_id') === (int) $partylist->id;
                                @endphp
                                @if ($partylist->elections_count > 0)
                                    <form
                                        method="POST"
                                        action="{{ route('admin.campaigns.poster.store', $partylist) }}"
                                        enctype="multipart/form-data"
                                        class="mt-3 flex flex-wrap items-center gap-2"
                                    >
                                        @csrf
                                        <label class="flex-1 min-w-[12rem]">
                                            <span class="sr-only">Poster image for {{ $partylist->name }}</span>
                                            <input
                                                type="file"
                                                name="poster_image"
                                                accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                                                required
                                                aria-invalid="{{ $posterUploadFailed ? 'true' : 'false' }}"
                                                @class([
                                                    'w-full rounded-xl border bg-slate-950/50 px-3 py-2 text-xs text-slate-100 file:mr-2 file:rounded-lg file:border-0 file:bg-violet-500/20 file:px-2 file:py-1 file:text-xs file:text-violet-300',
                                                    'border-rose-500/60' => $posterUploadFailed,
                                                    'border-slate-700' => ! $posterUploadFailed,
                                                ])
                                            >
                                        </label>
                                        <button type="submit" class="rounded-lg bg-violet-600 px-3 py-2 text-xs font-semibold text-white hover:bg-violet-500">
                                            Upload poster
                                        </button>
                                    </form>
                                    <p class="mt-1 text-[10px] text-slate-500">Portrait or landscape JPG/PNG, max 2MB.</p>
                                    @if ($posterUploadFailed && session('error'))
                                        <p class="mt-1 text-xs text-rose-300" role="alert">{{ session('error') }}</p>
                                    @endif
                                @else
                                    <p class="mt-2 text-sm text-slate-500">Attach this campaign to an election first, then you can upload a poster.</p>
                                    @if ($posterUploadFailed && session('error'))
                                        <p class="mt-1 text-xs text-rose-300" role="alert">{{ session('error') }}</p>
                                    @endif
                                @endif
                            @endcan
                        </div>

                        <div class="mt-4 flex flex-wrap gap-3 text-sm">
                            @can('update', $partylist)
                                <a href="{{ route('admin.campaigns.edit', $partylist) }}" class="text-violet-300 hover:text-violet-200">Edit details</a>
                            @endcan
                            @can('delete', $partylist)
                                <form method="POST" action="{{ route('admin.campaigns.destroy', $partylist) }}" class="inline" onsubmit="return confirm('Delete this campaign?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-rose-300 hover:text-rose-200">Delete</button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </article>
            @empty
                <div class="lg:col-span-2 rounded-2xl border border-slate-800 bg-slate-900/70 px-6 py-8 text-center text-slate-400">
                    No campaigns yet.
                    @if ($canManage)
                        <a href="{{ route('admin.campaigns.create') }}" class="ml-1 text-violet-300 hover:text-violet-200">Add your first campaign</a>
                    @endif
                </div>
            @endforelse
        </div>

        <p class="mt-6 text-xs text-slate-500">Active campaigns can be attached to elections during election setup and appear on the student portal automatically.</p>
    </x-admin-portal>
</x-app-layout>

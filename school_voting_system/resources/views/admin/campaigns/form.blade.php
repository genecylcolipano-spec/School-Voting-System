@php
    $partylist = $partylist ?? null;
    $isEdit = $partylist !== null;
    $formAction = $isEdit ? route('admin.campaigns.update', $partylist) : route('admin.campaigns.store');
    $currentStatus = old('status', $isEdit ? ($partylist->status?->value ?? 'draft') : 'draft');
    $currentColor = old('color', optional($partylist)->color ?? '#7c3aed');
    $logoUrl = optional($partylist)->logo_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($partylist->logo_path) : null;
    $bannerUrl = optional($partylist)->banner_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($partylist->banner_path) : null;
@endphp

<x-app-layout>
    <x-admin-portal :title="$isEdit ? 'Edit Campaign' : 'Create Campaign'" :user="$user" :notifications-count="$notificationsCount">
        <div class="mb-4 rounded-xl border border-violet-500/15 bg-violet-950/20 px-4 py-3 text-sm text-violet-100">
            Campaigns are reusable. Create them once, then attach them to elections during election setup.
        </div>

        <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="max-w-2xl space-y-4 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-6">
            @csrf
            @if ($isEdit) @method('PUT') @endif

            @include('admin.partials.form-input', ['label' => 'Party name', 'name' => 'name', 'value' => optional($partylist)->name, 'required' => true])
            @include('admin.partials.form-input', ['label' => 'Acronym', 'name' => 'acronym', 'value' => optional($partylist)->acronym, 'maxlength' => 50])
            <p class="-mt-2 text-xs text-slate-500">Short party code (e.g. PDP). Max 50 characters — use Party name for the full title.</p>

            <div>
                <label class="block text-sm font-medium text-slate-300">Party color</label>
                <div class="mt-1 flex items-center gap-3">
                    <input type="color" name="color" value="{{ $currentColor }}" class="h-10 w-16 cursor-pointer rounded-lg border border-slate-700 bg-slate-950/50">
                    <span class="text-xs text-slate-500">Used as an accent on campaign cards and results.</span>
                </div>
                @error('color')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>

            @include('admin.partials.form-input', ['label' => 'Party leader (optional)', 'name' => 'leader', 'value' => optional($partylist)->leader])

            @include('admin.partials.form-input', ['label' => 'Motto', 'name' => 'motto', 'value' => optional($partylist)->motto])

            <div>
                <label class="block text-sm font-medium text-slate-300">Platform</label>
                <textarea name="platform" rows="4" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">{{ old('platform', optional($partylist)->platform) }}</textarea>
                @error('platform')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300">Description</label>
                <textarea name="description" rows="3" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">{{ old('description', optional($partylist)->description) }}</textarea>
                @error('description')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div x-data="campaignLogoPreview(@js($logoUrl))">
                <label class="block text-sm font-medium text-slate-300">Campaign logo</label>
                <div class="mt-2 h-24 w-24 overflow-hidden rounded-xl border border-slate-700 bg-slate-950">
                    <template x-if="preview">
                        <img :src="preview" alt="Logo preview" class="h-full w-full object-cover object-center">
                    </template>
                    <template x-if="!preview">
                        <div class="flex h-full w-full items-center justify-center text-[10px] text-slate-600">1:1</div>
                    </template>
                </div>
                <input type="file" name="logo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    class="mt-2 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100 file:mr-4 file:rounded-lg file:border-0 file:bg-violet-500/20 file:px-3 file:py-1.5 file:text-sm file:text-violet-300"
                    @change="onFile($event)">
                <p class="mt-1 text-xs text-slate-500">
                    Recommended: <span class="text-slate-300">512 × 512 px</span> · Square (1:1) · JPG, PNG or WEBP · Max 2MB
                </p>
                @error('logo')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div x-data="campaignBannerPreview(@js($bannerUrl), @js(optional($partylist)->bannerNeedsContainLayout() ?? false))">
                <label class="block text-sm font-medium text-slate-300">Campaign banner (optional)</label>
                <p class="mt-1 text-xs text-slate-500">Used on campaign pages. Dedicated campaign posters can also be uploaded in <strong>Official Posters</strong>.</p>
                <div class="relative mt-2 aspect-video max-w-xl overflow-hidden rounded-xl border border-slate-700 bg-slate-950">
                    <template x-if="preview">
                        <div class="absolute inset-0">
                            <img x-show="contain" :src="preview" alt="" aria-hidden="true" class="absolute inset-0 h-full w-full scale-110 object-cover object-center blur-2xl brightness-[0.4] saturate-125">
                            <img :src="preview" alt="Banner preview" class="absolute inset-0 z-[1] h-full w-full object-center" :class="contain ? 'object-contain' : 'object-cover'">
                        </div>
                    </template>
                    <template x-if="!preview">
                        <div class="flex h-full w-full items-center justify-center text-xs text-slate-600">16:9 banner preview</div>
                    </template>
                </div>
                <input type="file" name="banner" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    class="mt-2 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100 file:mr-4 file:rounded-lg file:border-0 file:bg-violet-500/20 file:px-3 file:py-1.5 file:text-sm file:text-violet-300"
                    @change="onFile($event)">
                <p class="mt-1 text-xs text-slate-500">
                    Recommended: <span class="text-slate-300">1600 × 900 px</span> · Landscape (16:9) · JPG, PNG or WEBP · Max 2MB
                </p>
                <p class="mt-0.5 text-[11px] text-slate-600" x-text="hint"></p>
                @error('banner')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300">Status</label>
                <select name="status" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                    @foreach (\App\Enums\CampaignStatus::options() as $option)
                        <option value="{{ $option['value'] }}" @selected($currentStatus === $option['value'])>{{ $option['label'] }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">Only <strong>Active</strong> campaigns can be selected during election creation.</p>
                @error('status')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90">Save</button>
                <a href="{{ route('admin.campaigns.index') }}" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm text-slate-300 hover:bg-slate-800">Cancel</a>
            </div>
        </form>
    </x-admin-portal>

    @push('scripts')
        <script>
            function campaignLogoPreview(initial) {
                return {
                    preview: initial || null,
                    onFile(event) {
                        const file = event.target.files?.[0];
                        if (!file) return;
                        this.preview = URL.createObjectURL(file);
                    },
                };
            }

            function campaignBannerPreview(initial, initialContain) {
                return {
                    preview: initial || null,
                    contain: !!initialContain,
                    hint: 'Preview matches live campaign cards: landscape fills 16:9; portrait/square stay fully visible over a blurred backdrop.',
                    onFile(event) {
                        const file = event.target.files?.[0];
                        if (!file) return;
                        const url = URL.createObjectURL(file);
                        const img = new Image();
                        img.onload = () => {
                            this.contain = img.naturalWidth > 0 && img.naturalHeight > 0 && img.naturalWidth <= img.naturalHeight;
                            const orientation = this.contain
                                ? (img.naturalWidth === img.naturalHeight ? 'square' : 'portrait')
                                : 'landscape';
                            this.hint = `Selected ${img.naturalWidth}×${img.naturalHeight}px (${orientation}). Save to upload.`;
                            this.preview = url;
                        };
                        img.src = url;
                    },
                };
            }
        </script>
    @endpush
</x-app-layout>

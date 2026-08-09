@props([
    'inputName',
    'removeName' => null,
    'photoUrl' => null,
])

{{-- Inline circular photo uploader for a candidate row (create + edit). --}}
<div class="candidate-photo-field md:col-span-2 flex items-center gap-4 rounded-xl border border-slate-800/70 bg-slate-950/30 p-3">
    <div class="relative h-16 w-16 shrink-0 overflow-hidden rounded-full border border-slate-700 bg-slate-800">
        <img data-photo-preview src="{{ $photoUrl }}" alt="" class="h-full w-full object-cover {{ $photoUrl ? '' : 'hidden' }}">
        <div data-photo-placeholder class="flex h-full w-full items-center justify-center text-slate-500 {{ $photoUrl ? 'hidden' : '' }}">
            <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5z" /></svg>
        </div>
    </div>
    <div class="min-w-0">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Profile Photo</p>
        <div class="mt-1 flex flex-wrap items-center gap-3">
            <button type="button" data-photo-change class="rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-200 transition hover:bg-violet-500/10">Upload Photo</button>
            <button type="button" data-photo-remove class="text-xs font-semibold text-rose-300 transition hover:text-rose-200 {{ $photoUrl ? '' : 'hidden' }}">Remove</button>
        </div>
        <input type="file" data-photo-input name="{{ $inputName }}" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="hidden">
        @if ($removeName)
            <input type="hidden" data-photo-removed name="{{ $removeName }}" value="0">
        @endif
        <p data-photo-error class="mt-1 hidden text-xs text-rose-300"></p>
        <p class="mt-1 text-[11px] text-slate-500">Recommended: 600 × 600 px · Square (1:1) · JPG, JPEG, PNG or WebP · Max 2MB</p>
    </div>
</div>

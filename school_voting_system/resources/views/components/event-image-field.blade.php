@props([
    'src',
    'alt' => 'Image preview',
    'label' => 'Image',
    'inputId' => 'event-image-input',
    'previewId' => 'event-image-preview',
    'captionId' => 'event-image-caption',
    'hasUploaded' => false,
    'contain' => false,
    'orientation' => null,
    'warnPortrait' => false,
])

@php
    $placeholder = \App\Support\EventImageUrl::placeholder();
    $useContain = $contain || in_array($orientation, ['portrait', 'square'], true);
@endphp

<div>
    <label class="block text-sm font-medium text-slate-300">{{ $label }}</label>
    <div
        id="{{ $previewId }}-frame"
        class="relative mt-2 aspect-video w-full max-w-xl overflow-hidden rounded-xl border border-slate-700 bg-slate-950"
        data-contain="{{ $useContain ? '1' : '0' }}"
    >
        <img
            id="{{ $previewId }}-blur"
            src="{{ $src }}"
            alt=""
            aria-hidden="true"
            class="absolute inset-0 h-full w-full scale-110 object-cover object-center blur-2xl brightness-[0.4] saturate-125 {{ $useContain ? '' : 'hidden' }}"
        >
        <div class="absolute inset-0 z-[1] bg-gradient-to-t from-slate-950/50 via-transparent to-transparent"></div>
        <img
            id="{{ $previewId }}"
            src="{{ $src }}"
            alt="{{ $alt }}"
            data-placeholder="{{ $placeholder }}"
            class="absolute inset-0 z-[1] h-full w-full {{ $useContain ? 'object-contain' : 'object-cover' }} object-center"
            onerror="this.onerror=null;this.src=this.dataset.placeholder;"
        >
    </div>
    <p id="{{ $captionId }}" class="mt-1 text-xs text-slate-500">
        @if ($hasUploaded)
            Current uploaded banner. Choose a new file to replace it.
        @else
            Default placeholder shown. Upload a landscape banner (1600 × 900) for best results.
        @endif
    </p>
    <p
        id="{{ $previewId }}-orientation-warning"
        class="mt-2 rounded-xl border border-amber-500/25 bg-amber-500/10 px-3 py-2 text-xs font-medium text-amber-100 {{ $warnPortrait || $useContain ? '' : 'hidden' }}"
        data-default-hidden="{{ $warnPortrait || $useContain ? '0' : '1' }}"
    >
        This image is portrait. For best appearance, upload a landscape banner (1600 × 900).
    </p>
    <p class="mt-0.5 text-[11px] text-slate-600">
        Live preview: landscape fills 16:9 with cover; portrait/square stay fully visible over a blurred backdrop (never stretched).
    </p>
    {{ $slot }}
</div>

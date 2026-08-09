@props([
    'src',
    'alt' => '',
    /** When true, render only the fill layer (for absolute hero overlays). */
    'bare' => false,
    /** Force contain+blur layout. Auto-detected when orientation is provided. */
    'contain' => null,
    'orientation' => null,
    'srcMedium' => null,
    'srcMobile' => null,
])

@php
    $placeholder = \App\Support\EventImageUrl::placeholder();
    $useContain = $contain;
    if ($useContain === null && $orientation) {
        $useContain = in_array($orientation, ['portrait', 'square'], true);
    }
    $useContain = (bool) $useContain;

    $desktopSrc = $src ?: $placeholder;
    $mediumSrc = $srcMedium ?: $desktopSrc;
    $mobileSrc = $srcMobile ?: $mediumSrc;
@endphp

@if ($bare)
    <div {{ $attributes->class(['absolute inset-0 overflow-hidden']) }}>
        @if ($useContain)
            <img
                src="{{ $desktopSrc }}"
                alt=""
                aria-hidden="true"
                class="absolute inset-0 h-full w-full scale-110 object-cover object-center blur-2xl brightness-[0.4] saturate-125"
                loading="lazy"
            >
            <picture class="absolute inset-0 z-[1]">
                <source media="(max-width: 640px)" srcset="{{ $mobileSrc }}">
                <source media="(max-width: 1024px)" srcset="{{ $mediumSrc }}">
                <img
                    src="{{ $desktopSrc }}"
                    alt="{{ $alt }}"
                    class="h-full w-full object-contain object-center"
                    loading="lazy"
                    onerror="this.onerror=null;this.src='{{ $placeholder }}';"
                >
            </picture>
        @else
            <picture class="absolute inset-0">
                <source media="(max-width: 640px)" srcset="{{ $mobileSrc }}">
                <source media="(max-width: 1024px)" srcset="{{ $mediumSrc }}">
                <img
                    src="{{ $desktopSrc }}"
                    alt="{{ $alt }}"
                    class="h-full w-full object-cover object-center"
                    loading="lazy"
                    onerror="this.onerror=null;this.src='{{ $placeholder }}';"
                >
            </picture>
        @endif
    </div>
@else
    <div {{ $attributes->class(['relative aspect-video w-full overflow-hidden bg-slate-950']) }}>
        @if ($useContain)
            <img
                src="{{ $desktopSrc }}"
                alt=""
                aria-hidden="true"
                class="absolute inset-0 h-full w-full scale-110 object-cover object-center blur-2xl brightness-[0.4] saturate-125"
                loading="lazy"
            >
            <picture class="absolute inset-0 z-[1]">
                <source media="(max-width: 640px)" srcset="{{ $mobileSrc }}">
                <source media="(max-width: 1024px)" srcset="{{ $mediumSrc }}">
                <img
                    src="{{ $desktopSrc }}"
                    alt="{{ $alt }}"
                    class="h-full w-full object-contain object-center"
                    loading="lazy"
                    onerror="this.onerror=null;this.src='{{ $placeholder }}';"
                >
            </picture>
        @else
            <picture class="absolute inset-0">
                <source media="(max-width: 640px)" srcset="{{ $mobileSrc }}">
                <source media="(max-width: 1024px)" srcset="{{ $mediumSrc }}">
                <img
                    src="{{ $desktopSrc }}"
                    alt="{{ $alt }}"
                    class="h-full w-full object-cover object-center"
                    loading="lazy"
                    onerror="this.onerror=null;this.src='{{ $placeholder }}';"
                >
            </picture>
        @endif
    </div>
@endif

@props([
    'url',
    'alt',
    'portrait' => false,
    'contain' => null,
])

@php
    // Portrait/square: full image + blurred fill. Landscape: cover the 16:9 frame.
    $useContain = $contain ?? $portrait;
@endphp

<div {{ $attributes->class(['absolute inset-0']) }}>
    @if ($useContain)
        <img
            src="{{ $url }}"
            alt=""
            aria-hidden="true"
            loading="lazy"
            class="absolute inset-0 h-full w-full scale-110 object-cover object-center blur-2xl brightness-[0.35] saturate-125"
        >
        <img
            src="{{ $url }}"
            alt="{{ $alt }}"
            loading="lazy"
            class="absolute inset-0 z-[1] h-full w-full object-contain object-center"
        >
    @else
        <img
            src="{{ $url }}"
            alt="{{ $alt }}"
            loading="lazy"
            class="h-full w-full object-cover object-center"
        >
    @endif
</div>

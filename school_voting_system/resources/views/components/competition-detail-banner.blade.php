@props([
    'event',
    'bare' => false,
    'showWarning' => true,
])

@php
    $needsContain = $event->detailBannerNeedsContainLayout();
@endphp

<div {{ $attributes }}>
    <x-event-image
        :bare="$bare"
        :src="$event->detailBannerUrl()"
        :src-medium="$event->detailBannerMediumUrl()"
        :src-mobile="$event->detailBannerMobileUrl()"
        :orientation="$event->detailBannerOrientation()"
        :contain="$needsContain"
        :alt="$event->title"
    />
    @if ($showWarning && $event->shouldWarnNonLandscapeBanner())
        <p class="mt-2 rounded-xl border border-amber-500/25 bg-amber-500/10 px-3 py-2 text-center text-xs font-medium text-amber-100 sm:text-sm">
            This image is portrait. For best appearance, upload a landscape banner (1600 × 900).
        </p>
    @endif
</div>

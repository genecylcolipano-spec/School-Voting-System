@props([
    'event',
    'class' => 'rounded-xl',
    'bare' => false,
    /** Compact 16:9 banner for dashboard overview cards (capped height). */
    'compact' => false,
])

@if ($compact)
    {{-- Cap height so wide layouts stay overview-sized; object-cover fills the frame. --}}
    <div {{ $attributes->class([
        'competition-card-banner--compact relative w-full overflow-hidden rounded-xl bg-slate-950',
        'aspect-video h-[140px] max-h-[140px] sm:h-[180px] sm:max-h-[180px] lg:h-[220px] lg:max-h-[220px]',
        $class,
    ]) }}>
        <x-event-image
            bare
            :src="$event->cardBannerUrl()"
            :src-medium="$event->cardBannerMediumUrl()"
            :src-mobile="$event->cardBannerMobileUrl()"
            orientation="landscape"
            :contain="false"
            :alt="$event->title"
        />
    </div>
@else
    {{-- Full 16:9 banner for list pages and detail-adjacent cards. --}}
    <x-event-image
        :bare="$bare"
        :src="$event->cardBannerUrl()"
        :src-medium="$event->cardBannerMediumUrl()"
        :src-mobile="$event->cardBannerMobileUrl()"
        orientation="landscape"
        :contain="false"
        :alt="$event->title"
        {{ $attributes->class([$class, 'overflow-hidden']) }}
    />
@endif

@props([
    'subtitle' => null,
    'gradient' => 'from-violet-600 to-indigo-500',
    'iconClass' => 'text-white',
    'shadowClass' => 'shadow-violet-900/40',
    'collapsedAware' => false,
])

@php
    $systemName = \App\Support\SchoolBranding::systemName();
    $schoolName = \App\Support\SchoolBranding::schoolName();
    $poweredBy = \App\Support\SchoolBranding::poweredBy();
    // Custom upload only — otherwise use the purple book icon (not the Rosemont crest).
    $logoUrl = \App\Support\SchoolBranding::logoUrl(withFallback: false);
@endphp

<div {{ $attributes->class('flex shrink-0 items-center gap-3') }}>
    @if ($logoUrl)
        <img
            src="{{ $logoUrl }}"
            alt="{{ $schoolName }}"
            class="h-11 w-11 shrink-0 rounded-full border border-white/10 bg-white object-contain p-0.5 shadow-lg {{ $shadowClass }}"
            onerror="this.classList.add('hidden'); const fallback = this.nextElementSibling; if (fallback) { fallback.classList.remove('hidden'); fallback.classList.add('flex'); }"
        >
        <div class="hidden h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br {{ $gradient }} {{ $iconClass }} shadow-lg {{ $shadowClass }}" aria-hidden="true">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>
    @else
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br {{ $gradient }} {{ $iconClass }} shadow-lg {{ $shadowClass }}" aria-hidden="true">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>
    @endif

    <div @if ($collapsedAware) x-show="!collapsed" @endif class="min-w-0">
        <p class="truncate font-semibold text-white">{{ $systemName }}</p>
        @if (filled($subtitle))
            <p class="truncate text-xs text-slate-500">{{ $subtitle }}</p>
        @endif
        @if (filled($poweredBy))
            <p class="truncate text-[10px] text-slate-500">{{ $poweredBy }}</p>
        @endif
    </div>
</div>

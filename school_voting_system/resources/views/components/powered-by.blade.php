@php
    $schoolName = \App\Support\SchoolBranding::schoolName();
@endphp

@if (filled($schoolName))
    <p {{ $attributes->class('text-slate-500') }}>
        Powered by <span class="school-name">{{ $schoolName }}</span>
    </p>
@endif

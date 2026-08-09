@props([
    'path' => null,
    'name' => null,
    'size' => 'md',
])

@php
    $sizes = [
        'sm' => 'h-9 w-9 text-[11px]',
        'md' => 'h-12 w-12 text-sm',
        'lg' => 'h-16 w-16 text-lg',
        'xl' => 'h-32 w-32 text-3xl',
    ];
    $dimension = $sizes[$size] ?? $sizes['md'];

    $url = \App\Support\EventImageUrl::hasUploadedImage($path)
        ? \App\Support\EventImageUrl::resolve($path)
        : null;

    $initials = collect(preg_split('/\s+/', trim((string) $name)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
@endphp

<span {{ $attributes->merge(['class' => "relative inline-flex shrink-0 items-center justify-center overflow-hidden rounded-full border border-slate-700 bg-slate-800 {$dimension}"]) }}>
    @if ($url)
        <img src="{{ $url }}" alt="{{ $name ? $name.' photo' : 'Candidate photo' }}" class="h-full w-full object-cover" loading="lazy">
    @elseif ($initials !== '')
        <span class="font-semibold text-slate-300">{{ $initials }}</span>
    @else
        <svg class="h-1/2 w-1/2 text-slate-500" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 12a5 5 0 100-10 5 5 0 000 10zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5z" />
        </svg>
    @endif
</span>

@props([
    'user',
    'size' => 'md',
    'theme' => 'admin',
])

@php
    $sizes = [
        'sm' => 'h-8 w-8 text-[10px]',
        'md' => 'h-10 w-10 text-xs',
        'nav' => 'h-11 w-11 text-xs',
        'lg' => 'h-14 w-14 text-sm',
        'xl' => 'h-20 w-20 text-2xl',
    ];
    $dimension = $sizes[$size] ?? $sizes['md'];
    $url = $user->avatarUrl();
    $initials = $user->initials();
    $isStudent = $theme === 'student';
    $shell = $isStudent
        ? 'border-cyan-400/30 bg-gradient-to-br from-cyan-500 to-sky-400'
        : 'border-violet-400/30 bg-gradient-to-br from-violet-600 to-indigo-500';
    $initialsClass = $isStudent ? 'font-semibold text-slate-950' : 'font-semibold text-white';
@endphp

<span {{ $attributes->merge(['class' => "relative inline-flex shrink-0 items-center justify-center overflow-hidden rounded-full border {$shell} {$dimension}"]) }}>
    @if ($url)
        <img
            src="{{ $url }}"
            alt="{{ $user->name }} profile photo"
            class="absolute inset-0 h-full w-full object-cover"
            loading="lazy"
            onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
        >
        <span class="hidden {{ $initialsClass }}">{{ $initials }}</span>
    @else
        <span class="{{ $initialsClass }}">{{ $initials }}</span>
    @endif
</span>


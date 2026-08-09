@props([
    'href' => null,
    'variant' => 'secondary',
    'disabled' => false,
])

@php
    $isDisabled = $disabled || $variant === 'disabled' || blank($href);

    // Preserve existing button language; fixed box keeps Action column aligned.
    $shell = implode(' ', [
        'inline-flex',
        'h-9',
        'w-[112px]',
        'shrink-0',
        'items-center',
        'justify-center',
        'overflow-hidden',
        'whitespace-nowrap',
        'rounded-xl',
        'px-3',
        'text-xs',
        'font-semibold',
        'leading-none',
        'transition',
        'focus:outline-none',
        'focus-visible:ring-2',
        'focus-visible:ring-cyan-400/60',
        'focus-visible:ring-offset-2',
        'focus-visible:ring-offset-slate-950',
    ]);

    $tone = match (true) {
        $isDisabled => 'cursor-not-allowed border border-slate-700 text-slate-500',
        $variant === 'primary' => 'bg-gradient-to-r from-cyan-500 to-sky-400 text-slate-950 hover:from-cyan-400 hover:to-sky-300',
        default => 'border border-cyan-500/30 text-cyan-300 hover:bg-cyan-500/10',
    };
@endphp

@if ($isDisabled)
    <span
        {{ $attributes->merge([
            'class' => $shell.' '.$tone,
            'aria-disabled' => 'true',
            'role' => 'button',
            'tabindex' => '-1',
        ]) }}
    >
        <span class="truncate">{{ $slot }}</span>
    </span>
@else
    <a
        href="{{ $href }}"
        {{ $attributes->merge([
            'class' => $shell.' '.$tone,
        ]) }}
    >
        <span class="truncate">{{ $slot }}</span>
    </a>
@endif

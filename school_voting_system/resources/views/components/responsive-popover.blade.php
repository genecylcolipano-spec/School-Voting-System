@props([
    'align' => 'end',
    'panelClass' => '',
    'mobileTitle' => null,
    'widthClass' => 'w-72',
])

@php
    $sharedPanel = trim(
        'fixed z-[80] flex flex-col overflow-hidden border bg-slate-900 shadow-2xl shadow-black/50 '.$panelClass
    );
    $desktopExtra = trim($widthClass.' max-w-[calc(100vw-1rem)] rounded-2xl');
    $mobileExtra = 'inset-x-0 bottom-0 max-h-[min(85vh,36rem)] w-full rounded-t-2xl border-b-0';
@endphp

{{-- Use single-quoted x-data so @json string values do not break the HTML attribute. --}}
<div
    {{ $attributes->class(['relative inline-flex']) }}
    x-data='responsivePopover({ align: @json($align), desktopClass: @json($desktopExtra), mobileClass: @json($mobileExtra) })'
    @keydown.escape.window="open && close()"
>
    <div x-ref="trigger" class="inline-flex" @click.stop="toggle()">
        {{ $trigger }}
    </div>

    <template x-teleport="body">
        <div>
            <div
                x-show="open && isMobile"
                x-transition.opacity
                x-cloak
                class="fixed inset-0 z-[70] bg-black/60 sm:hidden"
                @click="close()"
                style="display: none;"
            ></div>

            <div
                x-ref="panel"
                x-show="open"
                x-cloak
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="{{ $sharedPanel }}"
                :class="isMobile ? mobileClass : desktopClass"
                :style="isMobile ? '' : panelStyle"
                role="menu"
                style="display: none;"
                @click="$event.target.closest('[data-popover-close]') && close()"
            >
                <div x-show="isMobile" x-cloak class="flex shrink-0 justify-center pt-3 sm:hidden" aria-hidden="true">
                    <span class="h-1 w-10 rounded-full bg-slate-600"></span>
                </div>

                @if ($mobileTitle)
                    <div x-show="isMobile" x-cloak class="shrink-0 px-4 pb-2 pt-2 sm:hidden">
                        <p class="text-sm font-semibold text-white">{{ $mobileTitle }}</p>
                    </div>
                @endif

                <div class="flex min-h-0 flex-1 flex-col overflow-hidden">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </template>
</div>


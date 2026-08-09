@props([
    'title',
    'description' => null,
    'borderClass' => 'border-cyan-500/15',
])

<section {{ $attributes->merge(['class' => "rounded-2xl border {$borderClass} bg-slate-900/70 p-5 sm:p-6"]) }}>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-white">{{ $title }}</h2>
            @if ($description)
                <p class="mt-1 text-sm text-slate-400">{{ $description }}</p>
            @endif
        </div>
        @isset($actions)
            <div class="shrink-0">{{ $actions }}</div>
        @endisset
    </div>
    <div class="mt-5">
        {{ $slot }}
    </div>
</section>

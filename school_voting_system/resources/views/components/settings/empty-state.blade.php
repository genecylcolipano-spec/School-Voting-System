@props([
    'title' => 'Nothing here yet',
    'description' => '',
    'actionLabel' => null,
    'actionHref' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-dashed border-slate-700 bg-slate-950/40 px-6 py-10 text-center']) }}>
    <p class="text-sm font-semibold text-white">{{ $title }}</p>
    @if ($description)
        <p class="mx-auto mt-2 max-w-md text-sm text-slate-400">{{ $description }}</p>
    @endif
    @if ($actionLabel && $actionHref)
        <a href="{{ $actionHref }}" class="mt-5 inline-flex rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:opacity-90">
            {{ $actionLabel }}
        </a>
    @endif
    {{ $slot }}
</div>

@props(['title', 'action' => null, 'actionLabel' => null, 'description' => null, 'showAction' => true])

<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <h2 class="text-xl font-bold text-white">{{ $title }}</h2>
        @if ($description)
            <p class="mt-1 text-sm text-slate-400">{{ $description }}</p>
        @endif
    </div>
    @if ($action && $actionLabel && $showAction)
        <a href="{{ $action }}" class="inline-flex min-h-10 w-full items-center justify-center rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white hover:opacity-90 sm:w-auto">
            {{ $actionLabel }}
        </a>
    @endif
</div>

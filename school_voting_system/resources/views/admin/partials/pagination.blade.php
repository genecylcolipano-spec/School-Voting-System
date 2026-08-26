@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-xs text-slate-500">
            @if ($paginator->firstItem())
                Showing
                <span class="font-medium text-slate-300">{{ $paginator->firstItem() }}</span>
                to
                <span class="font-medium text-slate-300">{{ $paginator->lastItem() }}</span>
                of
                <span class="font-medium text-slate-300">{{ $paginator->total() }}</span>
            @else
                {{ $paginator->count() }}
                {{ __('results') }}
            @endif
        </p>

        <div class="flex flex-wrap items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="inline-flex min-h-9 items-center rounded-lg border border-slate-700 px-3 py-1.5 text-xs text-slate-500">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex min-h-9 items-center rounded-lg border border-slate-700 px-3 py-1.5 text-xs font-semibold text-slate-200 hover:border-violet-500/40 hover:text-white">Previous</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex min-h-9 min-w-9 items-center justify-center px-2 text-xs text-slate-500">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-lg bg-violet-600 px-2 text-xs font-semibold text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-lg border border-slate-700 px-2 text-xs text-slate-300 hover:border-violet-500/40 hover:text-white">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex min-h-9 items-center rounded-lg border border-slate-700 px-3 py-1.5 text-xs font-semibold text-slate-200 hover:border-violet-500/40 hover:text-white">Next</a>
            @else
                <span class="inline-flex min-h-9 items-center rounded-lg border border-slate-700 px-3 py-1.5 text-xs text-slate-500">Next</span>
            @endif
        </div>
    </nav>
@endif

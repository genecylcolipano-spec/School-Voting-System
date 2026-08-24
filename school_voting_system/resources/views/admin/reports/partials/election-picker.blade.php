@php
    $pickerElections = $elections ?? collect();
    $pickerElection = $election ?? null;
    $pickerAction = $pickerAction ?? url()->current();
@endphp

@if ($pickerElections->isNotEmpty())
    <form method="GET" action="{{ $pickerAction }}" class="mb-5 flex flex-wrap items-end gap-3">
        <div class="min-w-[16rem] flex-1">
            <label for="report-election" class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Election</label>
            <select
                id="report-election"
                name="election"
                onchange="this.form.submit()"
                class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-sm text-slate-100"
            >
                @foreach ($pickerElections as $option)
                    <option value="{{ $option->id }}" @selected($pickerElection?->id === $option->id)>
                        {{ $option->title }} · {{ $option->status?->label() ?? '—' }}
                        @if ($option->public_results_published)
                            · Published
                        @endif
                    </option>
                @endforeach
            </select>
        </div>
        <noscript>
            <button type="submit" class="rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white">View</button>
        </noscript>
    </form>
@endif

@props([
    'passkey',
    'accent' => 'cyan',
])

@php
    $label = $passkey->display_name ?? $passkey->device_name ?? $passkey->name ?? 'Device';
    $renameClass = $accent === 'teal' ? 'text-teal-300 hover:text-teal-200' : 'text-cyan-300 hover:text-cyan-200';
@endphp

<li class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-4" data-device-id="{{ $passkey->id }}">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <p class="font-medium text-white">{{ $label }}</p>
                @if ($passkey->is_current ?? false)
                    <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-200">Current Device</span>
                @endif
            </div>
            <dl class="mt-3 grid gap-1 text-xs text-slate-500 sm:grid-cols-2">
                <div><dt class="inline text-slate-600">Type:</dt> <dd class="inline text-slate-300">{{ $passkey->device_type ?? '—' }}</dd></div>
                <div><dt class="inline text-slate-600">Browser:</dt> <dd class="inline text-slate-300">{{ $passkey->browser ?? '—' }}</dd></div>
                <div><dt class="inline text-slate-600">OS:</dt> <dd class="inline text-slate-300">{{ $passkey->operating_system ?? '—' }}</dd></div>
                <div><dt class="inline text-slate-600">Registered:</dt> <dd class="inline text-slate-300">{{ optional($passkey->created_at)->format('M d, Y') ?? '—' }}</dd></div>
                <div class="sm:col-span-2"><dt class="inline text-slate-600">Last used:</dt> <dd class="inline text-slate-300">{{ optional($passkey->last_used_at)->diffForHumans() ?? 'Never' }}</dd></div>
            </dl>
        </div>
        <div class="flex shrink-0 gap-3">
            <button type="button" data-rename="{{ $passkey->id }}" data-name="{{ $label }}" class="text-xs font-semibold {{ $renameClass }}">Rename</button>
            <button type="button" data-remove="{{ $passkey->id }}" class="text-xs font-semibold text-rose-300 hover:text-rose-200">Remove</button>
        </div>
    </div>
</li>

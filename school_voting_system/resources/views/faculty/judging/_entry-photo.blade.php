@php
    $photo = $entry->photoUrl() ?: $entry->thumbnailUrl();
    $initial = strtoupper(substr((string) $entry->display_name, 0, 1));
@endphp

@if ($photo)
    <img src="{{ $photo }}" alt="{{ $entry->display_name }}" class="h-full w-full object-cover object-center" loading="lazy">
@else
    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-teal-900/50 to-slate-900">
        <span class="font-bold text-teal-200/70 {{ $initialClass ?? 'text-2xl' }}">{{ $initial }}</span>
    </div>
@endif

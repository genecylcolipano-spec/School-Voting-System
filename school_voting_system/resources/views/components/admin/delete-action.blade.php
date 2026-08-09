@props([
    'action',
    'warning' => null,
    'buttonClass' => 'ml-3 text-rose-300 hover:text-rose-200',
    'label' => 'Delete',
])

@php
    $baseMessage = "Are you sure you want to delete this activity?\n\nThis action will remove the activity from the system.\n\nThis action cannot be undone.";

    if (filled($warning)) {
        $baseMessage = "Are you sure you want to delete this activity?\n\n{$warning}\n\nThis action will remove the activity from the system.\n\nThis action cannot be undone.";
    }
@endphp

<form
    method="POST"
    action="{{ $action }}"
    {{ $attributes->merge(['class' => 'inline']) }}
    data-confirm-sensitive
    data-confirm-title="Delete Activity?"
    data-confirm-message="{{ $baseMessage }}"
    data-confirm-ok-label="Delete Activity"
    data-confirm-danger="1"
>
    @csrf
    @method('DELETE')
    <button type="submit" class="{{ $buttonClass }}">{{ $label }}</button>
</form>

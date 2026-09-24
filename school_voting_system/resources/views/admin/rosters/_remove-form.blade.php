@php
    $hasPortal = (bool) ($portalAccountUrl ?? false);
    $isRegistered = (bool) $record->is_registered
        || (method_exists($record, 'isFullyRegistered') && $record->isFullyRegistered());

    $confirmMessage = $isRegistered
        ? 'This ID is marked registered but has no portal account. Remove it so they can be added and register again?'
        : 'Remove this roster record?';
@endphp
@unless ($hasPortal)
    <form method="POST" action="{{ route($routePrefix.'.destroy', $record) }}" onsubmit="return confirm(@js($confirmMessage))">
        @csrf
        @method('DELETE')
        @if ($isRegistered)
            <input type="hidden" name="confirm_linked" value="1">
        @endif
        <button type="submit" class="{{ $buttonClass ?? 'rounded-lg border border-rose-500/30 px-3 py-1.5 text-xs font-semibold text-rose-300 hover:bg-rose-500/10' }}">Remove</button>
    </form>
@endunless

@props(['passkey', 'currentPasskeyId' => 0, 'stacked' => false])

@php
    $owner = $passkey->user;
    $usable = $passkey->isUsable();
    $isCurrent = $usable && (int) $currentPasskeyId > 0 && (int) $passkey->id === (int) $currentPasskeyId;
    $accountLabel = $owner ? $owner->name.' ('.$owner->account_id.')' : 'this account';
    $revokeConfirm = $isCurrent
        ? 'This is the passkey you are signed in with. Disable it? This session stays open until you sign out, but this device cannot log in again. This does not send a new enrollment link.'
        : 'Revoke this passkey for '.$accountLabel.'? They will not be able to sign in with this device. This does not send a new enrollment link.';
    $lostConfirm = $isCurrent
        ? 'This is the passkey you are signed in with. Mark it lost? This session stays open until you sign out, but this device cannot log in again. This does not send a new enrollment link.'
        : 'Mark this passkey as lost for '.$accountLabel.'? Same effect as revoke — the device cannot sign in. This does not send a new enrollment link.';
    $revokeClass = $stacked
        ? 'rounded-lg border border-rose-500/30 px-3 py-1.5 text-xs font-semibold text-rose-300 hover:bg-rose-500/10'
        : 'text-xs font-semibold text-rose-300 hover:text-rose-200';
    $lostClass = $stacked
        ? 'rounded-lg border border-amber-500/30 px-3 py-1.5 text-xs font-semibold text-amber-300 hover:bg-amber-500/10'
        : 'text-xs font-semibold text-amber-300 hover:text-amber-200';
@endphp

@if ($usable)
    <div class="{{ $stacked ? 'flex flex-wrap gap-2' : 'flex flex-col items-start gap-1' }}">
        <form method="POST" action="{{ route('super-admin.passkeys.action', $passkey) }}" onsubmit="return confirm(@js($revokeConfirm));">
            @csrf
            <input type="hidden" name="action" value="revoke">
            <button type="submit" class="{{ $revokeClass }}">Revoke</button>
        </form>
        <form method="POST" action="{{ route('super-admin.passkeys.action', $passkey) }}" onsubmit="return confirm(@js($lostConfirm));">
            @csrf
            <input type="hidden" name="action" value="lost">
            <button type="submit" class="{{ $lostClass }}">Mark lost</button>
        </form>
    </div>
@else
    <span class="text-xs text-slate-500">Disabled</span>
@endif

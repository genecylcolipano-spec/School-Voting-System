@php
    $match = $recoveryRequest->queue_match ?? 'unknown';
    $queueUser = $recoveryRequest->queue_user;
    $lastSentAt = $recoveryRequest->last_sent_at;
    $cooldownRemaining = $lastSentAt ? max(0, 120 - $lastSentAt->diffInSeconds(now())) : 0;
    $cooldownActive = $cooldownRemaining > 0;
@endphp

@if ($queueUser)
    <a href="{{ $recoveryRequest->queue_user_url }}" class="font-medium text-white hover:text-violet-200">{{ $queueUser->name }}</a>
    @if ($match === 'exact')
        <p class="mt-0.5 text-xs text-emerald-300">Exact match</p>
    @else
        <p class="mt-0.5 text-xs text-amber-300">Account found · email does not match</p>
        <p class="mt-0.5 break-all text-xs text-slate-400">On file: {{ $recoveryRequest->queue_on_file_email }}</p>
    @endif
@else
    <span class="text-slate-300">No such account</span>
@endif

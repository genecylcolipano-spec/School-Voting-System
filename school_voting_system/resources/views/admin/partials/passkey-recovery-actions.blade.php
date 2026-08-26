@php
    $lastSentAt = $recoveryRequest->last_sent_at;
    $cooldownRemaining = $lastSentAt ? max(0, 120 - $lastSentAt->diffInSeconds(now())) : 0;
    $cooldownActive = $cooldownRemaining > 0;
    $stacked = $stacked ?? false;
@endphp

<div class="{{ $stacked ? 'flex flex-col gap-2' : 'flex flex-wrap items-center gap-2' }}">
    @if ($recoveryRequest->queue_can_issue)
        <button
            type="button"
            class="{{ $stacked ? 'w-full' : '' }} rounded-lg bg-gradient-to-r from-cyan-500 to-sky-400 px-3 py-2 text-xs font-semibold text-slate-950 hover:opacity-90 disabled:opacity-50"
            data-enroll-url="{{ $recoveryRequest->queue_enroll_url }}"
            data-recovery-request-id="{{ $recoveryRequest->id }}"
            data-confirm="{{ $recoveryRequest->queue_confirm }}"
            @disabled($cooldownActive)
        >
            Generate enrollment link
        </button>
        @if ($cooldownActive)
            <p class="text-xs text-amber-300">Cooldown: retry in {{ $cooldownRemaining }}s</p>
        @endif
    @endif

    @if ($recoveryRequest->queue_can_dismiss)
        <button
            type="button"
            class="{{ $stacked ? 'w-full' : '' }} rounded-lg border border-slate-700 px-3 py-2 text-xs font-semibold text-slate-300 hover:border-rose-500/40 hover:text-rose-200"
            data-dismiss-url="{{ $recoveryRequest->queue_dismiss_url }}"
            data-recovery-request-id="{{ $recoveryRequest->id }}"
        >
            Dismiss
        </button>
    @elseif (! $recoveryRequest->queue_can_issue)
        <span class="text-xs text-amber-300">Needs manual verification</span>
    @endif
</div>

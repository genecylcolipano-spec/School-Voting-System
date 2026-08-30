@props(['account'])

<div class="mt-3 space-y-1.5 text-sm">
    <p class="text-slate-400">
        <span class="text-slate-500">Email</span>
        @if (filled($account->email))
            <a href="mailto:{{ $account->email }}" class="ml-2 text-slate-200 hover:text-white">{{ $account->email }}</a>
        @else
            <span class="ml-2 text-slate-500">—</span>
        @endif
        <span class="text-slate-600"> · {{ $account->roleLabel() }}</span>
    </p>

    <p class="text-slate-400">
        <span class="text-slate-500">Phone</span>
        @if ($account->hasContactPhone())
            <a href="{{ $account->phoneTelHref() }}" class="ml-2 font-medium text-cyan-200 hover:text-cyan-100">{{ $account->phone }}</a>
        @else
            <span class="ml-2 text-slate-500">Not on file</span>
        @endif
    </p>
    @if ($account->hasContactPhone())
        <p class="text-xs text-slate-500">If they cannot open email, copy the enrollment URL after Reset Passkey.</p>
    @else
        <p class="text-xs text-slate-500">They can add a number in Settings, or you can record one on Edit Information.</p>
    @endif
</div>

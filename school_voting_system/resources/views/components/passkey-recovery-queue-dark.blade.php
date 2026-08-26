@props(['recoveryRequests'])

<div class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-5 sm:p-6">
    <h3 class="text-lg font-semibold text-white">Passkey Recovery Requests</h3>
    <p class="mt-1 text-sm text-slate-400">
        Self-service email reset still requires a matching Account ID and email. Use this queue for unmatched or stuck attempts — issue a link to the email on file, or dismiss junk.
    </p>

    <div id="recovery-admin-status" class="mt-4 hidden rounded-xl border px-3 py-2 text-sm" role="status"></div>

    <p class="mt-4 text-sm text-slate-400 {{ $recoveryRequests->isEmpty() ? '' : 'hidden' }}" data-recovery-empty>
        No pending recovery requests.
    </p>

    <div class="{{ $recoveryRequests->isEmpty() ? 'hidden' : '' }}" data-recovery-lists>
        <div class="mt-4 space-y-3 lg:hidden" data-recovery-cards>
            @foreach ($recoveryRequests as $recoveryRequest)
                <article class="rounded-xl border border-slate-800 bg-slate-950/50 p-3" data-recovery-row="{{ $recoveryRequest->id }}">
                    <p class="font-mono text-sm text-violet-300">{{ $recoveryRequest->account_id }}</p>
                    <p class="mt-1 break-all text-sm text-slate-200">{{ $recoveryRequest->email }}</p>
                    <dl class="mt-3 grid grid-cols-2 gap-2 text-xs text-slate-400">
                        <div class="col-span-2">
                            <dt class="uppercase tracking-wide text-slate-500">Match</dt>
                            <dd class="mt-0.5">@include('admin.partials.passkey-recovery-match', ['recoveryRequest' => $recoveryRequest])</dd>
                        </div>
                        <div>
                            <dt class="uppercase tracking-wide text-slate-500">Requested</dt>
                            <dd class="mt-0.5 text-slate-200">{{ $recoveryRequest->created_at?->diffForHumans() }}</dd>
                        </div>
                        <div>
                            <dt class="uppercase tracking-wide text-slate-500">Last email</dt>
                            <dd class="mt-0.5 text-slate-200">{{ $recoveryRequest->last_sent_at?->diffForHumans() ?? 'Never' }}</dd>
                        </div>
                    </dl>
                    <div class="mt-3">
                        @include('admin.partials.passkey-recovery-actions', ['recoveryRequest' => $recoveryRequest, 'stacked' => true])
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-4 hidden overflow-x-auto lg:block">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800 text-left text-slate-400">
                        <th class="py-2 pr-4 font-medium">Account ID</th>
                        <th class="py-2 pr-4 font-medium">Requested Email</th>
                        <th class="py-2 pr-4 font-medium">Match</th>
                        <th class="py-2 pr-4 font-medium">Requested</th>
                        <th class="py-2 pr-4 font-medium">Last Email Sent</th>
                        <th class="py-2 font-medium">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recoveryRequests as $recoveryRequest)
                        <tr class="border-b border-slate-800/80 last:border-0 text-slate-200" data-recovery-row="{{ $recoveryRequest->id }}">
                            <td class="py-3 pr-4 font-mono text-violet-300">{{ $recoveryRequest->account_id }}</td>
                            <td class="py-3 pr-4">{{ $recoveryRequest->email }}</td>
                            <td class="py-3 pr-4">@include('admin.partials.passkey-recovery-match', ['recoveryRequest' => $recoveryRequest])</td>
                            <td class="py-3 pr-4">{{ $recoveryRequest->created_at?->diffForHumans() }}</td>
                            <td class="py-3 pr-4">{{ $recoveryRequest->last_sent_at?->diffForHumans() ?? 'Never' }}</td>
                            <td class="py-3">
                                @include('admin.partials.passkey-recovery-actions', ['recoveryRequest' => $recoveryRequest, 'stacked' => false])
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

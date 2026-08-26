@props(['recoveryRequests'])

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    <h3 class="text-base font-semibold text-gray-900">Passkey Recovery Requests</h3>
    <p class="mt-1 text-sm text-gray-600">
        Self-service email reset still requires a matching Account ID and email. Use this queue for unmatched or stuck attempts.
    </p>

    <div id="recovery-admin-status" class="mt-4 hidden rounded-lg border px-3 py-2 text-sm" role="status"></div>

    <p class="mt-4 text-sm text-gray-500 {{ $recoveryRequests->isEmpty() ? '' : 'hidden' }}" data-recovery-empty>
        No pending recovery requests.
    </p>

    <div class="mt-4 overflow-x-auto {{ $recoveryRequests->isEmpty() ? 'hidden' : '' }}" data-recovery-lists>
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b text-left text-gray-500">
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
                    <tr class="border-b last:border-0 text-gray-700" data-recovery-row="{{ $recoveryRequest->id }}">
                        <td class="py-3 pr-4">{{ $recoveryRequest->account_id }}</td>
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

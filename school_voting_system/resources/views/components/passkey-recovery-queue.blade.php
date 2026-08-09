@props(['recoveryRequests'])

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    <h3 class="text-base font-semibold text-gray-900">Passkey Recovery Requests</h3>
    <p class="mt-1 text-sm text-gray-600">Review pending requests and issue a signed enrollment link.</p>

    <div id="recovery-admin-status" class="mt-4 hidden rounded-lg border px-3 py-2 text-sm" role="status"></div>

    @if ($recoveryRequests->isEmpty())
        <p class="mt-4 text-sm text-gray-500">No pending recovery requests.</p>
    @else
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b text-left text-gray-500">
                        <th class="py-2 pr-4 font-medium">Student ID</th>
                        <th class="py-2 pr-4 font-medium">Email</th>
                        <th class="py-2 pr-4 font-medium">Matched User</th>
                        <th class="py-2 pr-4 font-medium">Requested</th>
                        <th class="py-2 pr-4 font-medium">Last Email Sent</th>
                        <th class="py-2 font-medium">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recoveryRequests as $recoveryRequest)
                        @php
                            $lastSentAt = $recoveryRequest->last_sent_at
                                ? \Illuminate\Support\Carbon::parse($recoveryRequest->last_sent_at)
                                : null;
                            $cooldownRemaining = $lastSentAt
                                ? max(0, 120 - $lastSentAt->diffInSeconds(now()))
                                : 0;
                            $cooldownActive = $cooldownRemaining > 0;
                        @endphp
                        <tr class="border-b last:border-0 text-gray-700">
                            <td class="py-3 pr-4">{{ $recoveryRequest->account_id }}</td>
                            <td class="py-3 pr-4">{{ $recoveryRequest->email }}</td>
                            <td class="py-3 pr-4">
                                {{ $recoveryRequest->user?->name ?? 'No exact user match' }}
                            </td>
                            <td class="py-3 pr-4">{{ $recoveryRequest->created_at?->diffForHumans() }}</td>
                            <td class="py-3 pr-4">
                                {{ $recoveryRequest->last_sent_at ? \Illuminate\Support\Carbon::parse($recoveryRequest->last_sent_at)->diffForHumans() : 'Never' }}
                            </td>
                            <td class="py-3">
                                @if ($recoveryRequest->user_id)
                                    <button
                                        type="button"
                                        class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800"
                                        data-reset-url="{{ route('admin.passkey.reset', $recoveryRequest->user_id) }}"
                                        data-recovery-request-id="{{ $recoveryRequest->id }}"
                                        @disabled($cooldownActive)
                                    >
                                        Generate enrollment link
                                    </button>
                                    @if ($cooldownActive)
                                        <p class="mt-1 text-xs text-amber-700">
                                            Cooldown active: retry in {{ $cooldownRemaining }}s
                                        </p>
                                    @endif
                                @else
                                    <span class="text-xs text-amber-700">Needs manual verification</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>


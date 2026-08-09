<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin Dashboard</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-lg font-medium text-gray-900">Welcome, {{ $user->name }}.</p>
                <p class="mt-2 text-sm text-gray-600">Manage elections, events, and fundraisers.</p>

                @if ($user->passkeys_count === 0)
                    <div class="mt-6 max-w-lg">
                        <x-passkey-register
                            :register-options-url="route('register.passkey.options')"
                            :register-verify-url="route('register.passkey.verify')"
                        />
                    </div>
                @endif
            </div>

            <x-passkey-recovery-queue :recovery-requests="$recoveryRequests" />
        </div>
    </div>

    @vite('resources/js/passkey-admin-recovery.js')
</x-app-layout>

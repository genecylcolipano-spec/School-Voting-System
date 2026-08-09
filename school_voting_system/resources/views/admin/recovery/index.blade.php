<x-app-layout>
    <x-admin-portal title="Passkey Recovery" :user="$user" :notifications-count="$notificationsCount">
        <x-passkey-recovery-queue-dark :recovery-requests="$recoveryRequests" />
    </x-admin-portal>

    @vite('resources/js/passkey-admin-recovery.js')
</x-app-layout>

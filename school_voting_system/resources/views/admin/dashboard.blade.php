<x-app-layout>
    <x-admin-portal
        :title="'Admin Dashboard'"
        :user="$user"
        :notifications-count="$notificationsCount"
        :assigned-role="$assignedRole"
    >
        @if ($user->passkeys->isEmpty())
            <div class="max-w-xl">
                <x-passkey-register
                    :register-options-url="route('register.passkey.options')"
                    :register-verify-url="route('register.passkey.verify')"
                />
            </div>
        @endif

        <div class="space-y-6">
            <div
                id="admin-dashboard-live"
                data-live-url="{{ route('admin.dashboard.live') }}"
                class="hidden"
                aria-hidden="true"
            ></div>

            @include('admin.dashboard._role-banner')
            @include('admin.dashboard._bento')
            @include('admin.dashboard._posters')
            <section id="events" class="scroll-mt-28">
                @include('admin.dashboard._events-preview')
            </section>
            @include('admin.dashboard._talent')
            @include('admin.dashboard._fundraisers')
            @include('admin.dashboard._activity-timeline')
            @include('admin.dashboard._activity')
            @include('admin.dashboard._auditor')
        </div>
    </x-admin-portal>

    @vite(['resources/js/regular-admin-dashboard.js', 'resources/js/admin-dashboard-live.js'])
</x-app-layout>

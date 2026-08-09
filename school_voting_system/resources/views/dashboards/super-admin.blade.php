<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Super Admin Console</h2>
                <p class="mt-1 text-sm text-gray-600">Manage users, recovery requests, and system-wide security.</p>
            </div>

            <div class="flex items-center justify-end">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ $user->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-lg font-medium text-gray-900">Welcome, {{ $user->name }}.</p>
                <p class="mt-2 text-sm text-gray-600">Full system oversight, user recovery, and security controls.</p>

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

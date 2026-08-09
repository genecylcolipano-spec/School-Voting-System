<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">Passkey devices</h2>
        <p class="mt-1 text-sm text-gray-600">
            Register multiple devices. Your private keys never leave the hardware authenticator.
        </p>
    </header>

    <div class="mt-6 space-y-6">
        <x-passkey-register
            :register-options-url="route('register.passkey.options')"
            :register-verify-url="route('register.passkey.verify')"
        />

        <div>
            <h3 class="text-sm font-semibold text-gray-800">Registered devices</h3>
            <ul id="passkey-device-list" class="mt-3 divide-y divide-gray-200 rounded-lg border border-gray-200"
                data-index-url="{{ route('passkeys.index') }}"
                data-csrf="{{ csrf_token() }}">
                <li class="px-4 py-3 text-sm text-gray-500">Loading devices…</li>
            </ul>
        </div>
    </div>

    @vite(['resources/js/passkey-devices.js'])
</section>

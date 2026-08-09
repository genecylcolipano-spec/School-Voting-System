/**
 * Rename / remove registered passkey devices on Settings → Devices.
 * Keeps server-rendered markup; does not wipe the list on load.
 */

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content
        ?? document.getElementById('passkey-device-list')?.dataset.csrf
        ?? '';
}

function updateUrl(template, id) {
    return String(template || '/user/passkeys/__ID__').replace('__ID__', String(id));
}

async function renameDevice(list, id, currentName) {
    const next = window.prompt('Rename this device', currentName || 'Device');
    if (next === null) {
        return;
    }

    const name = next.trim();
    if (!name) {
        window.alert('Device name cannot be empty.');
        return;
    }

    const response = await fetch(updateUrl(list.dataset.updateUrlTemplate, id), {
        method: 'PATCH',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify({ device_name: name }),
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        window.alert(data.message ?? data.errors?.device_name?.[0] ?? 'Could not rename device.');
        return;
    }

    window.location.reload();
}

async function removeDevice(id) {
    if (!window.confirm('Remove this passkey from your account? You must keep at least one device.')) {
        return;
    }

    const response = await fetch(`/user/passkeys/${id}`, {
        method: 'DELETE',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        window.alert(data.message ?? 'Could not remove device.');
        return;
    }

    window.location.reload();
}

function bindDeviceList() {
    const list = document.getElementById('passkey-device-list');
    if (!list || list.dataset.bound === '1') {
        return;
    }

    list.dataset.bound = '1';

    list.addEventListener('click', (event) => {
        const renameBtn = event.target.closest('[data-rename]');
        if (renameBtn) {
            event.preventDefault();
            renameDevice(list, renameBtn.dataset.rename, renameBtn.dataset.name || 'Device');
            return;
        }

        const removeBtn = event.target.closest('[data-remove]');
        if (removeBtn) {
            event.preventDefault();
            removeDevice(removeBtn.dataset.remove);
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindDeviceList);
} else {
    bindDeviceList();
}

/**
 * Regular Admin Dashboard — filters, countdown.
 */

import { initAdminConfirmations } from './admin-confirm';

function initEntryRejectToggles() {
    document.querySelectorAll('[data-entry-reject-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const id = button.dataset.entryRejectToggle;
            document.getElementById(`entry-reject-form-${id}`)?.classList.toggle('hidden');
        });
    });
}

function initTurnoutBars() {
    document.querySelectorAll('[data-turnout-bar]').forEach((bar) => {
        const value = Number(bar.dataset.turnout ?? 0);
        bar.style.width = `${Math.min(100, value)}%`;
    });
}

function initCountdown() {
    const container = document.getElementById('election-countdown');
    const display = container?.querySelector('[data-countdown-display]');
    const endsAt = container?.dataset.countdownEnds;

    if (!container || !display || !endsAt) {
        return;
    }

    const target = new Date(endsAt).getTime();

    const tick = () => {
        const diff = target - Date.now();

        if (diff <= 0) {
            display.textContent = '0h 0m';
            return;
        }

        const hours = Math.floor(diff / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        display.textContent = `${hours}h ${minutes}m`;
    };

    tick();
    window.setInterval(tick, 60_000);
}

document.addEventListener('DOMContentLoaded', () => {
    initAdminConfirmations();
    initEntryRejectToggles();
    initTurnoutBars();
    initCountdown();
});

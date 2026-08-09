/**
 * Super Admin Dashboard — universal search, bulk actions, live filters.
 */

import { initAdminConfirmations } from './admin-confirm';

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

async function fetchJson(url, options = {}) {
    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf(),
            ...(options.headers ?? {}),
        },
        ...options,
    });

    return response.json().catch(() => ({}));
}

function initUniversalSearch() {
    const input = document.getElementById('super-admin-search');
    const panel = document.getElementById('super-admin-search-results');

    if (!input || !panel) return;

    let timer;

    input.addEventListener('input', () => {
        clearTimeout(timer);
        const q = input.value.trim();

        if (q.length < 2) {
            panel.classList.add('hidden');
            panel.innerHTML = '';
            return;
        }

        timer = setTimeout(async () => {
            const data = await fetchJson(`${window.superAdminPortal.searchUrl}?q=${encodeURIComponent(q)}`);
            const accounts = data.results?.accounts ?? [];
            const elections = data.results?.elections ?? [];

            if (!accounts.length && !elections.length) {
                panel.innerHTML = '<p class="px-4 py-3 text-sm text-slate-400">No matches found.</p>';
            } else {
                panel.innerHTML = [
                    ...accounts.map((u) => `<a href="#" class="block px-4 py-2 text-sm text-slate-200 hover:bg-slate-800" data-account="${u.account_id}">${u.name} <span class="text-slate-500">(${u.account_id})</span></a>`),
                    ...elections.map((e) => `<div class="px-4 py-2 text-sm text-violet-300">Election: ${e.title}</div>`),
                ].join('');
            }

            panel.classList.remove('hidden');
        }, 250);
    });
}

function initBulkSelect() {
    const master = document.getElementById('bulk-select-all');
    const checks = document.querySelectorAll('[data-bulk-user]');

    master?.addEventListener('change', () => {
        checks.forEach((c) => { c.checked = master.checked; });
    });
}

function initAuditFilter() {
    const form = document.getElementById('audit-filter-form');
    const rows = document.querySelectorAll('[data-audit-row]');

    form?.addEventListener('change', () => {
        const type = form.action_type?.value ?? '';
        const status = form.status?.value ?? '';

        rows.forEach((row) => {
            const matchType = !type || row.dataset.type === type;
            const matchStatus = !status || row.dataset.status === status;
            row.classList.toggle('hidden', !(matchType && matchStatus));
        });
    });
}

function initSensitiveConfirmations() {
    initAdminConfirmations();
}

function initPortalBulkForm() {
    const form = document.querySelector('[data-portal-bulk-form]');
    if (!form) return;

    form.addEventListener('submit', (event) => {
        const action = form.querySelector('[data-portal-bulk-action]')?.value ?? '';
        const selected = form.querySelectorAll('[data-bulk-user]:checked').length;

        if (selected === 0) {
            event.preventDefault();
            window.alert('Select at least one portal account.');
            return;
        }

        if (action === 'delete') {
            const confirmed = window.confirm(
                `Permanently delete ${selected} account(s)? Super Admin accounts and your own account will be skipped.`,
            );
            if (!confirmed) {
                event.preventDefault();
            }
            return;
        }

        if (action === 'deactivate') {
            const confirmed = window.confirm(
                `Deactivate ${selected} account(s)? They will not be able to sign in until reactivated.`,
            );
            if (!confirmed) {
                event.preventDefault();
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initUniversalSearch();
    initBulkSelect();
    initAuditFilter();
    initSensitiveConfirmations();
    initPortalBulkForm();
});

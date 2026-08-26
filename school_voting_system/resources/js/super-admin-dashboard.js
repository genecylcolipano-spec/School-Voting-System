/**
 * Super Admin Dashboard — universal search, bulk actions, live filters.
 */

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

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function hideSearchPanel(panel) {
    panel.classList.add('hidden');
    panel.innerHTML = '';
}

function initUniversalSearch() {
    const roots = document.querySelectorAll('[data-super-admin-search]');
    if (!roots.length || !window.superAdminPortal?.searchUrl) return;

    const panels = [];

    roots.forEach((root) => {
        const input = root.querySelector('input[type="search"]');
        const panel = root.querySelector('[data-super-admin-search-results]');
        if (!input || !panel) return;

        panels.push(panel);
        let timer;

        input.addEventListener('input', () => {
            clearTimeout(timer);
            const q = input.value.trim();

            panels.forEach((other) => {
                if (other !== panel) hideSearchPanel(other);
            });

            if (q.length < 2) {
                hideSearchPanel(panel);
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
                        ...accounts.map((u) => `<a href="${escapeHtml(u.url || '#')}" class="block px-4 py-2 text-sm text-slate-200 hover:bg-slate-800">${escapeHtml(u.name)} <span class="text-slate-500">(${escapeHtml(u.account_id)})</span></a>`),
                        ...elections.map((e) => `<a href="${escapeHtml(e.url || '#')}" class="block px-4 py-2 text-sm text-violet-300 hover:bg-slate-800">Election: ${escapeHtml(e.title)}</a>`),
                    ].join('');
                }

                panel.classList.remove('hidden');
            }, 250);
        });
    });

    document.addEventListener('pointerdown', (event) => {
        roots.forEach((root) => {
            if (!root.contains(event.target)) {
                const panel = root.querySelector('[data-super-admin-search-results]');
                if (panel) hideSearchPanel(panel);
            }
        });
    });
}

function layoutIsDesktop() {
    return window.matchMedia('(min-width: 1024px)').matches;
}

function syncBulkLayout() {
    const desktop = layoutIsDesktop();

    document.querySelectorAll('[data-bulk-user][data-bulk-layout="mobile"]').forEach((el) => {
        el.disabled = desktop;
        if (desktop) {
            el.checked = false;
        }
    });

    document.querySelectorAll('[data-bulk-user][data-bulk-layout="desktop"]').forEach((el) => {
        el.disabled = !desktop;
        if (!desktop) {
            el.checked = false;
        }
    });
}

function enabledBulkChecks() {
    return document.querySelectorAll('[data-bulk-user]:not(:disabled)');
}

function initBulkSelect() {
    syncBulkLayout();
    window.matchMedia('(min-width: 1024px)').addEventListener('change', syncBulkLayout);

    const masters = [
        document.getElementById('bulk-select-all'),
        document.getElementById('bulk-select-all-mobile'),
    ].filter(Boolean);

    masters.forEach((master) => {
        master.addEventListener('change', () => {
            enabledBulkChecks().forEach((c) => {
                c.checked = master.checked;
            });
            masters.forEach((other) => {
                if (other !== master) {
                    other.checked = master.checked;
                }
            });
        });
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

function initPortalBulkForm() {
    const form = document.querySelector('[data-portal-bulk-form]');
    if (!form) return;

    form.addEventListener('submit', (event) => {
        const action = form.querySelector('[data-portal-bulk-action]')?.value ?? '';
        const selected = form.querySelectorAll('[data-bulk-user]:checked:not(:disabled)').length;

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
    initPortalBulkForm();
});

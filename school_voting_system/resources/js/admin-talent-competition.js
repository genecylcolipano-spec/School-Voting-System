const LIVE_INTERVAL_MS = 5000;

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

function formatNumber(value) {
    return new Intl.NumberFormat().format(Number(value) || 0);
}

function filterEventTable(dashboard) {
    const table = dashboard.querySelector('[data-tc-table]');
    const tbody = table?.querySelector('tbody');

    if (!tbody) {
        return;
    }

    const search = (dashboard.querySelector('[data-tc-search]')?.value ?? '').trim().toLowerCase();
    const grade = dashboard.querySelector('[data-tc-grade-filter]')?.value ?? '';
    const category = dashboard.querySelector('[data-tc-category-filter]')?.value ?? '';
    const status = dashboard.querySelector('[data-tc-status-filter]')?.value ?? '';
    const sort = dashboard.querySelector('[data-tc-sort]')?.value ?? 'votes-desc';

    let rows = [...tbody.querySelectorAll('[data-tc-row]')];

    rows = rows.filter((row) => {
        const haystack = `${row.dataset.name ?? ''} ${row.dataset.grade ?? ''} ${row.dataset.category ?? ''}`.toLowerCase();
        const matchesSearch = !search || haystack.includes(search);
        const matchesGrade = !grade || row.dataset.grade === grade;
        const matchesCategory = !category || row.dataset.category === category;
        const matchesStatus = !status || row.dataset.status === status;

        return matchesSearch && matchesGrade && matchesCategory && matchesStatus;
    });

    rows.sort((left, right) => {
        const leftVotes = Number(left.dataset.votes) || 0;
        const rightVotes = Number(right.dataset.votes) || 0;
        const leftRank = Number(left.dataset.rank) || 999;
        const rightRank = Number(right.dataset.rank) || 999;

        if (sort === 'votes-asc') {
            return leftVotes - rightVotes || leftRank - rightRank;
        }

        if (sort === 'rank-asc') {
            return leftRank - rightRank || rightVotes - leftVotes;
        }

        return rightVotes - leftVotes || leftRank - rightRank;
    });

    rows.forEach((row) => tbody.appendChild(row));

    const meta = dashboard.querySelector('[data-tc-table-meta]');

    if (meta) {
        const total = tbody.querySelectorAll('[data-tc-row]').length;
        meta.textContent = rows.length === total
            ? `Showing ${rows.length} contestant${rows.length === 1 ? '' : 's'}`
            : `Showing ${rows.length} of ${total} contestants`;
    }
}

function syncContestantTable(dashboard, rankings) {
    const rowsById = new Map(
        [...dashboard.querySelectorAll('[data-tc-row]')].map((row) => [row.dataset.entryId ?? row.dataset.name, row]),
    );

    rankings.forEach((row) => {
        const tableRow = rowsById.get(String(row.id));

        if (!tableRow) {
            return;
        }

        tableRow.dataset.votes = String(row.votes ?? 0);
        tableRow.dataset.rank = String(row.rank ?? 0);

        const votesCell = tableRow.querySelector('[data-tc-row-votes]');
        const rankCell = tableRow.querySelector('[data-tc-row-rank]');

        if (votesCell) {
            votesCell.textContent = formatNumber(row.votes);
        }

        if (rankCell) {
            rankCell.textContent = `#${row.rank}`;
        }
    });

    filterEventTable(dashboard);
}

function renderLiveMonitor(dashboard, detail) {
    const list = dashboard.querySelector('[data-tc-live-list]');
    const updated = dashboard.querySelector('[data-tc-live-updated]');
    const rankings = detail?.rankings ?? [];

    if (list) {
        const rowsById = new Map(
            [...list.querySelectorAll('[data-tc-live-row]')].map((row) => [row.dataset.entryId, row]),
        );

        rankings
            .slice()
            .sort((left, right) => (left.rank ?? 999) - (right.rank ?? 999))
            .forEach((row) => {
                const liveRow = rowsById.get(String(row.id));

                if (!liveRow) {
                    return;
                }

                const percent = Number(row.percent) || 0;

                liveRow.querySelector('[data-tc-live-rank]')?.replaceChildren(document.createTextNode(`#${row.rank}`));
                liveRow.querySelector('[data-tc-live-name]')?.replaceChildren(document.createTextNode(row.name ?? ''));
                liveRow.querySelector('[data-tc-live-votes]')?.replaceChildren(document.createTextNode(formatNumber(row.votes)));
                liveRow.querySelector('[data-tc-live-percent]')?.replaceChildren(document.createTextNode(`${percent.toFixed(1)}%`));

                const bar = liveRow.querySelector('[data-tc-live-bar]');

                if (bar) {
                    bar.style.width = `${Math.min(100, percent)}%`;
                }
            });
    }

    if (updated && detail?.updated_at) {
        updated.textContent = `Last updated ${new Date(detail.updated_at).toLocaleTimeString()}`;
    }

    syncContestantTable(dashboard, rankings);
}

async function pollLiveMonitor(dashboard) {
    const monitor = dashboard.querySelector('[data-tc-live-monitor]');

    if (!monitor || monitor.dataset.isLive !== '1') {
        return;
    }

    const url = monitor.dataset.liveUrl;

    if (!url) {
        return;
    }

    try {
        const response = await fetch(url, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            return;
        }

        renderLiveMonitor(dashboard, await response.json());
    } catch {
        // ignore transient network errors
    }
}

function initLiveMonitor(dashboard) {
    const monitor = dashboard.querySelector('[data-tc-live-monitor]');

    if (!monitor || monitor.dataset.isLive !== '1') {
        return;
    }

    pollLiveMonitor(dashboard);
    window.setInterval(() => pollLiveMonitor(dashboard), LIVE_INTERVAL_MS);
}

function initDashboard(dashboard) {
    ['data-tc-search', 'data-tc-grade-filter', 'data-tc-category-filter', 'data-tc-status-filter', 'data-tc-sort'].forEach((attr) => {
        dashboard.querySelector(`[${attr}]`)?.addEventListener('input', () => filterEventTable(dashboard));
        dashboard.querySelector(`[${attr}]`)?.addEventListener('change', () => filterEventTable(dashboard));
    });

    dashboard.querySelectorAll('[data-tc-expand]').forEach((button) => {
        button.addEventListener('click', () => {
            const target = dashboard.querySelector(`#${button.dataset.tcExpand}`);

            if (!target) {
                return;
            }

            const isHidden = target.classList.contains('hidden');
            target.classList.toggle('hidden', !isHidden);
            button.textContent = isHidden ? 'Hide details' : 'View Details';
        });
    });

    filterEventTable(dashboard);
    initLiveMonitor(dashboard);
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-tc-dashboard]').forEach(initDashboard);
});

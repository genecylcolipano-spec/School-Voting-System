import { LIVE_INTERVAL_MS, renderChartSvg, escapeHtml, formatNumber } from './admin-dashboard-live.js';

const PAGE_SIZE = 10;
const CHART_COLORS = ['#818cf8', '#34d399', '#fbbf24', '#fb7185', '#22d3ee', '#a78bfa', '#f472b6', '#94a3b8'];

let tableState = {
    rows: [],
    filtered: [],
    sortKey: 'rank',
    sortDir: 'asc',
    page: 1,
};

function readInitialPayload() {
    const node = document.getElementById('results-initial-payload');

    if (!node) {
        return null;
    }

    try {
        return JSON.parse(node.textContent ?? '{}');
    } catch {
        return null;
    }
}

function renderPieChart(svg, labels, values, { doughnut = false } = {}) {
    if (!svg) {
        return;
    }

    const nums = values.map((value) => Number(value) || 0);
    const total = nums.reduce((sum, value) => sum + value, 0);

    if (total <= 0) {
        svg.innerHTML = '<text x="80" y="84" text-anchor="middle" fill="#94a3b8" font-size="11">No data</text>';
        return;
    }

    const cx = 80;
    const cy = 80;
    const radius = 60;
    const inner = doughnut ? 36 : 0;
    let start = -Math.PI / 2;
    let body = '';
    const legend = [];

    nums.forEach((value, index) => {
        if (value <= 0) {
            return;
        }

        const slice = (value / total) * Math.PI * 2;
        const end = start + slice;
        const color = CHART_COLORS[index % CHART_COLORS.length];
        const x1 = cx + radius * Math.cos(start);
        const y1 = cy + radius * Math.sin(start);
        const x2 = cx + radius * Math.cos(end);
        const y2 = cy + radius * Math.sin(end);
        const large = slice > Math.PI ? 1 : 0;

        if (doughnut) {
            const ix1 = cx + inner * Math.cos(end);
            const iy1 = cy + inner * Math.sin(end);
            const ix2 = cx + inner * Math.cos(start);
            const iy2 = cy + inner * Math.sin(start);
            body += `<path d="M ${x1} ${y1} A ${radius} ${radius} 0 ${large} 1 ${x2} ${y2} L ${ix1} ${iy1} A ${inner} ${inner} 0 ${large} 0 ${ix2} ${iy2} Z" fill="${color}" opacity="0.92" />`;
        } else {
            body += `<path d="M ${cx} ${cy} L ${x1} ${y1} A ${radius} ${radius} 0 ${large} 1 ${x2} ${y2} Z" fill="${color}" opacity="0.92" />`;
        }

        legend.push({ label: labels[index] ?? '', color, value });
        start = end;
    });

    svg.innerHTML = body;
}

function applyCharts(detail) {
    const charts = detail?.charts ?? {};

    document.querySelectorAll('[data-results-chart="bar"]').forEach((container) => {
        renderChartSvg(container, charts.bar ?? {});
    });

    renderPieChart(
        document.querySelector('[data-results-pie-canvas]'),
        charts.pie?.labels ?? [],
        charts.pie?.values ?? [],
    );

    renderPieChart(
        document.querySelector('[data-results-doughnut-canvas]'),
        charts.doughnut?.labels ?? [],
        charts.doughnut?.values ?? [],
        { doughnut: true },
    );
}

function applySummary(summary = {}) {
    const map = {
        total_votes: formatNumber(summary.total_votes ?? 0),
        turnout_percent: `${Number(summary.turnout_percent ?? 0).toFixed(1)}%`,
        winners_count: formatNumber(summary.winners_count ?? 0),
        participants: formatNumber(summary.participants ?? 0),
    };

    Object.entries(map).forEach(([key, value]) => {
        const el = document.querySelector(`[data-results-stat="${key}"]`);
        if (el) {
            el.textContent = value;
        }
    });
}

function winnerCardHtml(winner) {
    const party = winner.party && winner.party !== '—'
        ? `<p class="mt-1 text-xs text-slate-400">${escapeHtml(winner.party)}</p>`
        : '';

    return `
        <article class="rs-winner-card rounded-xl border border-violet-500/15 bg-slate-950/50 p-4">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-violet-300">${escapeHtml(winner.label ?? '')}</p>
            <p class="mt-2 text-lg font-bold text-white">${escapeHtml(winner.name ?? '—')}</p>
            ${party}
            <p class="mt-3 text-sm text-slate-300">${formatNumber(winner.votes ?? 0)} votes · ${Number(winner.percent ?? 0).toFixed(1)}%</p>
        </article>
    `;
}

function applyWinners(winners = []) {
    const grid = document.getElementById('results-winners-grid');

    if (!grid) {
        return;
    }

    const primary = winners.filter((winner) => winner.group !== 'top_ten');

    if (!primary.length) {
        grid.innerHTML = '<p class="text-sm text-slate-400 sm:col-span-2 lg:col-span-3">No winners recorded yet.</p>';
        return;
    }

    grid.innerHTML = primary.map(winnerCardHtml).join('');
}

function applyActivity(activity = []) {
    const container = document.getElementById('results-activity');

    if (!container) {
        return;
    }

    if (!activity.length) {
        container.innerHTML = '<p class="text-sm text-slate-400">No timeline events recorded yet.</p>';
        return;
    }

    container.innerHTML = activity.map((item) => `
        <div class="relative pl-4">
            <span class="absolute -left-[1.34rem] top-1.5 h-2.5 w-2.5 rounded-full bg-violet-400"></span>
            <p class="text-sm font-semibold text-white">${escapeHtml(item.label ?? '')}</p>
            <p class="text-xs text-slate-400">${escapeHtml(item.display ?? '—')}</p>
        </div>
    `).join('');
}

function compareRows(a, b, key) {
    const left = a[key];
    const right = b[key];

    if (key === 'votes' || key === 'percent' || key === 'rank') {
        return Number(left) - Number(right);
    }

    return String(left ?? '').localeCompare(String(right ?? ''), undefined, { sensitivity: 'base' });
}

function renderTableBody() {
    const tbody = document.getElementById('results-rankings-body');
    const meta = document.getElementById('results-table-meta');
    const prev = document.getElementById('results-table-prev');
    const next = document.getElementById('results-table-next');

    if (!tbody) {
        return;
    }

    const total = tableState.filtered.length;
    const pages = Math.max(1, Math.ceil(total / PAGE_SIZE));
    tableState.page = Math.min(tableState.page, pages);
    const start = (tableState.page - 1) * PAGE_SIZE;
    const slice = tableState.filtered.slice(start, start + PAGE_SIZE);

    tbody.innerHTML = slice.map((row) => {
        const statusClass = row.status === 'Winner'
            ? 'bg-emerald-500/15 text-emerald-300'
            : ['Trailing', 'Finalist'].includes(row.status)
                ? 'bg-violet-500/15 text-violet-300'
                : 'bg-slate-700/50 text-slate-400';

        return `
            <tr class="border-b border-slate-800/80 text-slate-200">
                <td class="px-4 py-3">${row.rank ?? ''}</td>
                <td class="px-4 py-3 font-medium text-white">${escapeHtml(row.name ?? '')}</td>
                <td class="px-4 py-3">${escapeHtml(row.position ?? '')}</td>
                <td class="px-4 py-3">${escapeHtml(row.party ?? '')}</td>
                <td class="px-4 py-3">${formatNumber(row.votes ?? 0)}</td>
                <td class="px-4 py-3">${Number(row.percent ?? 0).toFixed(1)}%</td>
                <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase ${statusClass}">${escapeHtml(row.status ?? '')}</span></td>
            </tr>
        `;
    }).join('') || '<tr><td colspan="7" class="px-4 py-6 text-center text-slate-400">No matching rows.</td></tr>';

    if (meta) {
        meta.textContent = total
            ? `Showing ${start + 1}–${Math.min(start + PAGE_SIZE, total)} of ${total}`
            : 'No rows to display';
    }

    if (prev) {
        prev.disabled = tableState.page <= 1;
    }

    if (next) {
        next.disabled = tableState.page >= pages;
    }
}

function filterAndSortRows() {
    const search = document.getElementById('results-table-search')?.value?.trim().toLowerCase() ?? '';
    const status = document.getElementById('results-table-filter')?.value ?? '';

    tableState.filtered = tableState.rows.filter((row) => {
        const matchesStatus = !status || row.status === status;
        const haystack = `${row.name} ${row.position} ${row.party}`.toLowerCase();
        const matchesSearch = !search || haystack.includes(search);

        return matchesStatus && matchesSearch;
    });

    tableState.filtered.sort((a, b) => {
        const result = compareRows(a, b, tableState.sortKey);
        return tableState.sortDir === 'asc' ? result : -result;
    });

    tableState.page = 1;
    renderTableBody();
}

let tableInitialized = false;

function initRankingsTable(rows = []) {
    tableState.rows = rows;
    tableState.filtered = [...rows];

    if (!tableInitialized) {
        tableInitialized = true;

        document.getElementById('results-table-search')?.addEventListener('input', filterAndSortRows);
        document.getElementById('results-table-filter')?.addEventListener('change', filterAndSortRows);
        document.getElementById('results-table-prev')?.addEventListener('click', () => {
            tableState.page -= 1;
            renderTableBody();
        });
        document.getElementById('results-table-next')?.addEventListener('click', () => {
            tableState.page += 1;
            renderTableBody();
        });

        document.querySelectorAll('#results-rankings-table th[data-sort]').forEach((header) => {
            header.addEventListener('click', () => {
                const key = header.dataset.sort;

                if (tableState.sortKey === key) {
                    tableState.sortDir = tableState.sortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    tableState.sortKey = key;
                    tableState.sortDir = key === 'votes' || key === 'percent' || key === 'rank' ? 'desc' : 'asc';
                }

                filterAndSortRows();
            });
        });
    }

    filterAndSortRows();
}

function applyDetail(detail) {
    if (!detail) {
        return;
    }

    applySummary(detail.summary);
    applyWinners(detail.winners);
    applyCharts(detail);
    applyActivity(detail.activity);
    initRankingsTable(detail.rankings ?? []);

    const updated = document.getElementById('results-live-updated');

    if (updated && detail.updated_at) {
        updated.textContent = `Updated ${new Date(detail.updated_at).toLocaleTimeString()}`;
    }
}

async function pollLive(dashboard) {
    const url = dashboard.dataset.liveUrl;
    const isLive = dashboard.dataset.isLive === '1';

    if (!url || !isLive) {
        return;
    }

    try {
        const response = await fetch(url, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (!response.ok) {
            return;
        }

        applyDetail(await response.json());
    } catch {
        // ignore transient network errors
    }
}

function initPrint() {
    document.querySelector('[data-results-print]')?.addEventListener('click', () => {
        window.print();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const dashboard = document.getElementById('admin-results-dashboard');
    const initial = readInitialPayload();

    if (initial) {
        applyDetail(initial);
    }

    initPrint();

    if (dashboard?.dataset.isLive === '1') {
        pollLive(dashboard);
        window.setInterval(() => pollLive(dashboard), LIVE_INTERVAL_MS);
    }
});

export { applyDetail, renderPieChart };

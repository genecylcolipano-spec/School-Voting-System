import { LIVE_INTERVAL_MS, renderChartSvg } from './admin-dashboard-live.js';

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

function formatUpdatedAt(isoString) {
    if (!isoString) {
        return '';
    }

    const date = new Date(isoString);

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    return date.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', second: '2-digit' });
}

function applyTurnoutBreakdown(sections) {
    const container = document.getElementById('analytics-turnout-breakdown');

    if (!container) {
        return;
    }

    if (!sections?.length) {
        container.innerHTML = '<p class="text-sm text-slate-400">No turnout data for the selected election yet.</p>';
        return;
    }

    container.innerHTML = sections.map((section) => `
        <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3">
            <div class="flex items-center justify-between gap-2">
                <p class="truncate text-sm font-medium text-white">${escapeHtml(section.label)}</p>
                <span class="shrink-0 text-sm font-semibold text-violet-300">${Number(section.turnout).toFixed(1)}%</span>
            </div>
            <p class="mt-1 text-xs text-slate-500">${Number(section.voted).toLocaleString()} voted · ${Number(section.eligible).toLocaleString()} eligible</p>
            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-800">
                <div class="h-full rounded-full bg-violet-500 transition-all" style="width:${Math.min(100, Number(section.turnout) || 0)}%"></div>
            </div>
        </div>
    `).join('');
}

function applyFundraisingPerformance(fundraising) {
    if (!fundraising?.values?.length) {
        return;
    }

    const values = fundraising.values.map((value) => Number(value) || 0);
    const ytdTotal = values.reduce((sum, value) => sum + value, 0);
    const bestMonthIndex = values.indexOf(Math.max(...values));
    const bestMonth = fundraising.labels?.[bestMonthIndex] ?? '—';
    const bestAmount = values[bestMonthIndex] ?? 0;
    const avgMonth = values.length > 0 ? ytdTotal / values.length : 0;

    const ytd = document.querySelector('[data-live-fundraising-ytd]');
    const best = document.querySelector('[data-live-fundraising-best-month]');
    const bestAmountEl = document.querySelector('[data-live-fundraising-best-amount]');
    const avg = document.querySelector('[data-live-fundraising-avg]');

    if (ytd) {
        ytd.textContent = `₱${ytdTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }

    if (best) {
        best.textContent = bestMonth;
    }

    if (bestAmountEl) {
        bestAmountEl.textContent = `₱${bestAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }

    if (avg) {
        avg.textContent = `₱${avgMonth.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }
}

function applyCampaignPerformance(campaigns) {
    const container = document.getElementById('analytics-campaign-performance');

    if (!container) {
        return;
    }

    if (!campaigns?.length) {
        container.innerHTML = '<p class="text-sm text-slate-400">No campaign vote data for the selected election yet.</p>';
        return;
    }

    container.innerHTML = campaigns.map((campaign) => {
        const color = campaign.color
            ? `<span class="inline-block h-3 w-3 rounded-full" style="background: ${escapeHtml(campaign.color)}"></span>`
            : '';
        const acronym = campaign.acronym
            ? `<span class="text-xs text-violet-300">${escapeHtml(campaign.acronym)}</span>`
            : '';
        const positions = Array.isArray(campaign.winning_positions) && campaign.winning_positions.length
            ? `<p class="mt-1 text-xs text-emerald-300">Won: ${escapeHtml(campaign.winning_positions.join(', '))}</p>`
            : '';
        const share = Number(campaign.vote_share) || 0;

        return `
            <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        ${color}
                        <p class="text-sm font-medium text-white">${escapeHtml(campaign.name)}</p>
                        ${acronym}
                    </div>
                    <span class="shrink-0 text-sm font-semibold text-violet-300">${share}%</span>
                </div>
                <p class="mt-1 text-xs text-slate-500">
                    ${Number(campaign.total_votes || 0).toLocaleString()} votes · ${Number(campaign.total_candidates || 0).toLocaleString()} candidate(s) · ${Number(campaign.winning_candidates || 0).toLocaleString()} winning
                </p>
                ${positions}
                <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-800">
                    <div class="h-full rounded-full bg-violet-500" style="width:${Math.min(100, share)}%"></div>
                </div>
            </div>
        `;
    }).join('');
}

function applyTalentCompetitions(events) {
    const container = document.getElementById('analytics-talent-competitions');

    if (!container) {
        return;
    }

    if (!events?.length) {
        container.innerHTML = '<tr><td colspan="7" class="px-3 py-6 text-center text-slate-400">No talent competition data in your scope yet.</td></tr>';
        return;
    }

    container.innerHTML = events.map((event) => {
        const winners = Array.isArray(event.winners) && event.winners.length
            ? escapeHtml(event.winners.join(', '))
            : '—';

        return `
            <tr class="text-slate-300">
                <td class="px-3 py-3 font-medium text-white">${escapeHtml(event.name)}</td>
                <td class="px-3 py-3">${escapeHtml(event.talent_category)}</td>
                <td class="px-3 py-3">${Number(event.contestants || 0).toLocaleString()}</td>
                <td class="px-3 py-3">${Number(event.total_votes || 0).toLocaleString()}</td>
                <td class="px-3 py-3">${escapeHtml(event.voting_method)}</td>
                <td class="px-3 py-3 text-emerald-300">${winners}</td>
                <td class="px-3 py-3">${escapeHtml(event.display_status)}</td>
            </tr>
        `;
    }).join('');
}

function applyAnalyticsReport(report) {
    if (!report) {
        return;
    }

    const chartMap = {
        participation: report.participation,
        fundraising: report.fundraising,
        turnout: report.turnout,
        campaigns: report.campaigns,
        events: report.events,
    };

    document.querySelectorAll('[data-live-chart]').forEach((container) => {
        const key = container.dataset.liveChart;
        if (chartMap[key]) {
            renderChartSvg(container, chartMap[key]);
        }
    });

    applyTurnoutBreakdown(report.turnoutSections ?? []);
    applyFundraisingPerformance(report.fundraising);
    applyCampaignPerformance(report.campaignPerformance ?? []);
    applyTalentCompetitions(report.talentCompetitions ?? []);

    const stamp = document.getElementById('analytics-live-updated');
    if (stamp && report.updated_at) {
        stamp.textContent = `Live · ${formatUpdatedAt(report.updated_at)}`;
    }
}

function initAnalyticsLive() {
    const root = document.getElementById('admin-analytics-live');
    const endpoint = root?.dataset.liveUrl;

    if (!root || !endpoint) {
        return;
    }

    const fetchReport = async () => {
        try {
            const response = await fetch(endpoint, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            applyAnalyticsReport(await response.json());
        } catch {
            // Keep last rendered report on transient network errors.
        }
    };

    fetchReport();
    window.setInterval(fetchReport, LIVE_INTERVAL_MS);
}

document.addEventListener('DOMContentLoaded', initAnalyticsLive);

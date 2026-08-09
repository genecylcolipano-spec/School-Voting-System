/**
 * Real-time dashboard polling — stats, analytics charts, events, fundraisers, voting.
 */

const LIVE_INTERVAL_MS = 5000;
const CHART_LEGEND_COLORS = ['#818cf8', '#34d399', '#fbbf24', '#fb7185', '#22d3ee', '#a78bfa', '#f472b6', '#94a3b8'];

let lastLiveCandidates = [];
let rankingsFilters = {
    search: '',
    position: '',
    party: '',
    sort: 'position',
};
let rankingsFiltersInitialized = false;

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

function formatNumber(value) {
    return Number(value ?? 0).toLocaleString();
}

function applyVmStatusBadge(element, status, { isPaused = false, windowOpen = false, isLive = false } = {}) {
    if (!element) {
        return;
    }

    const normalized = String(status ?? '').toLowerCase();
    element.textContent = status ?? element.dataset.fallback ?? element.textContent;

    let tone = 'idle';

    if (isPaused || normalized.includes('paused')) {
        tone = 'paused';
    } else if (isLive && windowOpen && ['voting open', 'live', 'active', 'open'].some((needle) => normalized.includes(needle))) {
        tone = 'live';
    } else if (['window ended', 'not started', 'outside window', 'closed', 'completed', 'ended', 'archived', 'annulled'].some((needle) => normalized.includes(needle))) {
        tone = 'closed';
    }

    element.className = `vm-badge vm-badge--${tone}`;
}

function updateLiveStatusBanner(voting) {
    const banner = document.getElementById('live-voting-live-status');

    if (!banner) {
        return;
    }

    const isLive = Boolean(voting?.is_live);
    const isPaused = Boolean(voting?.is_paused);
    const windowOpen = Boolean(voting?.window_open);
    const status = String(voting?.election_status ?? '').toLowerCase();
    const isCompleted = ['window ended', 'closed', 'completed', 'archived'].some((needle) => status.includes(needle))
        || voting?.countdown?.phase === 'ended'
        || voting?.countdown?.is_closed;

    let state = 'idle';
    let html = '';

    if (isPaused) {
        state = 'paused';
        html = `
            <span class="vm-live-status__icon" aria-hidden="true">🟡</span>
            <div>
                <p class="font-semibold text-amber-200">PAUSED</p>
                <p class="text-sm text-amber-100/80">Voting is temporarily suspended.</p>
            </div>
        `;
    } else if (isLive && windowOpen && ['voting open', 'live', 'active', 'open'].some((needle) => status.includes(needle))) {
        state = 'live';
        html = `
            <span class="vm-live-status__icon" aria-hidden="true">🟢</span>
            <div>
                <p class="font-semibold text-emerald-200">LIVE</p>
                <p class="text-sm text-emerald-100/80">Votes are updating automatically every 5 seconds.</p>
            </div>
        `;
    } else if (isCompleted) {
        state = 'completed';
        html = `
            <span class="vm-live-status__icon" aria-hidden="true">🔵</span>
            <div>
                <p class="font-semibold text-sky-200">COMPLETED</p>
                <p class="text-sm text-sky-100/80">Official results are available.</p>
            </div>
        `;
    }

    if (!html) {
        banner.classList.add('hidden');
        banner.dataset.state = 'idle';
        return;
    }

    banner.classList.remove('hidden', 'vm-live-status--live', 'vm-live-status--paused', 'vm-live-status--completed');
    banner.classList.add(`vm-live-status--${state}`);
    banner.dataset.state = state;
    banner.innerHTML = html;
}

function updateSystemStatus(voting) {
    const systemUpdated = document.getElementById('live-voting-system-updated');
    const connectionLabel = document.getElementById('live-voting-connection-label');
    const chartUpdated = document.getElementById('live-voting-chart-updated');

    if (systemUpdated) {
        systemUpdated.textContent = voting?.updated_at
            ? `Updated ${formatUpdatedAt(voting.updated_at)}`
            : '—';
    }

    if (connectionLabel) {
        connectionLabel.textContent = 'Connected';
        connectionLabel.classList.remove('text-amber-300');
        connectionLabel.classList.add('text-emerald-300');
    }

    if (chartUpdated && voting?.updated_at) {
        chartUpdated.textContent = `Updated ${formatUpdatedAt(voting.updated_at)}`;
    }
}

function updateSystemClock() {
    const clock = document.getElementById('live-voting-system-clock');

    if (clock) {
        clock.textContent = new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
    }
}

function renderChartLegend(container, leaders) {
    if (!container) {
        return;
    }

    if (!leaders?.length) {
        container.innerHTML = '';
        return;
    }

    container.innerHTML = leaders.slice(0, 6).map((leader, index) => `
        <span class="vm-chart-legend-item">
            <span class="vm-chart-legend-swatch" style="background:${CHART_LEGEND_COLORS[index % CHART_LEGEND_COLORS.length]}"></span>
            <span>${escapeHtml(leader.candidate)}</span>
        </span>
    `).join('');
}

function filterCandidates(candidates = []) {
    let rows = [...candidates];

    if (rankingsFilters.search) {
        const query = rankingsFilters.search.toLowerCase();
        rows = rows.filter((row) => `${row.candidate} ${row.party ?? ''} ${row.position}`.toLowerCase().includes(query));
    }

    if (rankingsFilters.position) {
        rows = rows.filter((row) => row.position === rankingsFilters.position);
    }

    if (rankingsFilters.party) {
        rows = rows.filter((row) => (row.party || 'Independent') === rankingsFilters.party);
    }

    if (rankingsFilters.sort === 'votes-desc') {
        rows.sort((left, right) => (Number(right.votes) || 0) - (Number(left.votes) || 0)
            || String(left.position).localeCompare(String(right.position)));
    } else if (rankingsFilters.sort === 'votes-asc') {
        rows.sort((left, right) => (Number(left.votes) || 0) - (Number(right.votes) || 0)
            || String(left.position).localeCompare(String(right.position)));
    }

    return rows;
}

function populateRankingFilters(candidates = []) {
    const positionFilter = document.getElementById('live-voting-rankings-position-filter');
    const partyFilter = document.getElementById('live-voting-rankings-party-filter');

    if (!positionFilter || !partyFilter) {
        return;
    }

    const positions = [...new Set(candidates.map((row) => row.position).filter(Boolean))].sort();
    const parties = [...new Set(candidates.map((row) => row.party || 'Independent'))].sort();
    const currentPosition = positionFilter.value;
    const currentParty = partyFilter.value;

    positionFilter.innerHTML = '<option value="">All positions</option>'
        + positions.map((position) => `<option value="${escapeHtml(position)}">${escapeHtml(position)}</option>`).join('');
    partyFilter.innerHTML = '<option value="">All parties</option>'
        + parties.map((party) => `<option value="${escapeHtml(party)}">${escapeHtml(party)}</option>`).join('');

    positionFilter.value = currentPosition;
    partyFilter.value = currentParty;
}

function initRankingFilters() {
    if (rankingsFiltersInitialized) {
        return;
    }

    rankingsFiltersInitialized = true;

    document.getElementById('live-voting-rankings-search')?.addEventListener('input', (event) => {
        rankingsFilters.search = event.target.value.trim().toLowerCase();
        renderLeadingCandidates(document.getElementById('live-voting-leaders'), lastLiveCandidates);
    });

    document.getElementById('live-voting-rankings-position-filter')?.addEventListener('change', (event) => {
        rankingsFilters.position = event.target.value;
        renderLeadingCandidates(document.getElementById('live-voting-leaders'), lastLiveCandidates);
    });

    document.getElementById('live-voting-rankings-party-filter')?.addEventListener('change', (event) => {
        rankingsFilters.party = event.target.value;
        renderLeadingCandidates(document.getElementById('live-voting-leaders'), lastLiveCandidates);
    });

    document.getElementById('live-voting-rankings-sort')?.addEventListener('change', (event) => {
        rankingsFilters.sort = event.target.value;
        renderLeadingCandidates(document.getElementById('live-voting-leaders'), lastLiveCandidates);
    });
}

function statusBadgeClass(status) {
    const normalized = String(status ?? '').toLowerCase();

    if (['pending', 'open', 'entries_open', 'scheduled'].includes(normalized)) {
        return 'inline-flex rounded-full bg-amber-500/20 px-2.5 py-0.5 text-[10px] font-semibold uppercase text-amber-300';
    }

    if (['approved', 'verified', 'active', 'voting_open', 'resolved', 'success', 'published'].includes(normalized)) {
        return 'inline-flex rounded-full bg-emerald-500/20 px-2.5 py-0.5 text-[10px] font-semibold uppercase text-emerald-300';
    }

    if (['completed', 'results_published'].includes(normalized)) {
        return 'inline-flex rounded-full bg-violet-500/20 px-2.5 py-0.5 text-[10px] font-semibold uppercase text-violet-300';
    }

    if (['archived', 'inactive', 'cancelled'].includes(normalized)) {
        return 'inline-flex rounded-full bg-slate-600/40 px-2.5 py-0.5 text-[10px] font-semibold uppercase text-slate-400';
    }

    return 'inline-flex rounded-full bg-slate-700 px-2.5 py-0.5 text-[10px] font-semibold uppercase text-slate-300';
}

function statusLabel(status, label) {
    if (label) {
        return label;
    }

    return String(status ?? '')
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase());
}

function votingStatusBadgeClass(status, { isPaused = false, windowOpen = false, isLive = false } = {}) {
    const normalized = String(status ?? '').toLowerCase();

    if (isPaused || normalized === 'paused') {
        return 'inline-flex rounded-full bg-amber-500/20 px-2.5 py-0.5 text-[10px] font-semibold uppercase text-amber-300';
    }

    if (
        isLive
        && windowOpen
        && ['voting open', 'live', 'active'].includes(normalized)
    ) {
        return 'inline-flex rounded-full bg-emerald-500/20 px-2.5 py-0.5 text-[10px] font-semibold uppercase text-emerald-300';
    }

    if (['window ended', 'not started', 'outside window', 'annulled', 'inactive', 'closed'].includes(normalized)) {
        return 'inline-flex rounded-full bg-rose-500/20 px-2.5 py-0.5 text-[10px] font-semibold uppercase text-rose-300';
    }

    if (!isLive) {
        return 'inline-flex rounded-full bg-slate-700 px-2.5 py-0.5 text-[10px] font-semibold uppercase text-slate-300';
    }

    return 'inline-flex rounded-full bg-violet-500/20 px-2.5 py-0.5 text-[10px] font-semibold uppercase text-violet-300';
}

function formatCountdownFromIso(isoString) {
    if (!isoString) {
        return '—';
    }

    const target = new Date(isoString).getTime();

    if (Number.isNaN(target)) {
        return '—';
    }

    const diff = target - Date.now();

    if (diff <= 0) {
        return '00 Hours 00 Minutes';
    }

    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

    return `${String(hours).padStart(2, '0')} Hours ${String(minutes).padStart(2, '0')} Minutes`;
}

function animateCounter(element, nextValue, { suffix = '', decimals = 0 } = {}) {
    if (!element) {
        return;
    }

    const target = Number(nextValue) || 0;
    const current = Number(element.dataset.value ?? 0) || 0;

    if (current === target && element.textContent) {
        return;
    }

    const start = performance.now();
    const duration = 500;

    const frame = (now) => {
        const progress = Math.min(1, (now - start) / duration);
        const eased = 1 - (1 - progress) ** 3;
        const value = current + ((target - current) * eased);

        element.textContent = `${value.toFixed(decimals)}${suffix}`;
        element.dataset.value = String(target);

        if (progress < 1) {
            requestAnimationFrame(frame);
        }
    };

    requestAnimationFrame(frame);
}

function chartEmptyState(title, subtitle) {
    return `
        <div class="vm-empty-state h-full min-h-[280px]">
            <svg class="vm-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <p class="text-sm font-medium text-white">${escapeHtml(title)}</p>
            <p class="mt-2 max-w-sm text-xs text-slate-500">${escapeHtml(subtitle)}</p>
        </div>
    `;
}

function renderVoteDistributionChart(container, leaders, totalVotes = 0) {
    if (!container) {
        return;
    }

    const hasVotes = Number(totalVotes) > 0 && leaders?.length;

    if (!hasVotes) {
        container.innerHTML = chartEmptyState(
            'No votes yet.',
            'The chart will appear automatically once students begin casting ballots.',
        );
        renderChartLegend(document.getElementById('live-voting-chart-legend'), []);
        return;
    }

    const maxVotes = Math.max(...leaders.map((item) => Number(item.votes) || 0), 1);
    const rows = leaders.slice(0, 8).map((leader, index) => {
        const votes = Number(leader.votes) || 0;
        const percent = Number(leader.percent ?? 0);
        const width = Math.max(4, Math.round((votes / maxVotes) * 100));

        return `
            <div class="vm-slide-up space-y-2" style="animation-delay:${index * 0.04}s">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-white">${escapeHtml(leader.candidate)}</p>
                        <p class="truncate text-[11px] text-slate-500">${escapeHtml(leader.position)}</p>
                    </div>
                    <div class="shrink-0 text-right">
                        <p class="text-sm font-semibold text-white">${formatNumber(votes)}</p>
                        <p class="text-[10px] text-slate-500">${percent.toFixed(1)}%</p>
                    </div>
                </div>
                <div class="vm-progress h-1.5">
                    <span class="vm-chart-bar" style="width:${width}%"></span>
                </div>
            </div>
        `;
    }).join('');

    container.innerHTML = `<div class="flex h-full min-h-[280px] flex-col justify-center space-y-5">${rows}</div>`;
    renderChartLegend(document.getElementById('live-voting-chart-legend'), leaders.slice(0, 8));
}

function renderRecentActivity(container, activity) {
    if (!container) {
        return;
    }

    if (!activity?.length) {
        container.innerHTML = `
            <div class="vm-empty-state min-h-[280px] rounded-xl border border-dashed border-white/[0.1] bg-white/[0.02] px-4 py-8">
                <div class="mb-3 text-2xl" aria-hidden="true">🕒</div>
                <p class="text-sm font-medium text-slate-300">No activity yet</p>
                <p class="mt-1 max-w-xs text-xs text-slate-500">Ballots and administrator actions will appear here in real time.</p>
            </div>
        `;
        return;
    }

    container.innerHTML = activity.map((item, index) => `
        <div class="vm-timeline-item" style="animation-delay:${index * 0.05}s">
            <span class="vm-timeline-item__icon" aria-hidden="true">🗳</span>
            <p class="text-[10px] font-medium text-violet-300">${escapeHtml(item.time_display ?? '—')}</p>
            <p class="mt-0.5 text-xs text-slate-300">
                <span class="font-semibold text-white">Student ${escapeHtml(item.student)}</span>
                <span class="text-slate-500"> ${escapeHtml(item.label ?? 'voted')}</span>
            </p>
        </div>
    `).join('');
}

function leadersPerPosition(candidates) {
    const seen = new Set();

    return (candidates ?? []).filter((row) => {
        const key = String(row.position ?? '');

        if (seen.has(key)) {
            return false;
        }

        seen.add(key);
        return true;
    });
}

function groupCandidatesByPosition(candidates) {
    const groups = [];
    const indexByPosition = new Map();

    (candidates ?? []).forEach((candidate) => {
        const key = String(candidate.position ?? 'Position');

        if (!indexByPosition.has(key)) {
            const group = { position: key, candidates: [] };
            indexByPosition.set(key, groups.length);
            groups.push(group);
        }

        groups[indexByPosition.get(key)].candidates.push(candidate);
    });

    return groups;
}

function buildCandidateCard(leader, positionPeers, index = 0) {
    const isLeader = Boolean(leader.is_leader);
    const positionMaxVotes = Math.max(...positionPeers.map((row) => Number(row.votes) || 0), 0);
    const showLeading = isLeader && positionMaxVotes > 0;
    const showOpposition = !isLeader && positionMaxVotes > 0;
    const percent = Number(leader.percent ?? 0);
    const avatar = leader.photo_url
        ? `<img src="${escapeHtml(leader.photo_url)}" alt="" class="h-9 w-9 shrink-0 rounded-full object-cover ring-1 ring-violet-500/30">`
        : `<div class="vm-avatar h-9 w-9 shrink-0 text-[11px]">${escapeHtml(initials(leader.candidate))}</div>`;
    const leaderBadge = showLeading
        ? '<span class="shrink-0 rounded-full bg-emerald-500/15 px-2 py-0.5 text-[10px] font-semibold text-emerald-300">Leading</span>'
        : (showOpposition
            ? '<span class="shrink-0 rounded-full bg-slate-700/60 px-2 py-0.5 text-[10px] font-semibold text-slate-400">Opposition</span>'
            : '');
    const partyLabel = leader.party ? escapeHtml(leader.party) : 'Independent';

    const card = document.createElement('article');
    card.className = `vm-card vm-slide-up flex flex-col rounded-xl border bg-slate-950/60 p-3 ${
        showLeading ? 'vm-candidate-card--winner border-violet-500/25' : 'border-slate-800'
    }`;
    card.style.animationDelay = `${index * 0.05}s`;
    card.innerHTML = `
        <div class="flex items-start justify-between gap-2">
            <div class="flex min-w-0 items-center gap-2.5">
                ${avatar}
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-white">${escapeHtml(leader.candidate)}</p>
                    <p class="truncate text-[11px] text-slate-500">${partyLabel}</p>
                </div>
            </div>
            ${leaderBadge}
        </div>
        <div class="mt-2.5">
            <div class="mb-1 flex justify-between text-[10px] text-slate-500">
                <span>${formatNumber(leader.votes)} votes</span>
                <span>${percent.toFixed(1)}%</span>
            </div>
            <div class="vm-progress h-1.5">
                <span style="width:${Math.min(100, percent)}%"></span>
            </div>
        </div>
    `;

    return card;
}

function renderLeadingCandidates(container, leaders) {
    if (!container) {
        return;
    }

    container.innerHTML = '';
    lastLiveCandidates = leaders ?? [];
    populateRankingFilters(lastLiveCandidates);
    initRankingFilters();

    const filtered = filterCandidates(lastLiveCandidates);

    if (!filtered.length) {
        container.innerHTML = `
            <div class="vm-card flex min-h-[12rem] flex-col items-center justify-center p-6 text-center" style="grid-column:1 / -1">
                <div class="mb-3 text-2xl" aria-hidden="true">👤</div>
                <p class="text-sm font-medium text-slate-300">No candidates found</p>
                <p class="mt-1 max-w-sm text-xs text-slate-500">${lastLiveCandidates.length ? 'Try adjusting your search or filters.' : 'Position leaders will appear here as ballots are cast.'}</p>
            </div>
        `;
        return;
    }

    const groups = groupCandidatesByPosition(filtered);

    groups.forEach((group, groupIndex) => {
        const section = document.createElement('section');
        section.className = 'vm-position-group vm-slide-up';
        section.style.animationDelay = `${groupIndex * 0.06}s`;
        section.setAttribute('aria-label', `${group.position} candidates`);

        const heading = document.createElement('p');
        heading.className = 'text-[10px] font-semibold uppercase tracking-wide text-slate-500';
        heading.textContent = group.position;

        const stack = document.createElement('div');
        stack.className = 'vm-position-stack';

        group.candidates.forEach((leader, index) => {
            stack.appendChild(buildCandidateCard(leader, group.candidates, index));
        });

        section.appendChild(heading);
        section.appendChild(stack);
        container.appendChild(section);
    });
}

function leadingCandidateForParty(candidates, party) {
    const keys = [party.acronym, party.name]
        .filter(Boolean)
        .map((value) => String(value).trim().toLowerCase());

    return (candidates ?? [])
        .filter((row) => keys.includes(String(row.party ?? '').trim().toLowerCase()))
        .sort((left, right) => (Number(right.votes) || 0) - (Number(left.votes) || 0))[0];
}

function renderPartylistComparison(container, partylists, candidates = []) {
    if (!container) {
        return;
    }

    container.innerHTML = '';

    if (!partylists?.length) {
        container.innerHTML = `
            <div class="vm-card col-span-full flex h-full min-h-[12rem] flex-col items-center justify-center p-6 text-center md:col-span-2 xl:col-span-3">
                <div class="mb-3 text-2xl" aria-hidden="true">🏛</div>
                <p class="text-sm font-medium text-slate-300">No party data</p>
                <p class="mt-1 max-w-sm text-xs text-slate-500">Registered partylists will appear here for live comparison.</p>
            </div>
        `;
        return;
    }

    partylists.forEach((party, index) => {
        const card = document.createElement('article');
        card.className = 'vm-card vm-slide-up flex h-full flex-col rounded-xl border border-slate-800 bg-slate-950/60 p-4';
        card.style.animationDelay = `${index * 0.05}s`;
        const percent = Number(party.percent ?? 0);
        const label = party.acronym || party.name;
        const leader = leadingCandidateForParty(candidates, party);
        const logo = party.logo_url
            ? `<img src="${escapeHtml(party.logo_url)}" alt="" class="h-10 w-10 shrink-0 rounded-lg object-cover ring-1 ring-slate-700">`
            : `<div class="vm-avatar h-10 w-10 shrink-0 rounded-lg text-[10px]">${escapeHtml((party.acronym ?? party.name ?? '?').slice(0, 2).toUpperCase())}</div>`;

        card.innerHTML = `
            <div class="flex flex-1 items-start justify-between gap-2">
                <div class="flex min-w-0 items-start gap-2.5">
                    ${logo}
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-white">${escapeHtml(party.name)}</p>
                        ${party.acronym ? `<p class="text-[11px] text-violet-300">${escapeHtml(party.acronym)}</p>` : ''}
                    </div>
                </div>
                <span class="shrink-0 rounded-full bg-violet-500/15 px-2 py-0.5 text-[10px] font-semibold text-violet-200">${formatNumber(party.seats_won)} seat${Number(party.seats_won) === 1 ? '' : 's'}</span>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-2 text-[10px] text-slate-500">
                <div>
                    <p>Total Votes</p>
                    <p class="mt-0.5 text-sm font-semibold text-white">${formatNumber(party.total_votes)}</p>
                </div>
                <div>
                    <p>Vote Share</p>
                    <p class="mt-0.5 text-sm font-semibold text-emerald-300">${percent.toFixed(1)}%</p>
                </div>
            </div>
            <div class="mt-3">
                <div class="vm-progress h-2">
                    <span style="width:${Math.min(100, percent)}%"></span>
                </div>
            </div>
            <p class="mt-3 text-[11px] text-slate-400">Leading candidate: <span class="font-semibold text-slate-200">${escapeHtml(leader?.candidate ?? '—')}</span></p>
        `;
        card.title = `${label}: ${party.total_votes} votes, ${percent}%`;
        container.appendChild(card);
    });
}

function countdownHintForPhase(phase) {
    if (phase === 'before_start') {
        return 'Until voting window opens';
    }

    if (phase === 'active') {
        return 'Until voting window closes';
    }

    return 'Voting window closed';
}

function resolveCountdown(voting) {
    if (voting?.countdown?.target_at_iso || voting?.countdown?.remaining) {
        return voting.countdown;
    }

    const starts = voting?.voting_starts_at;
    const ends = voting?.voting_ends_at;
    const now = Date.now();

    if (starts && new Date(starts).getTime() > now) {
        return {
            label: 'Voting Starts In',
            remaining: formatCountdownFromIso(starts),
            phase: 'before_start',
            target_at_iso: starts,
        };
    }

    if (ends && new Date(ends).getTime() > now) {
        return {
            label: 'Voting Ends In',
            remaining: formatCountdownFromIso(ends),
            phase: 'active',
            target_at_iso: ends,
        };
    }

    if (ends) {
        return {
            label: 'Voting Closed',
            remaining: '00 Hours 00 Minutes',
            phase: 'ended',
            target_at_iso: ends,
            is_closed: true,
        };
    }

    return null;
}

function updateLiveCountdown(voting) {
    const countdown = resolveCountdown(voting);
    const formatted = countdown?.remaining
        ?? (countdown?.target_at_iso ? formatCountdownFromIso(countdown.target_at_iso) : '—');

    const labelEl = document.getElementById('live-voting-countdown-label');
    if (labelEl && countdown?.label) {
        labelEl.textContent = countdown.label;
    }

    ['live-voting-countdown', 'live-voting-stat-countdown'].forEach((id) => {
        const el = document.getElementById(id);
        if (el) {
            el.textContent = formatted;
        }
    });

    const hintEl = document.getElementById('live-voting-stat-countdown-hint');
    if (hintEl && countdown?.phase) {
        hintEl.textContent = countdownHintForPhase(countdown.phase);
    }

    const panel = document.getElementById('live-voting-panel');
    if (panel && countdown?.phase) {
        panel.dataset.countdownPhase = countdown.phase;
    }

    if (panel && voting?.voting_starts_at) {
        panel.dataset.votingStarts = voting.voting_starts_at;
    }

    if (panel && voting?.voting_ends_at) {
        panel.dataset.votingEnds = voting.voting_ends_at;
    }

    if (panel && countdown?.starts_at_iso) {
        panel.dataset.votingStarts = countdown.starts_at_iso;
    }

    if (panel && countdown?.ends_at_iso) {
        panel.dataset.votingEnds = countdown.ends_at_iso;
    }
}

function setLiveIndicators(isLive, windowOpen = false) {
    const pulse = document.getElementById('live-voting-pulse');
    const showPulse = isLive && windowOpen;

    pulse?.classList.toggle('hidden', !showPulse);
    pulse?.classList.toggle('inline-flex', showPulse);
}

function initials(name) {
    return String(name ?? '')
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() ?? '')
        .join('') || '?';
}

function renderChartSvg(container, config) {
    const canvas = container?.querySelector('[data-live-chart-canvas]');

    if (!canvas || !config) {
        return;
    }

    const labels = [...(config.labels ?? [])];
    const values = [...(config.values ?? [])].map((value) => Number(value) || 0);
    const count = Math.max(labels.length, values.length, 1);
    const type = container.dataset.chartType ?? 'line';
    const accent = container.dataset.chartAccent ?? '#818cf8';
    const yMax = Math.max(Number(config.yMax) || 100, 1);
    const yTicks = [...(config.yTicks ?? [0, yMax / 2, yMax])].map((tick) => Number(tick) || 0);
    const valuePrefix = config.valuePrefix ?? '';
    const valueSuffix = config.valueSuffix ?? '';

    while (values.length < count) {
        values.push(0);
    }

    while (labels.length < count) {
        labels.push('');
    }

    const plotW = 280;
    const plotH = 120;
    const padL = 44;
    const padB = 30;
    const padT = 10;
    const padR = 10;
    const width = padL + plotW + padR;
    const height = padT + plotH + padB;

    const toX = (index) => (count <= 1 ? padL + plotW / 2 : padL + (index / (count - 1)) * plotW);
    const toY = (value) => padT + plotH - (Math.min(value, yMax) / yMax) * plotH;
    const formatTick = (tick) => {
        const formatted = Number.isInteger(tick) ? String(tick) : tick.toFixed(1);
        return `${valuePrefix}${formatted}${valueSuffix}`;
    };

    let body = '';

    yTicks.forEach((tick) => {
        const y = toY(tick);
        body += `<line x1="${padL}" y1="${y.toFixed(1)}" x2="${padL + plotW}" y2="${y.toFixed(1)}" stroke="#334155" stroke-width="1" stroke-dasharray="3 4" />`;
        body += `<text x="${padL - 6}" y="${(y + 3).toFixed(1)}" text-anchor="end" fill="#94a3b8" font-size="9" font-family="ui-sans-serif, system-ui, sans-serif">${escapeHtml(formatTick(tick))}</text>`;
    });

    if (type === 'line') {
        const points = values.map((value, index) => `${toX(index).toFixed(1)},${toY(value).toFixed(1)}`);
        body += `<path d="M ${points.join(' L ')}" fill="none" stroke="${accent}" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />`;
        values.forEach((value, index) => {
            body += `<circle cx="${toX(index).toFixed(1)}" cy="${toY(value).toFixed(1)}" r="3.5" fill="${accent}" stroke="#0f172a" stroke-width="1.5" />`;
        });
    } else if (type === 'bar') {
        const slotWidth = plotW / count;
        values.forEach((value, index) => {
            const barW = slotWidth * 0.55;
            const x = padL + index * slotWidth + (slotWidth - barW) / 2;
            const barH = (value / yMax) * plotH;
            const y = padT + plotH - barH;
            const opacity = Math.min(1, 0.45 + (index % 5) * 0.12);
            body += `<rect x="${x.toFixed(1)}" y="${y.toFixed(1)}" width="${barW.toFixed(1)}" height="${barH.toFixed(1)}" rx="2" fill="${accent}" opacity="${opacity}" />`;
        });
    } else if (type === 'horizontal-bar') {
        const rowH = plotH / Math.max(count, 1);
        const barTrack = plotW * 0.72;

        values.forEach((value, index) => {
            const label = labels[index] ?? '';
            const barW = (value / yMax) * barTrack;
            const y = padT + index * rowH + rowH * 0.22;
            const barHeight = rowH * 0.56;
            const shortLabel = label.length > 14 ? `${label.slice(0, 14)}…` : label;
            body += `<text x="${padL - 4}" y="${(y + barHeight / 2 + 3).toFixed(1)}" text-anchor="end" fill="#94a3b8" font-size="8" font-family="ui-sans-serif, system-ui, sans-serif">${escapeHtml(shortLabel)}</text>`;
            body += `<rect x="${padL}" y="${y.toFixed(1)}" width="${barW.toFixed(1)}" height="${barHeight.toFixed(1)}" rx="2" fill="${accent}" opacity="0.85" />`;
        });
    }

    if (type !== 'horizontal-bar') {
        labels.forEach((label, index) => {
            body += `<text x="${toX(index).toFixed(1)}" y="${padT + plotH + 18}" text-anchor="middle" fill="#94a3b8" font-size="9" font-family="ui-sans-serif, system-ui, sans-serif">${escapeHtml(label)}</text>`;
        });
    }

    body += `<line x1="${padL}" y1="${padT + plotH}" x2="${padL + plotW}" y2="${padT + plotH}" stroke="#475569" stroke-width="1" />`;
    body += `<line x1="${padL}" y1="${padT}" x2="${padL}" y2="${padT + plotH}" stroke="#475569" stroke-width="1" />`;

    const emptyMessage = container.dataset.emptyMessage;
    const hasData = values.some((value) => value > 0);

    if (emptyMessage && !hasData) {
        const cx = padL + plotW / 2;
        const cy = padT + plotH / 2 + 3;
        body += `<text x="${cx}" y="${cy}" text-anchor="middle" fill="#64748b" font-size="11" font-family="ui-sans-serif, system-ui, sans-serif" data-live-chart-empty>${escapeHtml(emptyMessage)}</text>`;
    }

    canvas.setAttribute('viewBox', `0 0 ${width} ${height}`);
    canvas.innerHTML = body;
}

function buildSparklinePath(values, width = 80, height = 24) {
    const series = [...values].map((value) => Number(value) || 0);

    if (series.length === 0) {
        return `M0 ${height / 2} L${width} ${height / 2}`;
    }

    const max = Math.max(...series);
    const min = Math.min(...series);
    const range = Math.max(max - min, 0.001);
    const count = series.length;

    return series
        .map((value, index) => {
            const x = count <= 1 ? width / 2 : (index / (count - 1)) * width;
            const y = height - ((value - min) / range) * (height - 4) - 2;

            return `${x.toFixed(1)},${y.toFixed(1)}`;
        })
        .reduce((path, point, index) => (index === 0 ? `M ${point}` : `${path} L ${point}`), '');
}

function buildBarHeights(values, maxHeight = 24) {
    const series = [...values].map((value) => Number(value) || 0);
    const peak = Math.max(...series, 1);

    return series.map((value) => Number(((value / peak) * maxHeight).toFixed(1)));
}

function buildDonutDash(percent) {
    const circumference = 2 * Math.PI * 8;
    const filled = (Math.max(0, Math.min(100, percent)) / 100) * circumference;

    return `${filled.toFixed(2)} ${circumference.toFixed(2)}`;
}

function renderStatSparkline(container, config, stroke) {
    const canvas = container?.querySelector('[data-sparkline-canvas]');

    if (!canvas || !config) {
        return;
    }

    const type = config.type ?? 'line';
    const values = [...(config.values ?? [])].map((value) => Number(value) || 0);
    const percent = Number(config.percent ?? 0);

    if (type === 'line') {
        canvas.setAttribute('viewBox', '0 0 80 24');
        canvas.innerHTML = `<path d="${buildSparklinePath(values)}" fill="none" stroke="${stroke}" stroke-width="2" stroke-linecap="round" />`;
        return;
    }

    if (type === 'bars') {
        const heights = buildBarHeights(values);
        const slotWidth = 80 / Math.max(heights.length, 1);
        let body = '';

        heights.forEach((barHeight, index) => {
            const barW = 8;
            const x = 4 + index * slotWidth;
            const y = 24 - barHeight;
            const opacity = barHeight > 0 ? Math.min(1, 0.45 + (index % 5) * 0.12) : 0.25;
            body += `<rect x="${x.toFixed(1)}" y="${y.toFixed(1)}" width="${barW}" height="${Math.max(barHeight, 1)}" rx="1.5" fill="${stroke}" opacity="${opacity}" />`;
        });

        canvas.setAttribute('viewBox', '0 0 80 24');
        canvas.innerHTML = body;
        return;
    }

    const dash = buildDonutDash(percent);
    canvas.setAttribute('viewBox', '0 0 36 24');
    canvas.innerHTML = `<circle cx="12" cy="12" r="8" fill="none" stroke="#1e293b" stroke-width="3" />${
        percent > 0
            ? `<circle cx="12" cy="12" r="8" fill="none" stroke="${stroke}" stroke-width="3" stroke-dasharray="${dash}" stroke-linecap="round" transform="rotate(-90 12 12)" />`
            : ''
    }`;
}

function applyStatSparklines(sparklines) {
    if (!sparklines) {
        return;
    }

    document.querySelectorAll('[data-live-sparkline]').forEach((container) => {
        const key = container.dataset.liveSparkline;
        const config = sparklines[key];
        const stroke = container.dataset.sparklineStroke ?? '#a78bfa';

        renderStatSparkline(container, config, stroke);
    });
}

function applyStats(stats) {
    const map = {
        turnout_percent: `${Number(stats?.turnout_percent ?? 0).toFixed(1)}%`,
        votes_cast: formatNumber(stats?.votes_cast),
        eligible_voters: formatNumber(stats?.eligible_voters),
        not_voted: formatNumber(stats?.not_voted),
        partylists: formatNumber(stats?.partylists),
        candidates: formatNumber(stats?.candidates),
    };

    Object.entries(map).forEach(([key, value]) => {
        const el = document.querySelector(`[data-live-stat="${key}"]`);
        if (el) {
            el.textContent = value;
        }
    });

    document.querySelectorAll('[data-live-election-status]').forEach((el) => {
        const status = stats?.election_status ?? el.dataset.fallback ?? el.textContent;
        el.textContent = status;
        el.className = statusBadgeClass(status);
    });

    const fundraiserBadge = document.querySelector('[data-live-fundraiser-badge]');
    if (fundraiserBadge) {
        if (Number(stats?.active_fundraisers) > 0) {
            fundraiserBadge.textContent = `${stats.active_fundraisers} active`;
            fundraiserBadge.classList.remove('hidden');
        } else {
            fundraiserBadge.classList.add('hidden');
        }
    }
}

function applyVoterBreakdown(breakdown) {
    if (!breakdown) {
        return;
    }

    Object.entries({
        eligible: breakdown.eligible,
        voted: breakdown.voted,
        notVoted: breakdown.notVoted,
        ineligible: breakdown.ineligible,
    }).forEach(([key, value]) => {
        const el = document.querySelector(`[data-live-voter="${key}"]`);
        if (el) {
            el.textContent = formatNumber(value);
        }
    });
}

function applyAnalyticsCharts(analytics) {
    if (!analytics) {
        return;
    }

    document.querySelectorAll('[data-live-chart]').forEach((container) => {
        const key = container.dataset.liveChart;
        if (analytics[key]) {
            renderChartSvg(container, analytics[key]);
        }
    });
}

function applyEventsPreview(events) {
    const tbody = document.getElementById('dashboard-events-tbody');

    if (!tbody) {
        return;
    }

    if (!events?.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="px-2 py-8 text-center text-slate-500">No events yet. Create a talent competition or school event to populate this table.</td></tr>';
        return;
    }

    tbody.innerHTML = events.map((event) => {
        const image = event.image_url
            ? `<img src="${escapeHtml(event.image_url)}" alt="" class="h-9 w-9 shrink-0 rounded-lg object-cover ring-1 ring-slate-700">`
            : '<div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-violet-500/15 text-[10px] font-bold text-violet-300">EV</div>';

        const deleteForm = event.can_delete
            ? `<form method="POST" action="${escapeHtml(event.delete_url)}" class="inline" onsubmit="return confirm('Delete this event? This cannot be undone.')">
                    <input type="hidden" name="_token" value="${escapeHtml(document.querySelector('meta[name=csrf-token]')?.content ?? '')}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="text-xs font-semibold text-rose-300 hover:text-rose-200">Delete</button>
               </form>`
            : '';

        return `
            <tr class="text-slate-200">
                <td class="px-2 py-3">
                    <div class="flex items-center gap-2.5">
                        ${image}
                        <span class="line-clamp-1 font-medium text-white">${escapeHtml(event.title)}</span>
                    </div>
                </td>
                <td class="px-2 py-3 text-slate-400">${escapeHtml(event.category)}</td>
                <td class="px-2 py-3 text-slate-400">${escapeHtml(event.schedule)}</td>
                <td class="px-2 py-3">
                    <span class="${statusBadgeClass(event.status)}">${escapeHtml(statusLabel(event.status, event.status_label))}</span>
                </td>
                <td class="px-2 py-3 text-right">
                    <div class="inline-flex items-center gap-3">
                        <a href="${escapeHtml(event.edit_url)}" class="text-xs font-semibold text-violet-300 hover:text-violet-200">Edit</a>
                        ${deleteForm}
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function applyFundraisers(fundraisers) {
    const grid = document.getElementById('dashboard-fundraisers-grid');

    if (!grid) {
        return;
    }

    if (!fundraisers?.length) {
        return;
    }

    fundraisers.forEach((item) => {
        const card = grid.querySelector(`[data-fundraiser-id="${item.id}"]`);

        if (!card) {
            return;
        }

        const raised = card.querySelector('[data-live-fundraiser-raised]');
        const donations = card.querySelector('[data-live-fundraiser-donations]');
        const progress = card.querySelector('[data-live-fundraiser-progress]');
        const status = card.querySelector('[data-live-fundraiser-status]');

        if (raised) {
            raised.textContent = `Raised ₱${Number(item.amount_raised).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        }

        if (donations) {
            donations.textContent = `${formatNumber(item.donations_count)} donation(s)`;
        }

        if (progress) {
            progress.style.width = `${Math.min(100, Number(item.progress_percent) || 0)}%`;
        }

        if (status) {
            status.textContent = statusLabel(item.status);
            status.className = statusBadgeClass(item.status);
        }
    });
}

function renderDistributionBars(container, leaders) {
    renderVoteDistributionChart(
        document.getElementById('live-voting-chart'),
        leaders,
        leaders?.reduce((sum, item) => sum + (Number(item.votes) || 0), 0) ?? 0,
    );
}

function applyLiveVotingIdle(voting) {
    const idle = document.getElementById('live-voting-idle');
    const active = document.getElementById('live-voting-active');
    const title = document.getElementById('live-voting-idle-title');
    const message = document.getElementById('live-voting-idle-message');
    const electionTitle = document.getElementById('live-voting-election-title');

    active?.classList.add('hidden');
    idle?.classList.remove('hidden');

    const titles = {
        no_election: 'No election assigned',
        not_active: 'Election is not active',
        annulled: 'Election annulled',
    };

    if (title) {
        title.textContent = titles[voting?.reason] ?? 'No Votes Yet';
    }

    if (message) {
        const hints = {
            no_election: 'Assign an election to your admin account to monitor voting here.',
            not_active: 'Set the election status to Active to unlock live statistics.',
            annulled: 'This election has been annulled. Assign a different election to continue monitoring.',
        };

        message.textContent = hints[voting?.reason]
            ?? 'Voting has not started. Once students begin voting, live statistics will automatically appear.';
    }

    if (electionTitle && voting?.election_title) {
        electionTitle.textContent = voting.election_title;
    }

    const status = document.getElementById('live-voting-status');
    if (status) {
        const label = voting?.election_status ?? status.dataset.fallback ?? status.textContent;
        applyVmStatusBadge(status, label, { isPaused: Boolean(voting?.is_paused), isLive: false });
    }

    updateLiveStatusBanner(voting);
    updateLiveCountdown(voting);
    updateSystemStatus(voting);
    setLiveIndicators(false);
}

function applyLiveVoting(voting) {
    const isLive = Boolean(voting?.is_live);
    const windowOpen = Boolean(voting?.window_open);
    const isPaused = Boolean(voting?.is_paused);
    const idle = document.getElementById('live-voting-idle');
    const active = document.getElementById('live-voting-active');

    if (!isLive) {
        applyLiveVotingIdle(voting);
        return;
    }

    idle?.classList.add('hidden');
    active?.classList.remove('hidden');
    setLiveIndicators(true, windowOpen && !isPaused);

    const status = document.getElementById('live-voting-status');
    if (status) {
        const label = voting.election_status ?? 'Voting Open';
        applyVmStatusBadge(status, label, { isPaused, windowOpen, isLive });
    }

    const totalVotes = document.getElementById('live-voting-total-votes');
    const turnout = document.getElementById('live-voting-turnout');
    const turnoutDetail = document.getElementById('live-voting-turnout-detail');
    const registered = document.getElementById('live-voting-registered');
    const title = document.getElementById('live-voting-election-title');
    const updatedAt = document.getElementById('live-voting-updated-at');
    const leaders = document.getElementById('live-voting-leaders');
    const partylists = document.getElementById('live-voting-partylists');
    const chart = document.getElementById('live-voting-chart');
    const activity = document.getElementById('live-voting-activity');

    animateCounter(totalVotes, voting.total_votes ?? 0);
    animateCounter(turnout, voting.turnout_percent ?? 0, { suffix: '%', decimals: 1 });
    animateCounter(registered, voting.registered_students ?? voting.eligible_voters ?? 0);

    if (turnoutDetail) {
        turnoutDetail.textContent = `${formatNumber(voting.unique_voters)} / ${formatNumber(voting.eligible_voters)} Students`;
    }

    if (title && voting.election_title) {
        title.textContent = voting.election_title;
    }

    if (updatedAt) {
        updatedAt.textContent = voting.updated_at ? `Updated ${formatUpdatedAt(voting.updated_at)}` : '';
    }

    updateLiveStatusBanner(voting);
    updateSystemStatus(voting);
    updateLiveCountdown(voting);
    const allCandidates = voting.leading_candidates ?? [];
    const chartLeaders = leadersPerPosition(allCandidates);
    renderVoteDistributionChart(chart, chartLeaders, voting.total_votes ?? 0);
    renderRecentActivity(activity, voting.recent_activity ?? []);
    renderLeadingCandidates(leaders, allCandidates);
    renderPartylistComparison(partylists, voting.partylist_comparison ?? [], allCandidates);
}

function applyLiveSnapshot(data) {
    applyStats(data?.stats);
    applyStatSparklines(data?.stats_sparklines);
    applyVoterBreakdown(data?.voter_breakdown);
    applyAnalyticsCharts(data?.analytics);
    applyEventsPreview(data?.events_preview);
    applyFundraisers(data?.fundraisers);
    applyLiveVoting(data?.voting);

    const stamp = document.getElementById('dashboard-live-updated');
    if (stamp && data?.updated_at) {
        stamp.textContent = `Live · ${formatUpdatedAt(data.updated_at)}`;
    }
}

function initLiveVotingCountdown() {
    const panel = document.getElementById('live-voting-panel');

    if (!panel) {
        return;
    }

    const tick = () => {
        updateLiveCountdown({
            voting_starts_at: panel.dataset.votingStarts || null,
            voting_ends_at: panel.dataset.votingEnds || null,
            countdown: {
                phase: panel.dataset.countdownPhase || 'before_start',
            },
        });
    };

    tick();
    window.setInterval(tick, 30_000);
}

function initDashboardLive() {
    const root = document.getElementById('admin-dashboard-live');
    const endpoint = root?.dataset.liveUrl;

    if (!root || !endpoint) {
        return;
    }

    initLiveVotingCountdown();
    initRankingFilters();
    updateSystemClock();
    window.setInterval(updateSystemClock, 30_000);

    const fetchSnapshot = async () => {
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

            applyLiveSnapshot(await response.json());
        } catch {
            // Keep last rendered snapshot on transient network errors.
        }
    };

    fetchSnapshot();
    window.setInterval(fetchSnapshot, LIVE_INTERVAL_MS);
}

document.addEventListener('DOMContentLoaded', initDashboardLive);

export {
    applyLiveSnapshot,
    renderChartSvg,
    escapeHtml,
    formatNumber,
    LIVE_INTERVAL_MS,
};

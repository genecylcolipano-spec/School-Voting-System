/**
 * Live voting progress polling for the admin dashboard (CampusHub-style voting card).
 */

const LIVE_VOTING_INTERVAL_MS = 5000;

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

    return `Updated ${date.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', second: '2-digit' })}`;
}

function statusTone(status) {
    const normalized = String(status ?? '').toLowerCase();

    if (normalized === 'paused') {
        return 'inline-flex rounded-full bg-amber-500/20 px-2.5 py-0.5 text-[10px] font-semibold uppercase text-amber-300';
    }

    if (normalized === 'voting open') {
        return 'inline-flex rounded-full bg-emerald-500/20 px-2.5 py-0.5 text-[10px] font-semibold uppercase text-emerald-300';
    }

    return 'inline-flex rounded-full bg-violet-500/20 px-2.5 py-0.5 text-[10px] font-semibold uppercase text-violet-300';
}

function initials(name) {
    return String(name ?? '')
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() ?? '')
        .join('') || '?';
}

function renderDistributionBars(container, leaders) {
    if (!container) {
        return;
    }

    container.innerHTML = '';

    if (!leaders?.length) {
        container.innerHTML = '<p class="w-full self-center text-center text-xs text-slate-500">Waiting for votes…</p>';
        return;
    }

    const maxVotes = Math.max(...leaders.map((item) => Number(item.votes) || 0), 1);

    leaders.slice(0, 8).forEach((leader) => {
        const height = Math.max(12, Math.round((Number(leader.votes) / maxVotes) * 100));
        const bar = document.createElement('div');
        bar.className = 'flex-1 rounded-t-md bg-gradient-to-t from-violet-600 to-indigo-400 transition-all';
        bar.style.height = `${height}%`;
        bar.title = `${leader.candidate}: ${leader.votes} votes`;
        container.appendChild(bar);
    });
}

function renderLeadingCandidates(container, leaders) {
    if (!container) {
        return;
    }

    container.innerHTML = '';

    if (!leaders?.length) {
        container.innerHTML = '<p class="col-span-2 text-sm text-slate-400">Leaders appear here as ballots are cast.</p>';
        return;
    }

    leaders.slice(0, 4).forEach((leader) => {
        const card = document.createElement('article');
        card.className = 'rounded-xl border border-slate-800 bg-slate-950/60 p-3';
        const percent = Number(leader.percent ?? 0);
        card.innerHTML = `
            <div class="flex items-center gap-2.5">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-violet-500/20 text-[11px] font-bold text-violet-200 ring-1 ring-violet-500/30">
                    ${escapeHtml(initials(leader.candidate))}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-white">${escapeHtml(leader.candidate)}</p>
                    <p class="truncate text-[11px] text-slate-500">${escapeHtml(leader.position)}${leader.party ? ` · ${escapeHtml(leader.party)}` : ''}</p>
                </div>
            </div>
            <div class="mt-2.5">
                <div class="mb-1 flex justify-between text-[10px] text-slate-500">
                    <span>${Number(leader.votes ?? 0).toLocaleString()} votes</span>
                    <span>${percent.toFixed(1)}%</span>
                </div>
                <div class="h-1.5 overflow-hidden rounded-full bg-slate-800">
                    <div class="h-full rounded-full bg-gradient-to-r from-violet-500 to-indigo-400" style="width:${Math.min(100, percent)}%"></div>
                </div>
            </div>
        `;
        container.appendChild(card);
    });
}

function setLiveIndicators(isLive) {
    const pulse = document.getElementById('live-voting-pulse');
    const dot = document.getElementById('live-voting-dot');

    pulse?.classList.toggle('hidden', !isLive);
    if (dot) {
        dot.className = isLive
            ? 'relative inline-flex h-2 w-2 rounded-full bg-emerald-400'
            : 'relative inline-flex h-2 w-2 rounded-full bg-slate-600';
    }
}

function applyLiveVotingData(panel, data) {
    const isLive = Boolean(data?.is_live);
    const idle = document.getElementById('live-voting-idle');
    const active = document.getElementById('live-voting-active');

    idle?.classList.toggle('hidden', isLive);
    active?.classList.toggle('hidden', !isLive);
    setLiveIndicators(isLive);

    const status = document.getElementById('live-voting-status');
    if (status) {
        const label = isLive ? (data.election_status ?? 'Voting Open') : (status.dataset.fallback || status.textContent);
        if (isLive) {
            status.textContent = label;
            status.className = statusTone(label);
        }
    }

    if (!isLive) {
        return;
    }

    const totalVotes = document.getElementById('live-voting-total-votes');
    const turnout = document.getElementById('live-voting-turnout');
    const turnoutDetail = document.getElementById('live-voting-turnout-detail');
    const title = document.getElementById('live-voting-election-title');
    const updatedAt = document.getElementById('live-voting-updated-at');
    const leaders = document.getElementById('live-voting-leaders');
    const bars = document.getElementById('live-voting-bars');

    if (totalVotes) {
        totalVotes.textContent = Number(data.total_votes ?? 0).toLocaleString();
    }

    if (turnout) {
        turnout.textContent = `${Number(data.turnout_percent ?? 0).toFixed(1)}%`;
    }

    if (turnoutDetail) {
        turnoutDetail.textContent = `${Number(data.unique_voters ?? 0).toLocaleString()} / ${Number(data.eligible_voters ?? 0).toLocaleString()}`;
    }

    if (title && data.election_title) {
        title.textContent = data.election_title;
    }

    if (updatedAt) {
        updatedAt.textContent = formatUpdatedAt(data.updated_at);
    }

    renderLeadingCandidates(leaders, data.leading_candidates ?? []);
    renderDistributionBars(bars, data.leading_candidates ?? []);
}

function initLiveVoting() {
    const panel = document.getElementById('live-voting-panel');
    const endpoint = panel?.dataset.liveVotingUrl;

    if (!panel || !endpoint) {
        return;
    }

    let timerId = null;

    const fetchProgress = async () => {
        try {
            const response = await fetch(endpoint, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                applyLiveVotingData(panel, { is_live: false });
                return;
            }

            const data = await response.json();
            applyLiveVotingData(panel, data);

            if (!data?.is_live && timerId) {
                // Keep polling so the panel appears when voting opens without reload.
            }
        } catch {
            applyLiveVotingData(panel, { is_live: false });
        }
    };

    fetchProgress();
    timerId = window.setInterval(fetchProgress, LIVE_VOTING_INTERVAL_MS);
}

document.addEventListener('DOMContentLoaded', initLiveVoting);

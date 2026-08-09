const POLL_MS = 5000;
const VIEW_KEY = 'live-monitoring-view';

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

function formatNumber(value) {
    return Number(value || 0).toLocaleString();
}

function currentFilterQuery() {
    const form = document.querySelector('[data-live-filters]');
    if (!form) {
        return '';
    }

    const params = new URLSearchParams();
    ['administrator', 'school_year', 'status'].forEach((name) => {
        const el = form.elements.namedItem(name);
        if (el && el.value) {
            params.set(name, el.value);
        }
    });

    ['active_only', 'published', 'results_pending'].forEach((name) => {
        const el = form.elements.namedItem(name);
        if (el && el.checked) {
            params.set(name, '1');
        }
    });

    const query = params.toString();
    return query ? `?${query}` : '';
}

function flash(el) {
    if (!el) {
        return;
    }

    el.classList.add('text-emerald-300');
    window.setTimeout(() => el.classList.remove('text-emerald-300'), 700);
}

function setText(root, field, value, { flashOnChange = false } = {}) {
    const nodes = root.querySelectorAll(`[data-field="${field}"]`);
    nodes.forEach((el) => {
        const next = String(value);
        const changed = el.textContent !== next;
        el.textContent = next;
        if (flashOnChange && changed && el.hasAttribute('data-flashable')) {
            flash(el);
        }
    });
}

function syncLiveBadge(card, isLive) {
    let badge = card.querySelector('[data-live-badge]');
    const host = card.querySelector('.absolute.left-3.top-3') || card.querySelector('[data-field="name"]')?.parentElement;

    if (isLive && !badge && host) {
        badge = document.createElement('span');
        badge.dataset.liveBadge = '';
        badge.className = 'inline-flex items-center gap-1.5 rounded-full bg-emerald-500/15 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-emerald-200';
        badge.innerHTML = `
            <span class="relative flex h-2 w-2">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
            </span>
            LIVE`;
        host.prepend(badge);
    } else if (!isLive && badge) {
        badge.remove();
    }
}

function updateLeaderboard(card, rankings) {
    const list = card.querySelector('[data-leaderboard-list]');
    const empty = card.querySelector('[data-leaderboard-empty]');

    if (!list) {
        return;
    }

    const rows = Array.isArray(rankings) ? rankings.slice(0, 5) : [];

    if (!rows.length) {
        list.innerHTML = '';
        list.classList.add('hidden');
        if (empty) {
            empty.classList.remove('hidden');
            empty.textContent = 'Waiting for votes…';
        }
        return;
    }

    if (empty) {
        empty.classList.add('hidden');
    }

    list.classList.remove('hidden');
    list.innerHTML = rows.map((row) => `
        <li class="flex items-center gap-3 px-3 py-2 text-sm">
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-violet-500/15 text-xs font-bold text-violet-200">${escapeHtml(row.rank)}</span>
            <span class="min-w-0 flex-1 truncate text-slate-200">${escapeHtml(row.name)}</span>
            <span class="font-bold text-white">${formatNumber(row.votes)}</span>
            <span class="w-12 text-right text-xs text-slate-400">${escapeHtml(row.percent ?? 0)}%</span>
        </li>
    `).join('');
}

function applySharedFields(root, data) {
    setText(root, 'name', data.name);
    setText(root, 'owner_name', data.owner_name);
    setText(root, 'status_label', data.status_label);
    setText(root, 'votes_cast', formatNumber(data.votes_cast), { flashOnChange: true });
    setText(root, 'last_vote_at', data.last_vote_at ?? '—', { flashOnChange: true });
    setText(root, 'phase', data.phase ?? '—');
}

function applyElectionCard(card, data) {
    applySharedFields(card, data);
    setText(card, 'schedule', data.schedule);
    setText(card, 'registered_voters', formatNumber(data.registered_voters));
    setText(card, 'turnout_percent', data.turnout_percent ?? 0, { flashOnChange: true });
    setText(card, 'candidates_count', formatNumber(data.candidates_count));

    const bar = card.querySelector('[data-turnout-bar]');
    if (bar) {
        bar.style.width = `${Math.min(100, Math.max(0, Number(data.turnout_percent) || 0))}%`;
    }

    card.classList.toggle('border-emerald-500/30', Boolean(data.is_live));
    card.classList.toggle('border-violet-500/15', !data.is_live);
    syncLiveBadge(card, Boolean(data.is_live));
}

function applyTalentCard(card, data) {
    applySharedFields(card, data);
    setText(card, 'category', data.category);
    setText(card, 'registration_count', formatNumber(data.registration_count), { flashOnChange: true });
    setText(card, 'approved_participants', formatNumber(data.approved_participants), { flashOnChange: true });
    setText(card, 'judges_completed', data.judges_completed ?? 0);
    setText(card, 'judges_total', data.judges_total ?? 0);
    setText(card, 'judges_remaining', data.judges_remaining ?? 0);

    const bar = card.querySelector('[data-judges-bar]');
    if (bar) {
        const total = Number(data.judges_total) || 0;
        const done = Number(data.judges_completed) || 0;
        bar.style.width = `${total > 0 ? Math.min(100, (done / total) * 100) : 0}%`;
    }

    card.classList.toggle('border-emerald-500/30', Boolean(data.is_live));
    card.classList.toggle('border-violet-500/15', !data.is_live);
    syncLiveBadge(card, Boolean(data.is_live));

    if (data.is_live) {
        updateLeaderboard(card, data.rankings || []);
    }
}

function applySummary(summary) {
    if (!summary) {
        return;
    }

    Object.entries(summary).forEach(([key, value]) => {
        if (typeof value === 'number') {
            const el = document.querySelector(`[data-summary="${key}"]`);
            if (el) {
                const next = formatNumber(value);
                if (el.textContent !== next) {
                    el.textContent = next;
                    flash(el);
                }
            }
        }
    });
}

function setUpdatedAt(isoString) {
    const updatedAt = document.querySelector('[data-live-updated-at]');
    const online = document.querySelector('[data-system-online]');

    if (updatedAt) {
        const stamp = isoString ? new Date(isoString) : new Date();
        updatedAt.textContent = Number.isNaN(stamp.getTime())
            ? 'Last updated —'
            : `Last updated ${stamp.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', second: '2-digit' })}`;
    }

    if (online) {
        online.textContent = 'Online';
        online.classList.remove('text-rose-400');
        online.classList.add('text-emerald-400/90');
    }
}

function setOfflineStatus() {
    const online = document.querySelector('[data-system-online]');
    if (online) {
        online.textContent = 'Reconnecting';
        online.classList.remove('text-emerald-400/90');
        online.classList.add('text-rose-400');
    }
}

function applyListRow(row, data, mode) {
    applySharedFields(row, data);
    if (mode === 'election') {
        setText(row, 'turnout_percent', data.turnout_percent ?? 0, { flashOnChange: true });
    } else {
        setText(row, 'category', data.category);
        setText(row, 'approved_participants', formatNumber(data.approved_participants), { flashOnChange: true });
    }
}

function setView(mode) {
    const cardsView = document.querySelector('[data-cards-view]');
    const listView = document.querySelector('[data-live-list]');
    const toggle = document.querySelector('[data-view-toggle]');

    const isList = mode === 'list';
    cardsView?.classList.toggle('hidden', isList);
    listView?.classList.toggle('hidden', !isList);

    toggle?.querySelectorAll('[data-view]').forEach((btn) => {
        const active = btn.dataset.view === mode;
        btn.classList.toggle('bg-slate-800', active);
        btn.classList.toggle('text-white', active);
        btn.classList.toggle('text-slate-400', !active);
    });

    try {
        localStorage.setItem(VIEW_KEY, mode);
    } catch {
        // ignore
    }
}

function wireViewToggle() {
    const toggle = document.querySelector('[data-view-toggle]');
    if (!toggle) {
        return;
    }

    toggle.querySelectorAll('[data-view]').forEach((btn) => {
        btn.addEventListener('click', () => setView(btn.dataset.view));
    });

    let saved = 'cards';
    try {
        saved = localStorage.getItem(VIEW_KEY) || 'cards';
    } catch {
        // ignore
    }
    setView(saved === 'list' ? 'list' : 'cards');
}

async function pollOnce(root) {
    const pollUrl = root.dataset.pollUrl;
    if (!pollUrl) {
        return;
    }

    const response = await fetch(`${pollUrl}${currentFilterQuery()}`, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    });

    if (!response.ok) {
        throw new Error(`Poll failed (${response.status})`);
    }

    const payload = await response.json();
    const cards = Array.isArray(payload.cards) ? payload.cards : [];
    const mode = root.dataset.mode;
    const cardNodes = [...root.querySelectorAll('[data-live-card]')];

    setUpdatedAt(payload.generated_at);
    applySummary(payload.summary);

    const existingIds = cardNodes.map((el) => String(el.dataset.cardId)).sort();
    const nextIds = cards.map((card) => String(card.id)).sort();
    const sameMembership = existingIds.length === nextIds.length
        && existingIds.every((id, index) => id === nextIds[index]);

    if (!sameMembership) {
        window.location.reload();
        return;
    }

    cards.forEach((data) => {
        const card = root.querySelector(`[data-live-card][data-card-id="${data.id}"]`);
        const row = root.querySelector(`[data-live-list-row][data-card-id="${data.id}"]`);

        if (card) {
            if (mode === 'talent') {
                applyTalentCard(card, data);
            } else {
                applyElectionCard(card, data);
            }
        }

        if (row) {
            applyListRow(row, data, mode);
        }
    });
}

function boot() {
    const root = document.getElementById('live-monitoring-root');
    if (!root) {
        return;
    }

    wireViewToggle();

    const refreshBtn = document.querySelector('[data-live-refresh]');
    let timer = null;
    let inFlight = false;

    const run = async () => {
        if (inFlight || document.hidden) {
            return;
        }

        inFlight = true;
        try {
            await pollOnce(root);
        } catch (error) {
            console.warn('Live monitoring poll failed', error);
            setOfflineStatus();
        } finally {
            inFlight = false;
        }
    };

    refreshBtn?.addEventListener('click', () => {
        window.location.reload();
    });

    timer = window.setInterval(run, POLL_MS);
    run();

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            run();
        }
    });

    window.addEventListener('beforeunload', () => {
        if (timer) {
            window.clearInterval(timer);
        }
    });
}

boot();

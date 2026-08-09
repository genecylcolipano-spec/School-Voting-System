document.addEventListener('DOMContentLoaded', () => {
    const panel = document.getElementById('talent-standings-panel');

    if (!panel) {
        return;
    }

    const standingsUrl = panel.dataset.standingsUrl;
    const votedEntryId = panel.dataset.votedEntryId ? Number(panel.dataset.votedEntryId) : null;
    const listEl = document.getElementById('talent-standings-list');
    const totalEl = document.getElementById('talent-standings-total');
    const updatedEl = document.getElementById('talent-standings-updated');

    if (!standingsUrl || !listEl) {
        return;
    }

    const renderStandings = (payload) => {
        if (!payload?.entries?.length) {
            listEl.innerHTML = '<p class="text-sm text-slate-400">No standings available yet.</p>';
            return;
        }

        listEl.innerHTML = payload.entries.map((entry, index) => {
            const isVoted = votedEntryId !== null && entry.id === votedEntryId;
            const rank = index + 1;

            return `
                <article class="rounded-xl border ${isVoted ? 'border-cyan-400/40 bg-cyan-500/10' : 'border-slate-800 bg-slate-950/50'} p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">#${rank}</p>
                            <h3 class="mt-1 text-base font-semibold text-white">${escapeHtml(entry.display_name)}</h3>
                            ${entry.grade_level ? `<p class="mt-1 text-xs text-slate-400">Grade ${escapeHtml(entry.grade_level)}${entry.section ? ` · Section ${escapeHtml(entry.section)}` : ''}</p>` : ''}
                            ${isVoted ? '<p class="mt-2 text-xs font-semibold text-cyan-300">Your vote</p>' : ''}
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-cyan-300">${entry.percent}%</p>
                            <p class="text-xs text-slate-400">${entry.votes} vote${entry.votes === 1 ? '' : 's'}</p>
                        </div>
                    </div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-800">
                        <div class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-sky-400 transition-all duration-500" style="width: ${entry.percent}%"></div>
                    </div>
                </article>
            `;
        }).join('');

        if (totalEl) {
            totalEl.textContent = `${payload.total_votes} total vote${payload.total_votes === 1 ? '' : 's'}`;
        }

        if (updatedEl && payload.updated_at) {
            const updated = new Date(payload.updated_at);
            updatedEl.textContent = `Updated ${updated.toLocaleTimeString()}`;
        }
    };

    const escapeHtml = (value) => String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');

    const fetchStandings = async () => {
        try {
            const response = await fetch(standingsUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            renderStandings(payload);
        } catch {
            // Ignore transient network errors during polling.
        }
    };

    fetchStandings();
    window.setInterval(fetchStandings, 5000);
});

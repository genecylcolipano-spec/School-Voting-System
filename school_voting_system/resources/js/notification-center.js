const POLL_INTERVAL_MS = 30000;
const LIST_RESOLVE_RETRIES = 20;
const LIST_RESOLVE_DELAY_MS = 50;

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function markOneUrl(root, id) {
    const template = root.dataset.markOneUrlTemplate || '';
    return template
        .replaceAll('__ID__', String(id))
        .replaceAll('%7Bnotification%7D', String(id))
        .replaceAll('{notification}', String(id));
}

/**
 * Panel content lives inside Alpine x-teleport="body", so it is NOT a
 * descendant of [data-notification-center] after mount. Resolve via center id.
 * Before Alpine starts, content is inside <template> and not queryable.
 */
function resolveList(root) {
    const centerId = root.dataset.centerId;
    if (centerId) {
        return document.querySelector(`[data-notification-list][data-center-id="${CSS.escape(centerId)}"]`);
    }

    return root.querySelector('[data-notification-list]')
        || document.querySelector(`[data-notification-list][data-feed-url="${CSS.escape(root.dataset.feedUrl || '')}"]`);
}

function resolveMarkAllForm(root) {
    const centerId = root.dataset.centerId;
    if (centerId) {
        return document.querySelector(`[data-notification-mark-all][data-center-id="${CSS.escape(centerId)}"]`)
            || root.querySelector('[data-notification-mark-all]');
    }

    return root.querySelector('[data-notification-mark-all]');
}

function resolveBadge(root) {
    return root.querySelector('[data-notification-badge]');
}

function waitForList(root) {
    return new Promise((resolve) => {
        let attempts = 0;

        const tick = () => {
            const list = resolveList(root);
            if (list || attempts >= LIST_RESOLVE_RETRIES) {
                resolve(list);
                return;
            }

            attempts += 1;
            window.setTimeout(tick, LIST_RESOLVE_DELAY_MS);
        };

        tick();
    });
}

function showListMessage(list, message, isError = false) {
    if (!list) {
        return;
    }

    const color = isError ? 'text-rose-300' : 'text-slate-500';
    list.innerHTML = `<p class="px-4 py-8 text-center text-sm ${color}">${escapeHtml(message)}</p>`;
}

function renderItems(container, items, root) {
    if (!items.length) {
        showListMessage(container, 'No notifications.');
        return;
    }

    container.innerHTML = items.map((item) => {
        const hasUrl = Boolean(item.url);
        const unreadClass = item.read && !hasUrl ? 'opacity-80' : 'bg-slate-950/40 cursor-pointer';
        const dot = item.read ? '' : '<span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-sky-400" aria-hidden="true"></span>';

        return `
            <article class="flex gap-3 px-4 py-3 transition hover:bg-slate-800/40 ${unreadClass}" data-notification-id="${escapeHtml(item.id)}" data-notification-read="${item.read ? '1' : '0'}" data-notification-url="${escapeHtml(item.url || '')}" role="button" tabindex="0">
                <span class="text-xl" aria-hidden="true">${escapeHtml(item.icon)}</span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-white">${escapeHtml(item.title)}</p>
                    <p class="mt-0.5 text-xs leading-relaxed text-slate-400">${escapeHtml(item.message)}</p>
                    <p class="mt-1 text-[11px] text-slate-500">${escapeHtml(item.time_ago)}</p>
                </div>
                ${dot}
            </article>
        `;
    }).join('');

    container.querySelectorAll('[data-notification-id]').forEach((article) => {
        const openNotification = async () => {
            const actionUrl = article.dataset.notificationUrl || '';
            const alreadyRead = article.dataset.notificationRead === '1';

            if (!alreadyRead) {
                const id = article.dataset.notificationId;
                const markUrl = markOneUrl(root, id);
                if (markUrl && !markUrl.includes('{notification}') && !markUrl.includes('__ID__')) {
                    try {
                        await fetch(markUrl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken(),
                            },
                        });
                    } catch (error) {
                        console.error('Failed to mark notification read', error);
                    }
                }
            }

            if (actionUrl) {
                window.location.href = actionUrl;
                return;
            }

            fetchFeed(root);
        };

        article.addEventListener('click', openNotification);
        article.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openNotification();
            }
        });
    });
}

function updateBadge(badge, count) {
    if (!badge) {
        return;
    }

    if (count > 0) {
        badge.textContent = count > 99 ? '99+' : String(count);
        badge.classList.remove('hidden');
    } else {
        badge.textContent = '';
        badge.classList.add('hidden');
    }
}

async function fetchFeed(root) {
    const url = root.dataset.feedUrl;
    const list = resolveList(root) || await waitForList(root);
    const badge = resolveBadge(root);

    if (!url) {
        showListMessage(list, 'Unable to load notifications.', true);
        return;
    }

    if (!list) {
        console.error('Notification list element not found after teleport');
        return;
    }

    try {
        const response = await fetch(url, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(`Feed request failed (${response.status})`);
        }

        const payload = await response.json();
        renderItems(list, payload.items ?? [], root);
        updateBadge(badge, Number(payload.unread_count ?? 0));
        window.dispatchEvent(new CustomEvent('responsive-popover:reposition'));
    } catch (error) {
        console.error('Notification feed error', error);
        showListMessage(list, 'Unable to load notifications.', true);
    }
}

function bindMarkAll(root) {
    const markAllForm = resolveMarkAllForm(root);
    if (!markAllForm || markAllForm.dataset.bound === '1') {
        return;
    }

    markAllForm.dataset.bound = '1';
    markAllForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        try {
            const formData = new FormData(markAllForm);
            await fetch(markAllForm.action, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
            });
        } catch (error) {
            console.error('Mark all notifications failed', error);
        } finally {
            fetchFeed(root);
        }
    });
}

async function initNotificationCenter(root) {
    if (root.dataset.notificationInitialized === '1') {
        return;
    }
    root.dataset.notificationInitialized = '1';

    await fetchFeed(root);
    bindMarkAll(root);
    // Mark-all may only exist after teleport; retry briefly.
    window.setTimeout(() => bindMarkAll(root), 100);
    window.setTimeout(() => bindMarkAll(root), 300);

    window.setInterval(() => fetchFeed(root), POLL_INTERVAL_MS);
}

function bootNotificationCenters() {
    document.querySelectorAll('[data-notification-center]').forEach((root) => {
        initNotificationCenter(root);
    });
}

function scheduleBoot() {
    const run = () => bootNotificationCenters();

    // Prefer after Alpine so x-teleport has moved the panel into the document.
    document.addEventListener('alpine:initialized', run, { once: true });

    // Fallback when the event already fired, or a second Vite entry loads late.
    const fallback = () => window.setTimeout(run, 100);
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fallback, { once: true });
    } else {
        fallback();
    }
}

scheduleBoot();

export { fetchFeed, POLL_INTERVAL_MS };

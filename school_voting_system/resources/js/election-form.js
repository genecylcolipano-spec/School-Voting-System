/**
 * Unified election setup form — dynamic positions and candidates.
 */

function nextIndex(container, rowSelector) {
    const rows = container.querySelectorAll(rowSelector);
    let max = -1;

    rows.forEach((row) => {
        const match = row.dataset.index;
        if (match !== undefined) {
            max = Math.max(max, Number(match));
        }
    });

    return max + 1;
}

function bindRemoveButtons(scope) {
    scope.querySelectorAll('[data-remove-row]').forEach((button) => {
        if (button.dataset.bound) {
            return;
        }

        button.dataset.bound = '1';
        button.addEventListener('click', () => {
            button.closest('.position-row, .candidate-row')?.remove();
            refreshPositionOptions();
        });
    });
}

function getPositionOptions() {
    const list = document.getElementById('positions-list');
    if (!list) {
        return [];
    }

    return [...list.querySelectorAll('.position-row')]
        .map((row, ordinal) => {
            const input = row.querySelector('input[type="text"]');
            if (!input) {
                return null;
            }

            // Use the real array index from the field name (e.g. positions[2][name])
            // so a candidate's position_index always matches the saved position key,
            // even after rows are added or removed.
            const match = input.name.match(/\[(\d+)\]/);
            const index = match ? Number(match[1]) : ordinal;

            return {
                index,
                label: input.value.trim() || `Position ${ordinal + 1}`,
            };
        })
        .filter(Boolean);
}

function refreshPositionOptions() {
    const candidatesList = document.getElementById('candidates-list');
    if (!candidatesList || candidatesList.dataset.isEdit === '1') {
        return;
    }

    const options = getPositionOptions();

    candidatesList.querySelectorAll('select[data-position-select]').forEach((select) => {
        const current = select.value;
        select.innerHTML = '';

        options.forEach((option) => {
            const el = document.createElement('option');
            el.value = String(option.index);
            el.textContent = option.label;
            select.appendChild(el);
        });

        if (current && [...select.options].some((o) => o.value === current)) {
            select.value = current;
        }
    });
}

function addPositionRow() {
    const list = document.getElementById('positions-list');
    if (!list) {
        return;
    }

    const prefix = list.dataset.prefix;
    const index = nextIndex(list, '.position-row');
    const row = document.createElement('div');
    row.className = 'position-row grid gap-3 rounded-xl border border-slate-800 bg-slate-950/40 p-4 md:grid-cols-[1fr_auto]';
    row.dataset.index = String(index);
    row.innerHTML = `
        <input type="text" name="${prefix}[${index}][name]" placeholder="e.g. Vice President" class="rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100" />
        <button type="button" data-remove-row class="rounded-lg border border-rose-500/30 px-3 py-2 text-xs font-semibold text-rose-300 hover:bg-rose-500/10">Remove</button>
    `;

    list.appendChild(row);
    bindRemoveButtons(row);
    row.querySelector('input')?.addEventListener('input', refreshPositionOptions);
    refreshPositionOptions();
}

function getCampaignOptions() {
    const container = document.getElementById('participating-campaigns');
    if (!container) {
        return [];
    }

    return [...container.querySelectorAll('.campaign-checkbox:checked')].map((checkbox) => ({
        id: checkbox.value,
        name: checkbox.dataset.campaignName || `Campaign ${checkbox.value}`,
        acronym: checkbox.dataset.campaignAcronym || '',
    }));
}

function buildCampaignSelect(prefix, index) {
    const options = getCampaignOptions()
        .map((c) => `<option value="${c.id}">${c.name}${c.acronym ? ` (${c.acronym})` : ''}</option>`)
        .join('');

    return `<select data-campaign-select name="${prefix}[${index}][partylist_id]" class="rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100"><option value="">— Independent (no campaign) —</option>${options}</select>`;
}

function refreshCampaignOptions() {
    const candidatesList = document.getElementById('candidates-list');
    if (!candidatesList) {
        return;
    }

    const options = getCampaignOptions();

    candidatesList.querySelectorAll('select[data-campaign-select]').forEach((select) => {
        const current = select.value;
        select.innerHTML = '<option value="">— Independent (no campaign) —</option>';

        options.forEach((option) => {
            const el = document.createElement('option');
            el.value = String(option.id);
            el.textContent = option.acronym ? `${option.name} (${option.acronym})` : option.name;
            select.appendChild(el);
        });

        if (current && [...select.options].some((o) => o.value === current)) {
            select.value = current;
        }
    });
}

function buildCategorySelect(prefix, index, categories, isEdit) {
    if (isEdit) {
        const options = categories.map((c) => `<option value="${c.id}">${c.name}</option>`).join('');
        return `<select name="${prefix}[${index}][election_category_id]" class="rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">${options}</select>`;
    }

    const positionOptions = getPositionOptions()
        .map((p) => `<option value="${p.index}">${p.label}</option>`)
        .join('');

    return `<select data-position-select name="${prefix}[${index}][position_index]" class="rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">${positionOptions}</select>`;
}

function photoFieldMarkup(prefix, index) {
    return `
        <div class="candidate-photo-field md:col-span-2 flex items-center gap-4 rounded-xl border border-slate-800/70 bg-slate-950/30 p-3">
            <div class="relative h-16 w-16 shrink-0 overflow-hidden rounded-full border border-slate-700 bg-slate-800">
                <img data-photo-preview src="" alt="" class="h-full w-full object-cover hidden">
                <div data-photo-placeholder class="flex h-full w-full items-center justify-center text-slate-500">
                    <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5z" /></svg>
                </div>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Profile Photo</p>
                <div class="mt-1 flex flex-wrap items-center gap-3">
                    <button type="button" data-photo-change class="rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-200 transition hover:bg-violet-500/10">Upload Photo</button>
                    <button type="button" data-photo-remove class="hidden text-xs font-semibold text-rose-300 transition hover:text-rose-200">Remove</button>
                </div>
                <input type="file" data-photo-input name="${prefix}[${index}][photo]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="hidden">
                <p data-photo-error class="mt-1 hidden text-xs text-rose-300"></p>
                <p class="mt-1 text-[11px] text-slate-500">JPG, JPEG, PNG or WebP · Max 2 MB</p>
            </div>
        </div>
    `;
}

function addCandidateRow() {
    const list = document.getElementById('candidates-list');
    if (!list) {
        return;
    }

    const prefix = list.dataset.prefix;
    const isEdit = list.dataset.isEdit === '1';
    const categories = JSON.parse(list.dataset.categories || '[]');
    const index = nextIndex(list, '.candidate-row');
    const row = document.createElement('div');
    row.className = 'candidate-row grid gap-3 rounded-xl border border-slate-800 bg-slate-950/40 p-4 md:grid-cols-2';
    row.dataset.index = String(index);
    row.innerHTML = `
        ${photoFieldMarkup(prefix, index)}
        <input type="text" name="${prefix}[${index}][display_name]" placeholder="Display name" class="rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100" />
        ${buildCategorySelect(prefix, index, categories, isEdit)}
        ${buildCampaignSelect(prefix, index)}
        <button type="button" data-remove-row class="rounded-lg border border-rose-500/30 px-3 py-2 text-xs font-semibold text-rose-300 hover:bg-rose-500/10">Remove</button>
        <textarea name="${prefix}[${index}][platform]" rows="2" placeholder="Platform" class="md:col-span-2 rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100"></textarea>
    `;

    list.appendChild(row);
    bindRemoveButtons(row);
}

const PHOTO_ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
const PHOTO_MAX_BYTES = 2 * 1024 * 1024;

function initCandidatePhotoDelegation() {
    document.addEventListener('click', (event) => {
        const changeBtn = event.target.closest('[data-photo-change]');
        if (changeBtn) {
            changeBtn.closest('.candidate-photo-field')?.querySelector('[data-photo-input]')?.click();
            return;
        }

        const removeBtn = event.target.closest('[data-photo-remove]');
        if (removeBtn) {
            const field = removeBtn.closest('.candidate-photo-field');
            if (!field) return;
            const input = field.querySelector('[data-photo-input]');
            const preview = field.querySelector('[data-photo-preview]');
            const placeholder = field.querySelector('[data-photo-placeholder]');
            const removed = field.querySelector('[data-photo-removed]');
            const error = field.querySelector('[data-photo-error]');

            if (input) input.value = '';
            if (preview) { preview.src = ''; preview.classList.add('hidden'); }
            if (placeholder) placeholder.classList.remove('hidden');
            if (removed) removed.value = '1';
            if (error) { error.textContent = ''; error.classList.add('hidden'); }
            removeBtn.classList.add('hidden');
        }
    });

    document.addEventListener('change', (event) => {
        const input = event.target.closest('[data-photo-input]');
        if (!input) return;

        const field = input.closest('.candidate-photo-field');
        const preview = field?.querySelector('[data-photo-preview]');
        const placeholder = field?.querySelector('[data-photo-placeholder]');
        const removed = field?.querySelector('[data-photo-removed]');
        const removeBtn = field?.querySelector('[data-photo-remove]');
        const error = field?.querySelector('[data-photo-error]');
        const file = input.files?.[0];

        const showError = (message) => {
            input.value = '';
            if (error) { error.textContent = message; error.classList.remove('hidden'); }
        };

        if (error) { error.textContent = ''; error.classList.add('hidden'); }

        if (!file) return;

        if (!PHOTO_ALLOWED_TYPES.includes(file.type)) {
            showError('Only JPG, JPEG, PNG, or WebP images are allowed.');
            return;
        }
        if (file.size > PHOTO_MAX_BYTES) {
            showError('Image must be 2 MB or smaller.');
            return;
        }

        if (removed) removed.value = '0';
        const reader = new FileReader();
        reader.onload = (e) => {
            if (preview) { preview.src = e.target.result; preview.classList.remove('hidden'); }
            if (placeholder) placeholder.classList.add('hidden');
            if (removeBtn) removeBtn.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelector('[data-add-position]')?.addEventListener('click', addPositionRow);
    document.querySelector('[data-add-candidate]')?.addEventListener('click', addCandidateRow);

    initCandidatePhotoDelegation();
    bindRemoveButtons(document);
    document.getElementById('positions-list')?.querySelectorAll('input').forEach((input) => {
        input.addEventListener('input', refreshPositionOptions);
    });
    document.getElementById('participating-campaigns')?.querySelectorAll('.campaign-checkbox').forEach((checkbox) => {
        checkbox.addEventListener('change', refreshCampaignOptions);
    });

    if (!document.getElementById('candidates-list')?.dataset.isEdit) {
        addCandidateRow();
    }
});

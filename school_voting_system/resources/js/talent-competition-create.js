/**
 * Talent Competition create form — dynamic participant rows + image compression.
 */

const MAX_IMAGE_BYTES = 2 * 1024 * 1024;
const MAX_IMAGE_DIMENSION = 1920;

async function compressImageFile(file) {
    if (!file?.type?.match(/^image\/(jpeg|jpg|png)$/i)) {
        return file;
    }

    const objectUrl = URL.createObjectURL(file);

    try {
        const image = await new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => resolve(img);
            img.onerror = () => reject(new Error('Unable to read image.'));
            img.src = objectUrl;
        });

        let width = image.naturalWidth;
        let height = image.naturalHeight;
        const needsResize = file.size > MAX_IMAGE_BYTES
            || width > MAX_IMAGE_DIMENSION
            || height > MAX_IMAGE_DIMENSION;

        if (!needsResize) {
            return file;
        }

        const ratio = Math.min(1, MAX_IMAGE_DIMENSION / Math.max(width, height));
        width = Math.max(1, Math.round(width * ratio));
        height = Math.max(1, Math.round(height * ratio));

        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');

        const drawScaled = (targetWidth, targetHeight) => {
            canvas.width = targetWidth;
            canvas.height = targetHeight;
            context.clearRect(0, 0, targetWidth, targetHeight);
            context.drawImage(image, 0, 0, targetWidth, targetHeight);
        };

        drawScaled(width, height);

        let quality = 0.9;
        let blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', quality));

        while (blob && blob.size > MAX_IMAGE_BYTES && quality > 0.35) {
            quality -= 0.08;
            blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', quality));
        }

        let scale = 0.85;
        while (blob && blob.size > MAX_IMAGE_BYTES && scale > 0.35) {
            drawScaled(Math.max(1, Math.round(width * scale)), Math.max(1, Math.round(height * scale)));
            blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.75));
            scale -= 0.1;
        }

        if (!blob) {
            return file;
        }

        const baseName = file.name.replace(/\.[^.]+$/, '') || 'event-image';

        return new File([blob], `${baseName}.jpg`, {
            type: 'image/jpeg',
            lastModified: Date.now(),
        });
    } catch {
        return file;
    } finally {
        URL.revokeObjectURL(objectUrl);
    }
}

function initEventImageCompression() {
    const input = document.querySelector('input[name="image"]');
    const status = document.getElementById('talent-event-image-status');

    if (!input) {
        return;
    }

    input.addEventListener('change', async () => {
        const file = input.files?.[0];

        if (!file) {
            if (status) {
                status.textContent = '';
            }
            return;
        }

        if (status) {
            status.textContent = 'Optimizing image…';
            status.className = 'mt-1 text-xs text-cyan-300';
        }

        const optimized = await compressImageFile(file);

        if (optimized !== file) {
            const transfer = new DataTransfer();
            transfer.items.add(optimized);
            input.files = transfer.files;
        }

        if (status) {
            const sizeMb = (optimized.size / (1024 * 1024)).toFixed(2);
            status.textContent = optimized.size > MAX_IMAGE_BYTES
                ? 'Image is still large; the server will compress it further on save.'
                : `Ready to upload (${sizeMb} MB).`;
            status.className = 'mt-1 text-xs text-slate-500';
        }
    });
}

function initParticipantRows() {
    const list = document.getElementById('participants-list');
    const addBtn = document.getElementById('add-participant');

    if (!list || !addBtn) return;

    let index = Number(list.dataset.nextIndex ?? list.querySelectorAll('[data-participant-row]').length);

    const categoryOptionsHtml = list.querySelector('select[name*="[talent_category]"]')?.innerHTML
        ?? '<option value="">— Use event category —</option>';

    addBtn.addEventListener('click', () => {
        const maxContestants = Number(list.dataset.maxContestants || 0);
        const currentRows = list.querySelectorAll('[data-participant-row]').length;

        if (maxContestants > 0 && currentRows >= maxContestants) {
            window.alert(`This event allows a maximum of ${maxContestants} contestants.`);
            return;
        }

        const row = document.createElement('div');
        row.className = 'participant-row rounded-xl border border-slate-800 bg-slate-950/50 p-4';
        row.dataset.participantRow = '';
        row.innerHTML = `
            <div class="mb-2 flex justify-end">
                <button type="button" data-remove-participant class="text-xs text-rose-400 hover:text-rose-300">Remove</button>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-medium text-slate-400">Full Name</label>
                    <input type="text" name="participants[${index}][display_name]" required class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400">Student ID</label>
                    <input type="text" name="participants[${index}][student_id_number]" placeholder="2026-00123" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-white">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-medium text-slate-400">Grade</label>
                        <input type="text" name="participants[${index}][grade_level]" required placeholder="10" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400">Section</label>
                        <input type="text" name="participants[${index}][section]" required placeholder="A" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-white">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400">Course / Strand</label>
                    <input type="text" name="participants[${index}][course_strand]" placeholder="STEM / ABM / HUMSS" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400">Talent Category</label>
                    <select name="participants[${index}][talent_category]" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-white">${categoryOptionsHtml}</select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400">Performance Title</label>
                    <input type="text" name="participants[${index}][performance_title]" placeholder="e.g. Rendition of 'Imagine'" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-white">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-slate-400">Profile</label>
                    <input type="text" name="participants[${index}][profile_summary]" placeholder="Short bio or background" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-white">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-slate-400">Performance Description</label>
                    <textarea name="participants[${index}][performance_description]" rows="2" placeholder="What they will perform or their platform" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-white"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400">Performance Video URL</label>
                    <input type="url" name="participants[${index}][video_url]" placeholder="https://youtu.be/…" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400">Social Media (optional)</label>
                    <input type="text" name="participants[${index}][social_media]" placeholder="@handle or link" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-white">
                </div>
            </div>
        `;
        list.appendChild(row);
        index += 1;
        list.dataset.nextIndex = String(index);
    });

    list.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-remove-participant]');
        if (!btn) return;

        const rows = list.querySelectorAll('[data-participant-row]');
        if (rows.length <= 1) return;

        btn.closest('[data-participant-row]')?.remove();
    });

    document.querySelector('input[name="max_contestants"]')?.addEventListener('change', (event) => {
        list.dataset.maxContestants = event.target.value || '';
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initParticipantRows();
    initEventImageCompression();
});

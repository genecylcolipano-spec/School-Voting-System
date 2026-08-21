/**
 * Live preview for event/competition banner uploads.
 * Compresses large images, then detects orientation for smart display.
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

function applyPreviewLayout(preview, blur, frame, width, height) {
    const isPortraitOrSquare = width > 0 && height > 0 && width <= height;
    const containClass = 'object-contain';
    const coverClass = 'object-cover';

    preview.classList.remove(containClass, coverClass);
    preview.classList.add(isPortraitOrSquare ? containClass : coverClass);

    if (blur) {
        blur.classList.toggle('hidden', !isPortraitOrSquare);
        blur.src = preview.src;
    }

    if (frame) {
        frame.dataset.contain = isPortraitOrSquare ? '1' : '0';
    }

    return isPortraitOrSquare ? (width === height ? 'square' : 'portrait') : 'landscape';
}

function setOrientationWarning(warningEl, orientation) {
    if (!warningEl) {
        return;
    }

    const show = orientation === 'portrait' || orientation === 'square';
    warningEl.classList.toggle('hidden', !show);

    if (show) {
        warningEl.textContent = orientation === 'square'
            ? 'This image is square. For best appearance, upload a landscape banner (1600 × 900).'
            : 'This image is portrait. For best appearance, upload a landscape banner (1600 × 900).';
    }
}

function initEventImagePreview() {
    const input = document.getElementById('event-image-input');
    const preview = document.getElementById('event-image-preview');
    const blur = document.getElementById('event-image-preview-blur');
    const frame = document.getElementById('event-image-preview-frame');
    const caption = document.getElementById('event-image-caption');
    const warning = document.getElementById('event-image-preview-orientation-warning');
    const status = document.getElementById('event-image-status');

    if (!input || !preview) {
        return;
    }

    const placeholder = preview.dataset.placeholder || preview.src;

    input.addEventListener('change', async () => {
        const file = input.files?.[0];

        if (!file) {
            preview.src = placeholder;
            if (blur) {
                blur.src = placeholder;
            }
            if (caption) {
                caption.textContent = 'Default placeholder shown. Upload a landscape banner (1600 × 900) for best results.';
            }
            if (status) {
                status.textContent = '';
            }
            if (warning && warning.dataset.defaultHidden === '1') {
                warning.classList.add('hidden');
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

        const objectUrl = URL.createObjectURL(optimized);
        preview.src = objectUrl;
        if (blur) {
            blur.src = objectUrl;
        }

        preview.onload = () => {
            const width = preview.naturalWidth;
            const height = preview.naturalHeight;
            const orientation = applyPreviewLayout(preview, blur, frame, width, height);
            const dimensions = width && height ? ` (${width}×${height}px · ${orientation})` : '';

            if (caption) {
                caption.textContent = `Selected: ${optimized.name}${dimensions}. Save the form to upload.`;
            }

            setOrientationWarning(warning, orientation);

            URL.revokeObjectURL(objectUrl);
        };
    });
}

function initCompetitionPosterPreview() {
    const input = document.getElementById('competition-poster-input');
    const preview = document.getElementById('competition-poster-preview');
    const placeholder = document.getElementById('competition-poster-placeholder');

    if (!input || !preview) {
        return;
    }

    input.addEventListener('change', () => {
        const file = input.files?.[0];

        if (!file) {
            return;
        }

        const objectUrl = URL.createObjectURL(file);
        preview.src = objectUrl;
        preview.classList.remove('hidden');
        if (placeholder) {
            placeholder.classList.add('hidden');
        }
        preview.onload = () => URL.revokeObjectURL(objectUrl);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initEventImagePreview();
    initCompetitionPosterPreview();
});

/**
 * Live preview for event/competition banner uploads.
 * Detects orientation and mirrors final smart-display behavior.
 */

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

    if (!input || !preview) {
        return;
    }

    const placeholder = preview.dataset.placeholder || preview.src;

    input.addEventListener('change', () => {
        const file = input.files?.[0];

        if (!file) {
            preview.src = placeholder;
            if (blur) {
                blur.src = placeholder;
            }
            if (caption) {
                caption.textContent = 'Default placeholder shown. Upload a landscape banner (1600 × 900) for best results.';
            }
            if (warning && warning.dataset.defaultHidden === '1') {
                warning.classList.add('hidden');
            }
            return;
        }

        const objectUrl = URL.createObjectURL(file);
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
                caption.textContent = `Selected: ${file.name}${dimensions}. Save the form to upload.`;
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

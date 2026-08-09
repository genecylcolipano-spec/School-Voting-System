/**
 * Live preview for official campaign poster on the create campaign form.
 */

function initCampaignPosterPreview() {
    const input = document.getElementById('campaign-poster-input');
    const preview = document.getElementById('campaign-poster-preview');
    const caption = document.getElementById('campaign-poster-caption');

    if (!input || !preview) {
        return;
    }

    const placeholder = preview.dataset.placeholder || preview.src;

    input.addEventListener('change', () => {
        const file = input.files?.[0];

        if (!file) {
            preview.src = placeholder;
            preview.classList.add('hidden');
            if (caption) {
                caption.textContent = 'JPG or PNG only. Max 2MB.';
            }
            return;
        }

        const objectUrl = URL.createObjectURL(file);
        preview.src = objectUrl;
        preview.classList.remove('hidden');
        preview.onload = () => URL.revokeObjectURL(objectUrl);

        if (caption) {
            caption.textContent = `Selected: ${file.name}. Save the form to upload.`;
        }
    });
}

document.addEventListener('DOMContentLoaded', initCampaignPosterPreview);

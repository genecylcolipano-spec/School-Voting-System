/**
 * Compress profile photo and video thumbnail on the student registration form
 * so oversized images are reduced before Laravel validation.
 */

const MAX_IMAGE_BYTES = 2 * 1024 * 1024;
const MAX_IMAGE_DIMENSION = 1920;

async function compressImageFile(file) {
    if (!file?.type?.match(/^image\/(jpeg|jpg|png|webp)$/i)) {
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

        const baseName = file.name.replace(/\.[^.]+$/, '') || 'talent-image';

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

function setStatus(input, message, isBusy = false) {
    const status = document.getElementById(
        input.name === 'photo' ? 'talent-photo-status' : 'talent-thumbnail-status',
    );

    if (!status) {
        return;
    }

    status.textContent = message;
    status.className = isBusy ? 'mt-1 text-xs text-cyan-300' : 'mt-1 text-xs text-slate-500';
}

async function optimizeInput(input) {
    const file = input.files?.[0];

    if (!file) {
        setStatus(input, '');

        return;
    }

    setStatus(input, 'Optimizing image…', true);

    const optimized = await compressImageFile(file);

    if (optimized !== file) {
        const transfer = new DataTransfer();
        transfer.items.add(optimized);
        input.files = transfer.files;
    }

    const sizeMb = (optimized.size / (1024 * 1024)).toFixed(2);
    setStatus(
        input,
        optimized.size > MAX_IMAGE_BYTES
            ? 'Image is still large; the server will compress it further on save.'
            : `Ready to upload (${sizeMb} MB).`,
    );
}

function initStudentTalentImageCompression() {
    const form = document.getElementById('student-talent-registration-form');

    if (!form) {
        return;
    }

    const inputs = form.querySelectorAll('input[type="file"][name="photo"], input[type="file"][name="thumbnail"]');
    const submit = form.querySelector('button[type="submit"]');

    inputs.forEach((input) => {
        input.addEventListener('change', () => {
            optimizeInput(input);
        });
    });

    form.addEventListener('submit', async (event) => {
        if (form.dataset.imagesReady === '1') {
            return;
        }

        event.preventDefault();

        if (submit) {
            submit.disabled = true;
            submit.textContent = 'Preparing images…';
        }

        for (const input of inputs) {
            await optimizeInput(input);
        }

        form.dataset.imagesReady = '1';

        if (submit) {
            submit.disabled = false;
            submit.textContent = 'Continue to Review';
        }

        form.requestSubmit(submit);
    });
}

document.addEventListener('DOMContentLoaded', initStudentTalentImageCompression);

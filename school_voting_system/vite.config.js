import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/admin-live-voting.css',
                'resources/css/admin-results.css',
                'resources/css/admin-talent-competition.css',
                'resources/js/app.js',
                'resources/js/passkey-auth.js',
                'resources/js/passkey-register.js',
                'resources/js/passkey-devices.js',
                'resources/js/passkey-recovery.js',
                'resources/js/passkey-admin-recovery.js',
                'resources/js/super-admin-dashboard.js',
                'resources/js/regular-admin-dashboard.js',
                'resources/js/admin-dashboard-live.js',
                'resources/js/admin-results.js',
                'resources/js/admin-talent-competition.js',
                'resources/js/admin-analytics-live.js',
                'resources/js/election-form.js',
                'resources/js/talent-competition-create.js',
                'resources/js/event-image-preview.js',
                'resources/js/campaign-poster-preview.js',
                'resources/js/student-talent-voting.js',
                'resources/js/notification-center.js',
                'resources/js/admin-confirm.js',
                'resources/js/admin-live-monitoring.js',
            ],
            refresh: true,
        }),
    ],
});

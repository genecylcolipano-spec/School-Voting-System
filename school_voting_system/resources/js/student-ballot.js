document.addEventListener('alpine:init', () => {
    Alpine.data('ballot', (config) => ({
        selections: { ...config.seed },
        locked: Object.keys(config.seed).map((k) => String(k)),
        names: { ...(config.seedNames || {}) },
        totalPositions: config.totalPositions,
        endsAt: config.endsAt,
        confirmOpen: false,
        mobileSummaryOpen: false,
        cd: { h: '--', m: '--', s: '--', closed: false, none: false },

        init() {
            this.tick();
            setInterval(() => this.tick(), 1000);
        },

        tick() {
            if (!this.endsAt) {
                this.cd = { ...this.cd, none: true, closed: false };
                return;
            }
            const diff = new Date(this.endsAt).getTime() - Date.now();
            if (diff <= 0) {
                this.cd = { h: '00', m: '00', s: '00', closed: true, none: false };
                return;
            }
            const totalSeconds = Math.floor(diff / 1000);
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;
            this.cd = {
                h: String(hours).padStart(2, '0'),
                m: String(minutes).padStart(2, '0'),
                s: String(seconds).padStart(2, '0'),
                closed: false,
                none: false,
            };
        },

        isLocked(catId) {
            return this.locked.includes(String(catId));
        },

        select(catId, candId, name) {
            if (this.isLocked(catId)) {
                return;
            }
            this.selections[String(catId)] = candId;
            this.names[String(catId)] = name;
        },

        isSelected(catId, candId) {
            return this.selections[String(catId)] === candId;
        },

        isCompleted(catId) {
            return this.selections[String(catId)] !== undefined;
        },

        selectionName(catId) {
            return this.names[String(catId)] || null;
        },

        get completedCount() {
            return Object.keys(this.selections).length;
        },

        get progressPercent() {
            if (this.totalPositions === 0) {
                return 0;
            }

            return Math.round((this.completedCount / this.totalPositions) * 100);
        },

        newSelectionEntries() {
            return Object.entries(this.selections).filter(([catId]) => !this.locked.includes(String(catId)));
        },

        canSubmit() {
            return this.completedCount >= this.totalPositions && this.newSelectionEntries().length > 0;
        },

        openConfirm() {
            if (!this.canSubmit()) {
                return;
            }
            this.mobileSummaryOpen = false;
            this.confirmOpen = true;
        },

        submitBallot() {
            this.$refs.ballotForm.submit();
        },
    }));
});

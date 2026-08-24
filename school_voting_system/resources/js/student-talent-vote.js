document.addEventListener('alpine:init', () => {
    Alpine.data('talentVoteConfirm', (options = {}) => ({
        confirmOpen: false,
        entryName: '',
        formEl: null,
        watchedIds: Object.fromEntries(
            (Array.isArray(options.watchedIds) ? options.watchedIds : []).map((id) => [String(id), true]),
        ),

        // Watch performance modal
        watchOpen: false,
        watch: {
            name: '',
            title: '',
            category: '',
            grade: '',
            embed: '',
            file: '',
        },

        // Participant detail modal
        viewOpen: false,
        participant: {},

        hasWatched(id) {
            return Boolean(this.watchedIds[String(id)]);
        },

        markWatched(id) {
            if (!id) {
                return;
            }

            this.watchedIds[String(id)] = true;
        },

        openConfirm(form, name) {
            this.formEl = form;
            this.entryName = name;
            this.confirmOpen = true;
        },

        submitVote() {
            if (this.formEl) {
                this.formEl.submit();
            }
        },

        openWatch(data) {
            this.watch = {
                name: data.name || '',
                title: data.title || '',
                category: data.category || '',
                grade: data.grade || '',
                embed: data.embed || '',
                file: data.file || '',
            };
            this.watchOpen = true;

            if (!data.viewUrl || !data.csrf) {
                this.markWatched(data.entryId);
                return;
            }

            fetch(data.viewUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': data.csrf,
                    Accept: 'application/json',
                },
            })
                .then((response) => {
                    if (response.ok) {
                        this.markWatched(data.entryId);
                    }
                })
                .catch(() => {});
        },

        closeWatch() {
            this.watchOpen = false;
            // Stop playback by clearing sources.
            this.watch.embed = '';
            this.watch.file = '';
        },

        openParticipant(data) {
            this.participant = data || {};
            this.viewOpen = true;
        },

        toggleFullscreen(refEl) {
            if (!refEl) {
                return;
            }

            if (document.fullscreenElement) {
                document.exitFullscreen();
            } else if (refEl.requestFullscreen) {
                refEl.requestFullscreen();
            }
        },
    }));
});

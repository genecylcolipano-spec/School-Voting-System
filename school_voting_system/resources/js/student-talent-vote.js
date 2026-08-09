document.addEventListener('alpine:init', () => {
    Alpine.data('talentVoteConfirm', () => ({
        confirmOpen: false,
        entryName: '',
        formEl: null,

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

            if (data.viewUrl && data.csrf) {
                fetch(data.viewUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': data.csrf,
                        Accept: 'application/json',
                    },
                }).catch(() => {});
            }
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

/**
 * Shared responsive popover for portal headers.
 * Desktop/tablet: fixed panel clamped to the viewport under the trigger.
 * Mobile: bottom sheet with backdrop (never clipped by overflow parents).
 */
export function responsivePopover(config = {}) {
    const mobileMax = Number(config.mobileMax ?? 639);

    return {
        open: false,
        isMobile: false,
        panelStyle: '',
        desktopClass: config.desktopClass ?? 'w-72 max-w-[calc(100vw-1rem)] rounded-2xl',
        mobileClass: config.mobileClass ?? 'inset-x-0 bottom-0 max-h-[min(85vh,36rem)] w-full rounded-t-2xl border-b-0',
        _mq: null,
        _onMq: null,
        _onReposition: null,
        _onDocPointer: null,

        init() {
            this._mq = window.matchMedia(`(max-width: ${mobileMax}px)`);
            this.isMobile = this._mq.matches;

            this._onMq = (event) => {
                this.isMobile = event.matches;
                if (this.open) {
                    this.$nextTick(() => this.position());
                }
            };
            this._mq.addEventListener('change', this._onMq);

            this._onReposition = () => {
                if (this.open && !this.isMobile) {
                    this.position();
                }
            };
            window.addEventListener('resize', this._onReposition);
            window.addEventListener('scroll', this._onReposition, true);

            this._onDocPointer = (event) => {
                if (!this.open) {
                    return;
                }

                const target = event.target;
                if (this.$refs.trigger?.contains(target) || this.$refs.panel?.contains(target)) {
                    return;
                }

                this.close();
            };
            document.addEventListener('pointerdown', this._onDocPointer, true);
        },

        destroy() {
            this._mq?.removeEventListener('change', this._onMq);
            window.removeEventListener('resize', this._onReposition);
            window.removeEventListener('scroll', this._onReposition, true);
            document.removeEventListener('pointerdown', this._onDocPointer, true);
        },

        toggle() {
            if (this.open) {
                this.close();
            } else {
                this.openMenu();
            }
        },

        openMenu() {
            this.isMobile = this._mq?.matches ?? window.innerWidth <= mobileMax;
            this.open = true;
            this.$nextTick(() => {
                this.position();
                // Re-measure after paint (panel height may change with content).
                requestAnimationFrame(() => this.position());
            });
        },

        close() {
            this.open = false;
            this.panelStyle = '';
        },

        position() {
            if (!this.open || this.isMobile || !this.$refs.trigger || !this.$refs.panel) {
                if (this.isMobile) {
                    this.panelStyle = '';
                }
                return;
            }

            const trigger = this.$refs.trigger.getBoundingClientRect();
            const panel = this.$refs.panel;
            const gap = 8;
            const margin = 8;
            const vw = window.innerWidth;
            const vh = window.innerHeight;

            // Temporarily clear max-height so we measure natural size.
            panel.style.maxHeight = '';
            const pw = panel.offsetWidth || 288;
            const ph = panel.offsetHeight || 240;

            let top = trigger.bottom + gap;
            const spaceBelow = vh - margin - top;
            const spaceAbove = trigger.top - gap - margin;

            if (ph > spaceBelow && spaceAbove > spaceBelow) {
                top = Math.max(margin, trigger.top - gap - Math.min(ph, spaceAbove));
            }

            let left = trigger.right - pw;
            if (config.align === 'start') {
                left = trigger.left;
            }

            if (left < margin) {
                left = margin;
            }
            if (left + pw > vw - margin) {
                left = Math.max(margin, vw - margin - pw);
            }

            const available = Math.max(160, vh - top - margin);
            const maxHeight = Math.min(available, Math.max(ph, 160));

            this.panelStyle = `top:${Math.round(top)}px;left:${Math.round(left)}px;width:${Math.round(pw)}px;max-height:${Math.round(maxHeight)}px;`;
        },
    };
}


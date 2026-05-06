window.timerComponentData = function ($wire) {
    return {
        initialized: false,

        timerActive: true,
        secondsRemaining: 0,
        interval: null,

        hasAlphaTab: $wire.entangle('hasAlphaTab'),
        alphaTabTex: $wire.entangle('alphaTabTex'),
        alphaTabTempo: $wire.entangle('alphaTabTempo'),
        alphaTabTimeSignature: $wire.entangle('alphaTabTimeSignature'),

        showCountdown: false,
        countdownNumber: 3,

        hasRenderedAlphaTab: false,
        initialSettings: {},

        init() {
            if (this.initialized) return;
            this.initialized = true;

            console.log('INIT RUNNING');

            this.secondsRemaining = this.$wire.get('timerDuration') * 60 || 0;

            if (this.hasAlphaTab) {
                this.renderAlphaTab();   // 👈 render immediately
                this.startCountdown();   // 👈 delay playback only
            } else {
                this.startTimers();
            }

            this.$wire.$on('stop-timer', () => this.cleanUp());
        },

        renderAlphaTab() {
            if (this.hasRenderedAlphaTab) return;

            this.hasRenderedAlphaTab = true;

            this.initialSettings = {
                tex: this.alphaTabTex,
                tempo: this.alphaTabTempo,
                timeSignature: this.alphaTabTimeSignature,
            };

            console.log('CALLING RENDER');

            window.alphaTabEngine
                .render('#alphaTab-container', this.initialSettings)
                .then(() => {
                    console.log('RENDER DONE');
                });
        },

        // ── Countdown ─────────────────
        startCountdown() {
            if (this.showCountdown) return;
            this.showCountdown = true;
            this.countdownNumber = 3;

            const cd = setInterval(() => {
                this.countdownNumber--;

                if (this.countdownNumber <= 0) {
                    clearInterval(cd);
                    this.showCountdown = false;

                    this.startTimers();
                }
            }, 1000);
        },

        // ── Timer + playback ──────────
        startTimers() {
            console.log('START TIMERS');

            this.interval = setInterval(() => {
                this.secondsRemaining--;

                if (this.secondsRemaining <= 0) {
                    this.cleanUp();
                    this.$wire.dispatch('timer-completed');
                }
            }, 1000);

            if (this.hasAlphaTab) {
                console.log('PLAYING ALPHATAB');
                window.alphaTabEngine.play();
            }
        },

        stopTimer() {
            this.$wire.stopTimer();
        },

        cleanUp() {
            if (this.interval) {
                clearInterval(this.interval);
                this.interval = null;
            }

            window.alphaTabEngine.stop();
            window.alphaTabEngine.destroy();

            this.timerActive = false;
        },
    };
};

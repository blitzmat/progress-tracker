window.timerComponentData = function ($wire) {
    return {
        timerActive: true,
        secondsRemaining: 0,
        interval: null,

        hasAlphaTab: $wire.entangle('hasAlphaTab'),
        alphaTabTex: $wire.entangle('alphaTabTex'),
        alphaTabTempo: $wire.entangle('alphaTabTempo'),
        alphaTabVolume: $wire.entangle('alphaTabVolume'),
        alphaTabTimeSignature: $wire.entangle('alphaTabTimeSignature'),

        showCountdown: false,
        countdownNumber: 3,
        hasRenderedAlphaTab: false,

        init() {
            if (this.initialized) return;
            this.initialized = true;

            this.secondsRemaining = this.$wire.get('timerDuration') * 60 || 0;

            if (this.hasAlphaTab) {
                this.renderAlphaTab(); // render immediately
                this.startCountdown(); // playback delayed
            } else {
                this.startTimers();
            }

            this.$wire.$on('stop-timer', () => this.cleanUp());

            this.$watch('alphaTabTempo', value => {
                const api = window.alphaTabEngine?.api;
                if (api) api.playbackSpeed = value / 120;
            });

            this.$watch('alphaTabVolume', value => {
                const api = window.alphaTabEngine?.api;
                if (api) api.masterVolume = value;
            });

        },

        async renderAlphaTab() {
            if (!this.hasAlphaTab || this.hasRenderedAlphaTab) return;

            this.hasRenderedAlphaTab = true;

            const settings = {
                tex: this.alphaTabTex,
                tempo: this.alphaTabTempo,
                volume: this.alphaTabVolume,
                timeSignature: this.alphaTabTimeSignature,
            };

            await window.alphaTabEngine.render(
                '#alphaTab-container',
                settings
            );
        },

        startCountdown() {
            if (this.showCountdown) return;

            this.showCountdown = true;
            this.countdownNumber = 3;

            const cd = setInterval(() => {
                this.countdownNumber--;

                // show GO! briefly
                if (this.countdownNumber < 0) {
                    clearInterval(cd);

                    this.showCountdown = false;

                    this.startTimers();
                }
            }, 1000);
        },

        startTimers() {
            this.clearIntervals();

            this.interval = setInterval(() => {
                this.secondsRemaining--;

                if (this.secondsRemaining <= 0) {
                    this.cleanUp();
                    this.$wire.dispatch('timer-completed');
                }
            }, 1000);

            // ONLY PLAY HERE
            if (this.hasAlphaTab) {
                window.alphaTabEngine.playOnce(this.alphaTabVolume);
            }
        },

        stopTimer() {
            this.$wire.stopTimer();
        },

        cleanUp() {
            this.clearIntervals();
            const api = window.alphaTabEngine?.api;
            if (api) {
                api.pause();
                api.destroy();
            }
            window.alphaTabEngine.destroy();
            this.timerActive = false;
        },

        clearIntervals() {
            if (this.interval) {
                clearInterval(this.interval);
                this.interval = null;
            }
        },

        destroy() {
            this.cleanUp();
        },
    };
};;

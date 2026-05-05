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

        init() {
            this.secondsRemaining = this.timerActive ? this.$wire.get('timerDuration') * 60 : 0;

            if (this.hasAlphaTab) {
                this.startCountdown();
            } else {
                this.startTimers();
            }

            this.$wire.$on('stop-timer', () => {
                this.cleanUp();
            });

            this.$watch('alphaTabTempo', () => {
                if (this.hasAlphaTab) this.restartAlphaTab();
            });
            this.$watch('alphaTabTimeSignature', () => {
                if (this.hasAlphaTab) this.restartAlphaTab();
            });
            this.$watch('alphaTabVolume', (vol) => {
                if (window.alphaTabEngine) window.alphaTabEngine.setVolume(vol);
            });
        },

        startCountdown() {
            if (this.showCountdown) return;
            this.showCountdown = true;
            this.countdownNumber = 3;
            let cd = setInterval(() => {
                this.countdownNumber--;
                if (this.countdownNumber <= 0) {
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

            if (this.hasAlphaTab) {
                const settings = {
                    tex: this.alphaTabTex,
                    tempo: this.alphaTabTempo,
                    volume: this.alphaTabVolume,
                    timeSignature: this.alphaTabTimeSignature,
                };
                window.alphaTabEngine.init('#alphaTab-container', settings);
            }
        },

        stopTimer() {
            this.$wire.stopTimer();
        },

        cleanUp() {
            this.clearIntervals();
            if (window.alphaTabEngine) window.alphaTabEngine.destroy();
            this.timerActive = false;
        },

        clearIntervals() {
            if (this.interval) {
                clearInterval(this.interval);
                this.interval = null;
            }
        },

        restartAlphaTab() {
            if (!this.hasAlphaTab) return;
            const settings = {
                tex: this.alphaTabTex,
                tempo: this.alphaTabTempo,
                volume: this.alphaTabVolume,
                timeSignature: this.alphaTabTimeSignature,
            };
            window.alphaTabEngine.init('#alphaTab-container', settings);
        },
    };
};

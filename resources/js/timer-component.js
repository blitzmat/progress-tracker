window.timerComponentData = function ($wire) {
    return {
        timerActive: true,
        secondsRemaining: 0,
        interval: null,

        hasMetronome: $wire.entangle('hasMetronome'),
        duration: $wire.entangle('timerDuration'),
        tempo: $wire.entangle('widgetTempo'),
        sound: $wire.entangle('widgetSound'),
        volume: $wire.entangle('widgetVolume'),
        timeSignature: $wire.entangle('widgetTimeSignature'),

        hasFingerWarmup: $wire.entangle('hasFingerWarmup'),
        fingerWarmupNoteType: $wire.entangle('fingerWarmupNoteType'),
        fingerWarmupPattern: $wire.entangle('fingerWarmupPattern'),
        fingerPatternArray: [],
        fingerPosition: 0,
        fingerWarmupInterval: null,

        showCountdown: false,
        countdownNumber: 3,

        init() {
            this.secondsRemaining = this.duration * 60;
            if (this.hasMetronome) {
                this.startCountdown();
            } else {
                this.startTimers();
            }

            this.$wire.$on('stop-timer', () => {
                this.cleanUp();
            });

            // Watchers for settings that require restarting alphaTab
            this.$watch('tempo', () => {
                if (this.hasFingerWarmup) this.restartAlphaTab();
            });
            this.$watch('timeSignature', () => {
                if (this.hasFingerWarmup) this.restartAlphaTab();
            });
            this.$watch('fingerWarmupNoteType', () => {
                if (this.hasFingerWarmup) this.restartAlphaTab();
            });
            this.$watch('fingerWarmupPattern', () => {
                if (this.hasFingerWarmup) this.restartAlphaTab();
            });

            // Volume changes can be applied directly without restart
            this.$watch('volume', (vol) => {
                if (window.fingerWarmup && window.fingerWarmup.setVolume) {
                    window.fingerWarmup.setVolume(vol);
                }
            });
        },

        // ── Countdown (visual only) ────────────────────
        startCountdown() {
            if (this.showCountdown) return;
            this.showCountdown = true;
            this.countdownNumber = 3;
            let cd = setInterval(() => {
                this.countdownNumber--;
                if (this.countdownNumber > 0) {
                    // no beep – just visual
                } else {
                    clearInterval(cd);
                    this.showCountdown = false;
                    this.startTimers();
                }
            }, 1000);
        },

        // ── Timer + Widget start ──────────────────────
        startTimers() {
            this.clearIntervals();
            this.interval = setInterval(() => {
                this.secondsRemaining--;
                if (this.secondsRemaining <= 0) {
                    this.cleanUp();
                    this.$wire.dispatch('timer-completed');
                }
            }, 1000);

            if (this.hasFingerWarmup) {
                const settings = {
                    pattern: this.fingerWarmupPattern,
                    noteType: this.fingerWarmupNoteType,
                    tempo: this.tempo,
                    timeSignature: this.timeSignature,
                    volume: this.volume,
                };
                window.fingerWarmup.init('#finger-warmup-container', settings);
            }
        },

        // ── Stop & cleanup ─────────────────────────────
        stopTimer() {
            this.$wire.stopTimer();
        },
        cleanUp() {
            this.clearIntervals();
            if (window.fingerWarmup) window.fingerWarmup.stop();
            this.timerActive = false;
        },
        clearIntervals() {
            if (this.interval) {
                clearInterval(this.interval);
                this.interval = null;
            }
        },

        // ── Restart alphaTab on setting change ────────
        restartAlphaTab() {
            if (!this.hasFingerWarmup) return;
            const settings = {
                pattern: this.fingerWarmupPattern,
                noteType: this.fingerWarmupNoteType,
                tempo: this.tempo,
                timeSignature: this.timeSignature,
                volume: this.volume,
            };
            window.fingerWarmup.updateSettings(settings);
        }
    };
};

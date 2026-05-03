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

        audioCtx: null,
        metronomeInterval: null,
        showCountdown: false,
        countdownNumber: 3,
        currentBeat: 1,

        hasFingerWarmup: $wire.entangle('hasFingerWarmup'),
        fingerWarmupNoteType: $wire.entangle('fingerWarmupNoteType'),
        fingerWarmupPattern: $wire.entangle('fingerWarmupPattern'),
        fingerPatternArray: [],
        fingerPosition: 0,
        fingerWarmupInterval: null,

        sounds: {
            beep: { type: 'sine', freq: 880, dur: 0.05, gain: 0.1 },
            click: { type: 'square', freq: 1000, dur: 0.02, gain: 0.07 },
            woodblock: { type: 'triangle', freq: 800, dur: 0.03, gain: 0.12 },
            pulse: { type: 'sine', freq: 660, dur: 0.08, gain: 0.08 },
        },

        init() {
            // Build the finger pattern array right away (even if warm‑up is off)
            this.updateFingerPattern();

            this.secondsRemaining = this.duration * 60;
            if (this.hasMetronome) {
                this.startCountdown();
            } else {
                this.startTimers();
            }

            this.$wire.$on('stop-timer', () => {
                this.cleanUp();
            });
            this.$watch('tempo', (t) => {
                if (this.hasMetronome) this.syncWidgets();
            });
            this.$watch('sound', (s) => {
                if (this.hasMetronome) this.syncWidgets();
            });
            this.$watch('timeSignature', () => {
                if (this.hasMetronome) this.syncWidgets();
            });
            this.$watch('fingerWarmupNoteType', () => {
                if (this.hasFingerWarmup) this.syncWidgets();
            });
            this.$watch('fingerWarmupPattern', () => {
                if (this.hasFingerWarmup) {
                    this.updateFingerPattern();
                    this.syncWidgets();
                }
            });
        },

        startCountdown() {
            if (this.showCountdown) return;
            this.showCountdown = true;
            this.countdownNumber = 3;
            this.playCountdownBeep();
            let cd = setInterval(() => {
                this.countdownNumber--;
                if (this.countdownNumber > 0) {
                    this.playCountdownBeep();
                } else {
                    clearInterval(cd);
                    // No more beep here – just hide overlay and start
                    this.showCountdown = false;
                    this.startTimers();
                }
            }, 1000);
        },

        playCountdownBeep() {
            if (!this.audioCtx) this.ensureAudioContext();
            if (this.audioCtx) {
                let o = this.audioCtx.createOscillator();
                let g = this.audioCtx.createGain();
                g.gain.value = 0.1;
                o.type = 'sine';
                o.frequency.value = this.countdownNumber > 0 ? 880 : 1320;
                o.connect(g);
                g.connect(this.audioCtx.destination);
                o.start();
                o.stop(this.audioCtx.currentTime + 0.1);
            }
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

            if (this.hasMetronome) {
                this.ensureAudioContext();
                // Play the first beat immediately (accented)
                this.currentBeat = 1;
                this.playMetronomeBeep(this.sound, true);
                // Then start the repeating interval for the remaining beats
                const numerator = parseInt(this.timeSignature.split('/')[0]);
                this.metronomeInterval = setInterval(() => {
                    this.currentBeat = this.currentBeat >= numerator ? 1 : this.currentBeat + 1;
                    const isAccent = this.currentBeat === 1;
                    this.playMetronomeBeep(this.sound, isAccent);
                }, 60000 / this.tempo);
            }

            if (this.hasFingerWarmup) {
                this.startFingerWarmup();   // shows first finger immediately, then cycles
            }
        },
        stopTimer() {
            this.$wire.stopTimer();
        },
        cleanUp() {
            this.clearIntervals();
            this.stopMetronome();
        },
        clearIntervals() {
            if (this.interval) {
                clearInterval(this.interval);
                this.interval = null;
            }
        },
        // Synchronise both widgets from beat 1
        syncWidgets() {
            if (!this.timerActive) return;

            // Always rebuild the pattern array (defensive)
            this.updateFingerPattern();

            this.stopMetronome();
            this.stopFingerWarmup();

            if (this.hasMetronome) {
                this.ensureAudioContext();
                this.currentBeat = 1;
                this.playMetronomeBeep(this.sound, true);
                const numerator = parseInt(this.timeSignature.split('/')[0]);
                this.metronomeInterval = setInterval(() => {
                    this.currentBeat = this.currentBeat >= numerator ? 1 : this.currentBeat + 1;
                    const isAccent = this.currentBeat === 1;
                    this.playMetronomeBeep(this.sound, isAccent);
                }, 60000 / this.tempo);
            }

            if (this.hasFingerWarmup) {
                this.startFingerWarmup();
            }
        },

        ensureAudioContext() {
            if (!this.audioCtx) this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            if (this.audioCtx.state === 'suspended') this.audioCtx.resume();
        },
        playMetronomeBeep(sound, isAccent = false) {
            if (!this.audioCtx) return;
            const base = this.sounds[sound] || this.sounds['beep'];
            const o = this.audioCtx.createOscillator();
            const g = this.audioCtx.createGain();
            g.gain.value = base.gain * this.volume;
            o.type = base.type;
            o.frequency.value = isAccent ? base.freq * 1.5 : base.freq;
            o.connect(g);
            g.connect(this.audioCtx.destination);
            o.start();
            o.stop(this.audioCtx.currentTime + base.dur);
        },
        startMetronome(tempo, sound) {
            this.stopMetronome();
            if (!this.audioCtx) return;
            // Play first accented beat right away
            this.currentBeat = 1;
            this.playMetronomeBeep(sound, true);
            const numerator = parseInt(this.timeSignature.split('/')[0]);
            this.metronomeInterval = setInterval(() => {
                this.currentBeat = this.currentBeat >= numerator ? 1 : this.currentBeat + 1;
                const isAccent = this.currentBeat === 1;
                this.playMetronomeBeep(sound, isAccent);
            }, 60000 / tempo);
        },
        stopMetronome() {
            if (this.metronomeInterval) {
                clearInterval(this.metronomeInterval);
                this.metronomeInterval = null;
            }
        },
        // ── Finger Warm‑up ───────────────────
        getSubdivisionsPerBeat() {
            const map = { quarter: 1, eighth: 2, sixteenth: 4, 'thirty-second': 8 };
            return map[this.fingerWarmupNoteType] || 1;
        },
        startFingerWarmup() {
            this.stopFingerWarmup();
            if (!this.hasFingerWarmup) return;

            // Ensure we have a valid pattern array (fallback to 1-2-3-4)
            if (!this.fingerPatternArray || this.fingerPatternArray.length === 0) {
                this.fingerWarmupPattern = this.fingerWarmupPattern || '1-2-3-4';
                this.updateFingerPattern();
            }

            const subdivisions = this.getSubdivisionsPerBeat();
            const intervalMs = (60000 / this.tempo) / subdivisions;
            this.fingerPosition = 0;
            const length = this.fingerPatternArray.length;
            this.fingerWarmupInterval = setInterval(() => {
                this.fingerPosition = (this.fingerPosition + 1) % length;
            }, intervalMs);
        },
        stopFingerWarmup() {
            if (this.fingerWarmupInterval) {
                clearInterval(this.fingerWarmupInterval);
                this.fingerWarmupInterval = null;
            }
            this.fingerPosition = 0;
        },
        updateFingerPattern() {
            this.fingerPatternArray = this.fingerWarmupPattern.split('-').map(Number);
        },

    };
};

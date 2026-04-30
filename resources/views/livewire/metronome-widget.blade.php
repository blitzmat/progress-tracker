<!-- Left Column: Practice Timer -->
<div class="lg:col-span-1" x-data="{
    timerActive: @entangle('timerActive'),
    secondsRemaining: 0,
    interval: null,
    // Metronome state (entangled with Livewire)
    hasMetronome: @entangle('hasMetronome'),
    tempo: @entangle('widgetTempo'),
    sound: @entangle('widgetSound'),
    volume: @entangle('widgetVolume'),
    // Audio for metronome
    audioCtx: null,
    metronomeInterval: null,
    // Countdown state
    showCountdown: false,
    countdownNumber: 3,

    // Sound definitions (same as before)
    sounds: {
        beep: { type: 'sine', freq: 880, dur: 0.05, gain: 0.1 },
        click: { type: 'square', freq: 1000, dur: 0.02, gain: 0.07 },
        woodblock: { type: 'triangle', freq: 800, dur: 0.03, gain: 0.12 },
        pulse: { type: 'sine', freq: 660, dur: 0.08, gain: 0.08 },
    },

    init() {
        // Existing timer event listeners
        this.$wire.$on('start-timer', (event) => {
            this.secondsRemaining = event.duration;
            this.startTimer();
        });
        this.$wire.$on('stop-timer', () => {
            this.stopTimer();
        });

        // Also stop metronome when timer stops
        this.$watch('timerActive', (active) => {
            if (!active) this.stopMetronome();
        });
    },

    // --- Countdown logic ---
    triggerCountdown() {
        // Prevent multiple clicks
        if (this.showCountdown) return;
        this.showCountdown = true;
        this.countdownNumber = 3;
        this.playCountdownBeep();
        let countdownInterval = setInterval(() => {
                    this.countdownNumber--;
                    if (this.countdownNumber > 0) {
                        this.playCountdownBeep();
                    } else {
                        clearInterval(countdownInterval);
                        // "Go!" this.playCountdownBeep(); this.showCountdown=false; // Start the
    actual timer (Livewire call) this.$wire.startTimer(); // Start metronome if enabled if (this.hasMetronome) {
    this.ensureAudioContext(); this.startMetronome(this.tempo, this.sound); } } }, 1000); }, playCountdownBeep() { if
    (!this.audioCtx) this.ensureAudioContext(); if (this.audioCtx) { const osc=this.audioCtx.createOscillator(); const
    gain=this.audioCtx.createGain(); gain.gain.value=0.1; osc.type = 'sine'; osc.frequency.value=this.countdownNumber> 0
    ? 880 : 1320; // higher for "Go"
    osc.connect(gain);
    gain.connect(this.audioCtx.destination);
    osc.start();
    osc.stop(this.audioCtx.currentTime + 0.1);
    }
    },

    // --- Metronome audio (copied from earlier) ---
    ensureAudioContext() {
    if (!this.audioCtx) {
    this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
    if (this.audioCtx.state === 'suspended') {
    this.audioCtx.resume();
    }
    },

    startMetronome(tempo, sound) {
    this.stopMetronome();
    if (!this.audioCtx) return;
    const beepFunc = () => {
    const s = this.sounds[sound] || this.sounds['beep'];
    const osc = this.audioCtx.createOscillator();
    const gain = this.audioCtx.createGain();
    gain.gain.value = s.gain * this.volume;
    osc.type = s.type;
    osc.frequency.value = s.freq;
    osc.connect(gain);
    gain.connect(this.audioCtx.destination);
    osc.start();
    osc.stop(this.audioCtx.currentTime + s.dur);
    };
    this.metronomeInterval = setInterval(beepFunc, 60000 / tempo);
    },

    stopMetronome() {
    if (this.metronomeInterval) {
    clearInterval(this.metronomeInterval);
    this.metronomeInterval = null;
    }
    },

    // --- Timer methods (your existing ones) ---
    startTimer() {
    this.stopTimer(); // clear any previous
    this.interval = setInterval(() => {
    this.secondsRemaining--;
    const display = document.getElementById('timer-display');
    if (display) {
    display.textContent =
    Math.floor(this.secondsRemaining / 60) + ':' +
    (this.secondsRemaining % 60).toString().padStart(2, '0');
    }
    if (this.secondsRemaining <= 0) { this.stopTimer(); this.$wire.dispatch('timer-completed'); } }, 1000); },
        stopTimer() { if (this.interval) { clearInterval(this.interval); this.interval=null; } this.stopMetronome(); }
        }">
        <div class="bg-white dark:bg-gray-700 rounded-lg p-3 shadow-sm">
            <!-- Top row: Metronome label and Play/Stop button -->
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Metronome</span>
                <button wire:click="togglePlay" class="px-3 py-1 text-sm rounded text-white font-medium"
                    :class="playing ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600'">
                    <span x-text="playing ? 'Stop' : 'Start'"></span>
                </button>
            </div>

            <!-- Controls: BPM, Sound, Volume -->
            <div class="space-y-2">
                <!-- BPM -->
                <div class="flex items-center space-x-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400 w-10">BPM</span>
                    <input type="number" wire:model.live.debounce.300ms="tempo" min="40" max="240"
                        class="w-16 border border-gray-300 dark:border-gray-500 dark:bg-gray-600 dark:text-white rounded px-2 py-1 text-sm">
                </div>

                <!-- Sound selector -->
                <div class="flex items-center space-x-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400 w-10">Sound</span>
                    <select wire:model.live="sound"
                        class="border border-gray-300 dark:border-gray-500 dark:bg-gray-600 dark:text-white rounded px-2 py-1 text-sm">
                        <option value="beep">Beep</option>
                        <option value="click">Click</option>
                        <option value="woodblock">Wood</option>
                        <option value="pulse">Pulse</option>
                    </select>
                </div>

                <!-- Volume slider -->
                <div class="flex items-center space-x-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400 w-10">Volume</span>
                    <input type="range" wire:model.live="volume" min="0" max="1" step="0.01"
                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-600">
                    <span class="text-xs text-gray-500 dark:text-gray-400 w-8"
                        x-text="Math.round(volume * 100) + '%'"></span>
                </div>
            </div>
        </div>
</div>

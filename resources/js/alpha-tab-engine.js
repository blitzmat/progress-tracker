import * as alphaTab from '@coderline/alphatab';

console.log('alphaTabEngine loaded');

const engine = {
    api: null,
    _playRequested: false,   // prevents duplicate play calls
    _soundfontLoaded: false,

    async render(container, settings) {
        console.log('RENDER CALLED');

        const tex = `\\tempo ${settings.tempo} \\track "Guitar" \\staff \\ts ${settings.timeSignature.replace('/', ' ')} ${settings.tex}`;

        // destroy old instance completely
        this.destroy();

        const containerEl = document.querySelector(container);
        if (!containerEl) return;

        this.api = new alphaTab.AlphaTabApi(containerEl, {
            core: {
                engine: 'html5',            // required for audio
            },
            display: {
                layoutMode: 'Horizontal',
                staveProfile: 'Default',
            },
            player: {
                enablePlayer: true,
                enableCursor: true,
                soundFont: '/font/sonivox.sf2',
                scrollElement: containerEl,   // ← this makes the cursor move
            },
            fontDirectory: window.location.origin + '/font/',
        });

        return new Promise((resolve, reject) => {
            // notation rendered
            this.api.renderFinished.on(() => {
                console.log('RENDER FINISHED');
            });

            // audio/player fully ready
            this.api.playerReady.on(() => {
                console.log('PLAYER READY');
                resolve();
            });

            this.api.soundFontLoad.on(() => {
                console.log('SOUNDFONT LOADED');
                this._soundfontLoaded = true;
            });

            this.api.playerStateChanged.on((s) => {
                console.log('PLAYER STATE:', s);
            });

            this.api.error.on((error) => {
                console.error('[AlphaTab Error]', error);
                reject(error);
            });

            // IMPORTANT:
            // use tex() AFTER all listeners registered
            this.api.tex(tex);
        });
    },

    // Call this instead of play() – it waits for the player to be ready
    playOnce(volume) {
        console.log('playOnce called', 'api:', !this.api, '_playRequested:', this._playRequested, 'isPlaying:', this.api.playerState == alphaTab.synth.PlayerState.Playing);
        if (!this.api || this._playRequested || this.api.playerState == alphaTab.synth.PlayerState.Playing) return;
        this._playRequested = true;

        this.api.masterVolume = volume ?? 0.8;

        // If the SoundFont is already decoded, start immediately
        if (this._soundfontLoaded) {
            this.api.play();
            return;
        }

        // Otherwise wait for the SoundFont to finish loading
        const listener = () => {
            if (this.api && !this.api.isPlaying) {
                this.api.play();
            }
            // Clean up to avoid memory leaks
            if (this.api) {
                this.api.soundFontLoaded.off(listener);
            }
        };
        this.api.soundFontLoaded.on(listener);
    },

    // Basic controls
    play() {
        if (!this.api) return;
        if (!this.api.isPlaying) this.api.play();
    },

    stop() {
        if (!this.api) return;
        this.api.stop();
        this._playRequested = false;
    },

    destroy() {
        if (!this.api) return;
        this.api.destroy();
        this.api = null;
        this._playRequested = false;
    },
};

window.alphaTabEngine = engine;

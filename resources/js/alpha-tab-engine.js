import * as alphaTab from '@coderline/alphatab';

console.log('alphaTabEngine loaded');

const engine = {
    api: null,
    _playRequested: false,   // prevents duplicate play calls

    async render(container, settings) {
        console.log('RENDER CALLED');

        const tex = `\\tempo ${settings.tempo} \\track "Guitar" \\staff \\ts ${settings.timeSignature.replace('/', ' ')} ${settings.tex}`;

        // destroy old instance completely
        if (this.api) {
            this.api.destroy();
            this.api = null;
            this._playRequested = false;
        }

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

        return new Promise((resolve) => {
            this.api.renderFinished.on(() => {
                console.log('RENDER FINISHED');
                resolve();
            });

            this.api.tex(tex);
        });
    },

    // Call this instead of play() – it waits for the player to be ready
    playOnce(volume) {
        console.log('playOnce called, _playRequested:', this._playRequested, 'isPlaying:', this.api?.isPlaying);
        if (!this.api || this._playRequested || this.api.isPlaying) return;
        this._playRequested = true;

        this.api.masterVolume = volume ?? 0.8;

        // If the SoundFont is already decoded, start immediately
        if (this.api.isSoundFontLoaded) {
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

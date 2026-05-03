import * as alphaTab from '@coderline/alphatab';

let api = null;
let containerEl = null;
let isActive = false;

// ── Map finger numbers to frets on the 6th string ──────
function patternToFrets(patternStr) {
    const numbers = patternStr.split('-').map(Number);
    return numbers.map(num => ({ fret: num, string: 6 }));
}

// ── Generate alphaTex for tab + standard notation ──────
function generateAlphaTex(pattern, noteType, tempo, timeSignature) {
    const frets = patternToFrets(pattern);
    const noteChar = { quarter: '4', eighth: '8', sixteenth: '16', 'thirty-second': '32' }[noteType] || '4';
    const timesig = timeSignature.replace('/', ' ');

    let melody = frets.map(f => `${f.fret}.${f.string}`).join(' ');
    melody = `${melody} | ${melody} | ${melody} | ${melody}`;

    return [
        `\\tempo ${tempo}`,
        `\\ts ${timesig}`,
        `\\tuning E4 B3 G3 D3 A2 E2`,
        `\\staff{score tabs}`,
        `:${noteChar} ${melody}`,
    ].join('\n');
}

// ── Wait for player to be ready (polling) ──────────────
async function waitForPlayerReady() {
    return new Promise((resolve) => {
        const check = () => {
            if (api && api.player && api.player.isReady) {
                console.log('[finger-warmup] player is ready');
                resolve();
            } else {
                setTimeout(check, 100);
            }
        };
        check();
    });
}

// ── Initialize alphaTab ─────────────────────────────────
async function initAlphaTab(containerSelector, settings) {
    const { pattern, noteType, tempo, timeSignature, volume } = settings;

    // Destroy previous instance
    if (api) {
        api.stop();
        api.destroy();
        api = null;
    }

    containerEl = document.querySelector(containerSelector);
    containerEl.innerHTML = '';

    const tex = generateAlphaTex(pattern, noteType, tempo, timeSignature);
    const fontDir = window.location.origin + '/font/';
    const soundFont = window.location.origin + '/font/sonivox.sf2';

    api = new alphaTab.AlphaTabApi(containerEl, {
        core: {
            useWorkers: false,
        },
        display: {
            staveProfile: 'Default',
            layoutMode: 'Horizontal',
        },
        player: {
            enablePlayer: true,
            enableCursor: true,
            soundFont: soundFont,
        },
        fontDirectory: fontDir,
    });

    try {
        // Load the score
        if (typeof api.tex === 'function') {
            api.tex(tex);
        } else {
            api.render();
        }

        // Wait for player to be ready (polling)
        await waitForPlayerReady();

        // Start playback
        api.metronomeVolume = volume ?? 0.5;
        api.play();
        isActive = true;
        console.log('[finger-warmup] playback started');
    } catch (err) {
        console.error('[finger-warmup] init error:', err);
    }
}

// ── Destroy ─────────────────────────────────────────────
function destroy() {
    if (api) {
        api.stop();
        api.destroy();
        api = null;
    }
    containerEl = null;
    isActive = false;
}

// ── Public update ───────────────────────────────────────
async function updateSettings(settings) {
    if (!api) return;
    // For any change, restart from scratch
    await initAlphaTab('#finger-warmup-container', settings);
}

// ── Expose to window ────────────────────────────────────
window.fingerWarmup = {
    init: initAlphaTab,
    updateSettings,
    destroy,
    setVolume: (vol) => { if (api) api.metronomeVolume = vol; },
    stop: () => { if (api) api.stop(); },
    isActive: () => isActive,
};

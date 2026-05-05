import * as alphaTab from '@coderline/alphatab';

let api = null;
let containerEl = null;
let isActive = false;

async function waitForPlayerReady() {
    return new Promise((resolve) => {
        const check = () => {
            if (api && api.player && api.player.isReady) {
                resolve();
            } else {
                setTimeout(check, 100);
            }
        };
        check();
    });
}

async function initAlphaTab(containerSelector, settings) {
    const { tex, tempo, volume, timeSignature } = settings;

    if (api) {
        api.stop();
        api.destroy();
        api = null;
    }

    containerEl = document.querySelector(containerSelector);
    containerEl.innerHTML = '';

    const fullTex = `\\tempo ${tempo}\n\\ts ${timeSignature.replace('/', ' ')}\n${tex}`;
    const fontDir = window.location.origin + '/font/';
    const soundFont = window.location.origin + '/font/sonivox.sf2';

    api = new alphaTab.AlphaTabApi(containerEl, {
        core: { useWorkers: false },
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

    if (typeof api.tex === 'function') {
        api.tex(fullTex);
    } else {
        api.render();
    }

    await waitForPlayerReady();

    api.metronomeVolume = volume ?? 0.5;
    api.play();
    isActive = true;
}

function destroy() {
    if (api) {
        api.stop();
        api.destroy();
        api = null;
    }
    containerEl = null;
    isActive = false;
}

function setVolume(vol) {
    if (api) api.metronomeVolume = vol;
}

function updateSettings(settings) {
    initAlphaTab('#alphaTab-container', settings);
}

window.alphaTabEngine = {
    init: initAlphaTab,
    destroy,
    setVolume,
    updateSettings,
    isActive: () => isActive,
};

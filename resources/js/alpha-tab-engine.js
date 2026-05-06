let api = null;
let containerEl = null;
let alphaTabModule = null;
let rendered = false;

async function getAlphaTab() {
    if (!alphaTabModule) {
        alphaTabModule = await import('@coderline/alphatab');
    }
    return alphaTabModule;
}

async function render(containerSelector, settings) {
    if (api) {
        console.log('Already initialized, skipping render');
        return;
    }
    console.log('RENDER CALLED');
    const { tex, tempo, timeSignature } = settings;

    const fullTex = `
\\tempo ${tempo}
\\ts ${timeSignature.replace('/', ' ')}
${tex}
    `;

    containerEl = document.querySelector(containerSelector);
    if (!containerEl) return;

    containerEl.innerHTML = '';

    const alphaTab = await getAlphaTab();

    api = new alphaTab.AlphaTabApi(containerEl, {
        core: { useWorkers: false },
        display: {
            staveProfile: 'Default',
            layoutMode: 'Horizontal',
        },
        player: {
            enablePlayer: true,
            enableCursor: true,
            soundFont: '/font/sonivox.sf2', // 🔊 REQUIRED
        },
        fontDirectory: '/font/',
    });

    await api.ready;

    api.tex(fullTex);

    rendered = true;
}

function play() {
    if (!api) return;

    // 🧨 Prevent double playback
    if (api.isPlaying) {
        console.log('Already playing, skipping...');
        return;
    }

    // 🧹 Always reset before play (prevents overlap)
    try {
        api.stop();
    } catch (e) { }

    console.log('START PLAYBACK');

    api.play();
}

function pause() {
    if (api) {
        api.pause();
    }
}

function stop() {
    if (api) {
        api.stop();
    }
}

function destroy() {
    rendered = false;

    if (api) {
        try { api.destroy(); } catch (e) { }
        api = null;
    }
}

window.alphaTabEngine = {
    render,
    play,
    pause,
    stop,
    destroy,
};

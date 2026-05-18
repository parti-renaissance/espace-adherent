const NATIVE_HLS_MIME = 'application/vnd.apple.mpegurl';

const attachHlsPlayer = (video) => {
    const src = video.dataset.hlsSrc;
    if (!src) {
        return;
    }

    if (video.canPlayType(NATIVE_HLS_MIME)) {
        video.src = src;
        return;
    }

    import(/* webpackChunkName: "hls" */ 'hls.js').then(({ default: Hls }) => {
        if (!Hls.isSupported()) {
            video.outerHTML = '<em>Lecteur HLS non supporté par ce navigateur.</em>';
            return;
        }

        const hls = new Hls();
        hls.loadSource(src);
        hls.attachMedia(video);
    });
};

const setupVideoPlayer = () => {
    document.querySelectorAll('video[data-hls-src]').forEach(attachHlsPlayer);
};

export default setupVideoPlayer;

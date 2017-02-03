import stackblur from 'stackblur-canvas';

/*
 * Progressive background images
 */
export default () => {
    const imgToCanvas = (img) => {
        const canvas = document.createElement('canvas');
        canvas.width = img.naturalWidth;
        canvas.height = img.naturalHeight;

        canvas.getContext('2d').drawImage(img, 0, 0);

        return canvas;
    };

    findAll(document, '.progressive-background').forEach((element) => {
        const sdSrc = element.dataset.sd;
        const hdSrc = element.dataset.hd;
        const bgPrefix = element.dataset.bgPrefix ? `${element.dataset.bgPrefix}, ` : '';

        const sd = new Image();
        const hd = new Image();

        sd.src = sdSrc;

        on(sd, 'load', () => {
            const canvas = imgToCanvas(sd);

            stackblur.canvasRGB(canvas, 0, 0, canvas.width, canvas.height, 10);

            element.style['background-image'] = `${bgPrefix}url(${canvas.toDataURL('image/png')})`;

            // Load high quality version after low quality one
            hd.src = hdSrc;

            on(hd, 'load', () => {
                element.style['background-image'] = `${bgPrefix}url(${hd.src})`;
            });
        });
    });
};

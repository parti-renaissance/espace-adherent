export default(api) => {
    const emojiCanvas = dom('#profil-emoji');

    if (!emojiCanvas) {
        return;
    }

    const emojiField = dom('#citizen_project_media_emoji');
    const downloadButton = dom('#citizen_project_image_download_button');
    const emojiCanvasContext = emojiCanvas.getContext('2d');
    const coverImage = dom('#cover-image');

    const drawEmoji = (event) => {
        const emoji = event.currentTarget.value;
        emojiCanvasContext.font = '170px Segoe UI Emoji';
        emojiCanvasContext.textBaseline = 'top';
        emojiCanvasContext.fillText(emoji, 0, 0);
    };

    const downloadImages = (event) => {
        event.preventDefault();

        const link = document.createElement('a');

        link.setAttribute('download', 'profile.png');
        link.setAttribute('href', emojiCanvas.toDataURL('image/png').replace('image/png', 'image/octet-stream'));
        link.click();

        link.setAttribute('download', 'cover.png');
        link.setAttribute('href', coverImage.src.replace('image/png', 'image/octet-stream'));
        link.click();
    };

    on(emojiField, 'change', drawEmoji);
    on(downloadButton, 'click', downloadImages);

    emojiField.dispatchEvent(new Event('change'));
};

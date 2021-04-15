export default (api) => {
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
        emojiCanvasContext.padding = '10px';
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

export const previewHandler = () => {
    const imageUpload = dom('#citizen_project_media_backgroundImage');

    if (!imageUpload) {
        return;
    }

    const imagePreview = ({ target }) => {
        if (target.files && target.files[0]) {
            const reader = new FileReader();

            reader.onload = (e) => {
                const thumbnail = new Image();
                thumbnail.src = e.target.result;
                thumbnail.classList.add('image-thumbnail');

                const preview = dom('#image-thumbnail');
                preview.classList.add('has-thumbnail');
                while (preview.firstElementChild) {
                    preview.firstElementChild.remove();
                }
                preview.appendChild(thumbnail);
            };

            reader.readAsDataURL(target.files[0]);
        }
    };

    on(imageUpload, 'change', imagePreview);
};

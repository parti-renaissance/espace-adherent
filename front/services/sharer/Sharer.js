export default class Sharer {
    constructor(shareDialogFactory) {
        this._shareDialogFactory = shareDialogFactory;
    }

    share(type, link, title) {
        if ('email' === type) {
            window.location = `mailto:?subject=${title}&body=${link}`;
            return false;
        }

        const dialog = this._shareDialogFactory.createShareLink(type, link, title);

        const windowLeft = window.screenLeft ? window.screenLeft : window.screenX;
        const windowTop = window.screenTop ? window.screenTop : window.screenY;
        const left = windowLeft + ((window.innerWidth / 2) - (dialog.getWidth() / 2));
        const top = windowTop + ((window.innerHeight / 2) - (dialog.getHeight() / 2));

        const popup = window.open(
            dialog.getUrl(),
            title,
            `width=${dialog.getWidth()}, height=${dialog.getHeight()}, top=${top}, left=${left}`
        );

        if (window.focus) {
            popup.focus();
        }

        return false;
    }
}

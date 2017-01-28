import ShareDialog from './ShareDialog';

export default class ShareLinkFactory {
    createShareLink(type, link, title) {
        if ('facebook' === type) {
            return new ShareDialog(
                `https://www.facebook.com/dialog/share?app_id=620675918119463&display=popup&href=${link}`,
                555,
                450
            );
        }

        if ('twitter' === type) {
            return new ShareDialog(
                `https://twitter.com/share?url=${link}&text=${title}&via=enmarchefr`,
                600,
                450
            );
        }

        return null;
    }
}

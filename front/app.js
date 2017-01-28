import './style/app.scss';

import Container from './src/Container';
import ShareDialogFactory from './src/sharer/ShareDialogFactory';
import Sharer from './src/sharer/Sharer';

class App {
    constructor() {
        this._di = null;
    }

    run(parameters) {
        let di = new Container(parameters);
        this._di = di;

        /*
         * Sharer
         */
        di.set('sharer', () => {
            return new Sharer(di.get('sharer.dialog_factory'));
        });

        di.set('sharer.dialog_factory', () => {
            return new ShareDialogFactory();
        });

        /*
         * Top banner
         */
        if (typeof Cookies.get('banner_donation') === 'undefined') {
            let banner = $('#header-banner'),
                bannerButton = $('#header-banner-close-btn');

            banner.show();

            bannerButton.click(() => {
                banner.hide();
                Cookies.set('banner_donation', 'dismiss', { expires: 1 });
            });
        }
    }

    share(type, url, title) {
        this._di.get('sharer').share(type, url, title);
    }
}

window.App = new App();

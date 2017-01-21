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
        $('#header-banner-close-btn').click(() => {
            $('#header-banner').hide();
        });
    }

    share(type, url, title) {
        this._di.get('sharer').share(type, url, title);
    }
}

window.App = new App();

import './style/app.scss';

import Container from './services/Container';
import registerServices from './services';

// Listeners
import amountChooser from './listeners/amount-chooser';
import cookiesConsent from './listeners/cookies-consent';
import donationBanner from './listeners/donation-banner';
import donationAmount from './listeners/donation-amount';

class App {
    constructor() {
        this._di = null;
        this._listeners = [
            cookiesConsent,
            donationBanner,
            amountChooser,
            donationAmount,
        ];
    }

    addListener(listener) {
        this._listeners.push(listener);
    }

    run(parameters) {
        this._di = new Container(parameters);

        // Register the services
        registerServices(this._di);

        // Execute the page load listeners
        on(window, 'load', () => {
            this._listeners.forEach((listener) => {
                listener(this._di);
            });
        });
    }

    get(key) {
        return this._di.get(key);
    }

    share(type, url, title) {
        this._di.get('sharer').share(type, url, title);
    }
}

window.App = new App();

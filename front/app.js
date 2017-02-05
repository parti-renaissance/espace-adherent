import './style/app.scss';

import Container from './services/Container';
import registerServices from './services';

// Listeners
import amountChooser from './listeners/amount-chooser';
import cookiesConsent from './listeners/cookies-consent';
import donationBanner from './listeners/donation-banner';
import progressiveBackground from './listeners/progressive-background';
import externalLinks from './listeners/external-links';

class App {
    constructor() {
        this._di = null;
        this._listeners = [
            cookiesConsent,
            donationBanner,
            amountChooser,
            progressiveBackground,
            externalLinks,
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
        this._listeners.forEach((listener) => {
            listener(this._di);
        });
    }

    get(key) {
        return this._di.get(key);
    }

    share(type, url, title) {
        this._di.get('sharer').share(type, url, title);
    }

    creteAddressSelector(country, postalCode, city, cityName) {
        const formFactory = this._di.get('address.form_factory');
        const form = formFactory.createAddressForm(country, postalCode, city, cityName);

        form.prepare();
        form.refresh();
        form.attachEvents();
    }

    runDonation() {
        System.import('pages/donation').catch((error) => { throw error; }).then((module) => {
            module.default();
        });
    }
}

window.App = new App();

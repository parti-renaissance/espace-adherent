import Container from "./services/Container";
import registerServices from './services';

import amountChooser from './listeners/amount-chooser'
import addressAutocomplete from './listeners/address-autocomplete'
import carousel from './listeners/carousel'

class Main {
    constructor() {
        this._di = null;
        this._listeners = [
            amountChooser,
            addressAutocomplete,
            carousel,
        ];
    }

    addListener(listener) {
        this._listeners.push(listener);
    }

    run(parameters) {
        this._di = new Container(parameters);

        registerServices(this._di);

        // Execute the page load listeners
        this._listeners.forEach((listener) => {
            listener(this._di)
        });
    }

    get(key) {
        return this._di.get(key);
    }

    runHomePage() {
        import('pages/home_page').then((module) => module.default());
    }

    runDonationPage() {
        import('pages/donation_page').catch((error) => { throw error; }).then((module) => module.default());
    }

    runAdhesionAmountPage() {
        import('pages/adhesion_amount_page').catch((error) => { throw error; }).then((module) => module.default());
    }

    runMailchimpResubscribeEmail({
        redirectUrl = null, signupPayload = null, authenticated = true, callback = null,
    }) {
        import('pages/mc_resubscribe_email').catch((error) => { throw error; }).then((module) => module.default(this.get('api'), redirectUrl, signupPayload, authenticated, callback))
    }
}

window.Main = new Main();

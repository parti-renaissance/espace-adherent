import amountChooser from './listeners/amount-chooser'
import addressAutocomplete from './listeners/address-autocomplete'
import carousel from './listeners/carousel'
import glider from './listeners/glider'

class Main {
    constructor() {
        this._listeners = [
            amountChooser,
            addressAutocomplete,
            carousel,
            glider,
        ];
    }

    addListener(listener) {
        this._listeners.push(listener);
    }

    run() {
        // Execute the page load listeners
        this._listeners.forEach(listener => listener());
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
}

window.Main = new Main();

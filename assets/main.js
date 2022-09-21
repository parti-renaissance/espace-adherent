import amountChooser from './listeners/amount-chooser'
import addressAutocomplete from './listeners/address-autocomplete'

class Main {
    constructor() {
        this._listeners = [
            amountChooser,
            addressAutocomplete,
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
}

window.Main = new Main();

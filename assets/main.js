class Main {
    constructor() {
        this._listeners = [];
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

    runRenaissanceDonation() {
        import('pages/renaissance_donation').catch((error) => { throw error; }).then((module) => module.default('.renaissance-donation-widget-wrapper'));
    }
}

window.Main = new Main();

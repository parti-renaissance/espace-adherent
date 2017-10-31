import './style/app.scss';

import Container from './services/Container';
import registerServices from './services';

// Listeners
import amountChooser from './listeners/amount-chooser';
import cookiesConsent from './listeners/cookies-consent';
import donationBanner from './listeners/donation-banner';
import progressiveBackground from './listeners/progressive-background';
import externalLinks from './listeners/external-links';
import noJsRecaptcha from './listeners/no-js-recaptcha';
import alogliaSearch from './listeners/algolia-search';

class App {
    constructor() {
        this._di = null;
        this._listeners = [
            cookiesConsent,
            donationBanner,
            amountChooser,
            progressiveBackground,
            externalLinks,
            noJsRecaptcha,
            alogliaSearch,
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

    createAddressSelector(country, postalCode, city, cityName, cityNameRequired) {
        const formFactory = this._di.get('address.form_factory');
        const form = formFactory.createAddressForm(country, postalCode, city, cityName, cityNameRequired);

        form.prepare();
        form.refresh();
        form.attachEvents();
    }

    createVoteLocationSelector(country, postalCode, city, cityName, office) {
        const formFactory = this._di.get('vote_location.form_factory');
        const form = formFactory.createVoteLocationForm(country, postalCode, city, cityName, office);

        form.prepare();
        form.refresh();
        form.attachEvents();
    }

    runDonation() {
        System.import('pages/donation').catch((error) => { throw error; }).then((module) => {
            module.default();
        });
    }

    runOrganisation() {
        System.import('pages/organisation').catch((error) => { throw error; }).then((module) => {
            module.default(this.get('map_factory'), this.get('api'));
        });
    }

    runCommitteesMap() {
        System.import('pages/committees_map').catch((error) => { throw error; }).then((module) => {
            module.default(this.get('map_factory'), this.get('api'));
        });
    }

    runEventsMap() {
        System.import('pages/events_map').catch((error) => { throw error; }).then((module) => {
            module.default(this.get('map_factory'), this.get('api'));
        });
    }

    runReferentUsers(columns, users) {
        System.import('pages/referent_users').catch((error) => { throw error; }).then((module) => {
            module.default(this.get('slugifier'), columns, users);
        });
    }

    runReferentList(columns, items) {
        System.import('pages/referent_list').catch((error) => { throw error; }).then((module) => {
            module.default(this.get('slugifier'), columns, items);
        });
    }

    runJeMarche() {
        System.import('pages/jemarche').catch((error) => { throw error; }).then((module) => {
            module.default();
        });
    }

    runRegistration() {
        System.import('pages/registration').catch((error) => { throw error; }).then((module) => {
            module.default();
        })
    }

    runProcurationThanks() {
        System.import('pages/procuration_thanks').catch((error) => { throw error; }).then((module) => {
            module.default();
        })
    }

    runProcurationManagerRequests(queryString, totalCount, perPage) {
        System.import('pages/procuration_manager_requests').catch((error) => { throw error; }).then((module) => {
            module.default(queryString, totalCount, perPage, this.get('api'));
        })
    }

    runProcurationManagerProposals(queryString, totalCount, perPage) {
        System.import('pages/procuration_manager_proposals').catch((error) => { throw error; }).then((module) => {
            module.default(queryString, totalCount, perPage, this.get('api'));
        })
    }

    runSocialShare(urlAll, urlCategory) {
        System.import('pages/social_share').catch((error) => { throw error; }).then((module) => {
            module.default(urlAll, urlCategory);
        })
    }

    runFacebookPictureChooser(urls) {
        System.import('pages/facebook_pictures').catch((error) => { throw error; }).then((module) => {
            module.default(urls, this.get('api'));
        })
    }

    runLegislativesCandidatesList() {
        System.import('pages/candidates_list').catch((error) => { throw error; }).then((module) => {
            module.default();
        });
    }

    runLegislativesCandidatesMap() {
        System.import('pages/candidates_map').catch((error) => { throw error; }).then((module) => {
            module.default(this.get('map_factory'), this.get('api'));
        });
    }

    runReferentsList() {
        System.import('pages/referents_list').catch((error) => { throw error; }).then((module) => {
            module.default();
        });
    }

    runBoardMember() {
        System.import('pages/board_member_list').catch((error) => { throw error; }).then((module) => {
            module.default(this.get('api'));
        });
    }
}

window.App = new App();

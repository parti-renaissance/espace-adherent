import Container from './services/Container';
import registerServices from './services';

import amountChooser from './listeners/amount-chooser';
import addressAutocomplete from './listeners/address-autocomplete';
import confirmModal from './listeners/confirm-modal';
import setupLucide from './listeners/lucide';

import alpine from './listeners/alpine';

class Main {
    constructor() {
        this._di = null;
        this._listeners = [amountChooser, addressAutocomplete, confirmModal, alpine, setupLucide];
    }

    addListener(listener) {
        this._listeners.push(listener);
    }

    async run(parameters) {
        this._di = new Container(parameters);

        registerServices(this._di);

        // Execute the page load listeners
        // eslint-disable-next-line no-restricted-syntax
        for await (const listener of this._listeners) {
            await listener(this._di);
        }
    }

    get(key) {
        return this._di.get(key);
    }

    runDonationFunnelPage() {
        return import('pages/donation_funnel')
            .catch((error) => {
                throw error;
            })
            .then((module) => module.default());
    }

    runAdhesionPage(props) {
        return import('pages/adhesion_funnel/index')
            .catch((error) => {
                throw error;
            })
            .then((module) => module.default(props));
    }

    runAdhesionConfirmEmailPage() {
        return import('pages/adhesion_confirmation_email')
            .catch((error) => {
                throw error;
            })
            .then((module) => module.default());
    }

    runPaymentStatusPage(...args) {
        return import('pages/payment_status')
            .catch((error) => {
                throw error;
            })
            .then((module) => module.default(...args));
    }

    runAdhesionCreatePasswordPage() {
        return import('pages/adhesion_create_password')
            .catch((error) => {
                throw error;
            })
            .then((module) => module.default());
    }

    runAdhesionFurtherInformationPage() {
        return import('pages/adhesion_further_information')
            .catch((error) => {
                throw error;
            })
            .then((module) => module.default());
    }

    runAdhesionCommitteePage() {
        return import('pages/adhesion_committee')
            .catch((error) => {
                throw error;
            })
            .then((module) => module.default());
    }

    runAdhesionMemberCardPage() {
        return import('pages/adhesion_member_card')
            .catch((error) => {
                throw error;
            })
            .then((module) => module.default());
    }

    runNationaleEventPage() {
        return import('pages/national_event')
            .catch((error) => {
                throw error;
            })
            .then((module) => module.default());
    }

    runProxyVotingRepresentativePage() {
        return import('pages/proxy_voting_representative')
            .catch((error) => {
                throw error;
            })
            .then((module) => module.default());
    }

    runBDEInscriptionPage() {
        return import('pages/bde_inscription')
            .catch((error) => {
                throw error;
            })
            .then((module) => module.default());
    }

    runProxyVotingRepresentativeThanksPage() {
        return import('pages/proxy_voting_representative/thanks')
            .catch((error) => {
                throw error;
            })
            .then((module) => module.default());
    }

    runMailchimpResubscribeEmail({ redirectUrl = null, signupPayload = null, authenticated = true, callback = null, uuid = null, apiKey = null }) {
        import('pages/mc_resubscribe_email')
            .catch((error) => {
                throw error;
            })
            .then((module) => module.default(this.get('api'), redirectUrl, signupPayload, authenticated, callback, uuid, apiKey));
    }

    runCountdownClock(clockSelector, refreshPage = false) {
        import('services/utils/countdownClock')
            .catch((error) => {
                throw error;
            })
            .then((module) => {
                module.default(clockSelector, refreshPage);
            });
    }

    runDepartmentMapPage() {
        import('pages/department_map')
            .catch((error) => {
                throw error;
            })
            .then((module) => {
                module.default();
            });
    }
}

window.Main = new Main();

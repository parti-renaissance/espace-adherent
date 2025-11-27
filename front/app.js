import Container from './services/Container';
import registerServices from './services';

// Listeners
import donationBanner from './listeners/donation-banner';
import progressiveBackground from './listeners/progressive-background';
import externalLinks from './listeners/external-links';
import noJsRecaptcha from './listeners/no-js-recaptcha';
import confirmModal from './listeners/confirm-modal';
import emModal from './listeners/em-modal';
import emDateTimePicker from './listeners/em-datetime-picker';
import AutocompletedAddressForm from './services/address/AutocompletedAddressForm';
import AddressObject from './services/address/AddressObject';

class App {
    constructor() {
        this._di = null;
        this._listeners = [donationBanner, progressiveBackground, externalLinks, noJsRecaptcha, confirmModal, emModal, emDateTimePicker];
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

    createAddressSelector(country, postalCode, city, cityName, cityNameRequired, region = null) {
        const formFactory = this._di.get('address.form_factory');
        const form = formFactory.createAddressForm(country, postalCode, city, cityName, cityNameRequired, region);

        form.prepare();
        form.refresh();
        form.attachEvents();
    }

    createAutocompleteAddress(
        addressFieldSelector,
        zipCodeFieldSelector,
        cityNameFieldSelector,
        regionFieldSelector,
        countryFieldSelector,
        autocompleteWrapperSelector = '.address-autocomplete',
        addressBlockWrapperSelector = '.address-block',
        helpMessageContainer = '#address-autocomplete-help-message',
        showOnlyAutocomplete = false
    ) {
        const addressObject = new AddressObject(
            dom(addressFieldSelector),
            dom(zipCodeFieldSelector),
            dom(cityNameFieldSelector),
            dom(regionFieldSelector),
            dom(countryFieldSelector)
        );

        const autocompleteAddressForm = new AutocompletedAddressForm(
            dom(autocompleteWrapperSelector),
            dom(addressBlockWrapperSelector),
            addressObject,
            dom(helpMessageContainer),
            false,
            showOnlyAutocomplete
        );

        autocompleteAddressForm.buildWidget();
    }

    runReport() {
        import('pages/report')
            .catch((error) => {
                throw error;
            })
            .then((module) => {
                module.default();
            });
    }

    runManagedList(columns, items) {
        import('pages/managed_list')
            .catch((error) => {
                throw error;
            })
            .then((module) => {
                module.default(columns, items);
            });
    }

    runSocialShare(urlAll, urlCategory) {
        import('pages/social_share')
            .catch((error) => {
                throw error;
            })
            .then((module) => {
                module.default(urlAll, urlCategory);
            });
    }

    runCandidacyModal(triggerSelector) {
        import('pages/committee_candidacies')
            .catch((error) => {
                throw error;
            })
            .then((module) => {
                module.default(triggerSelector, this.get('api'));
            });
    }

    runProfileUpdatePage() {
        import('pages/profile_update')
            .catch((error) => {
                throw error;
            })
            .then((module) => {
                module.default();
            });
    }

    runImageCropper(inputFileElement, inputCroppedImageElement, inputsContainerElement, options = { ratio: 1, width: 500, height: 500 }) {
        import('services/utils/imageCropper')
            .catch((error) => {
                throw error;
            })
            .then((module) => {
                module.default(inputFileElement, inputCroppedImageElement, inputsContainerElement, options);
            });
    }

    runCommitteeCandidacy(slug, submitButtonSelector, wrapperSelector) {
        import('pages/committee_candidacy')
            .catch((error) => {
                throw error;
            })
            .then((module) => {
                module.default(this.get('api'), slug, submitButtonSelector, wrapperSelector);
            });
    }

    runResubscribeEmail({ redirectUrl = null, signupPayload = null, authenticated = true, callback = null }) {
        import('pages/resubscribe_email')
            .catch((error) => {
                throw error;
            })
            .then((module) => {
                module.default(this.get('api'), redirectUrl, signupPayload, authenticated, callback);
            });
    }
}

window.App = new App();

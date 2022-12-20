import GooglePlaceAutocomplete from '../components/Address/GooglePlaceAutocomplete';
import AddressForm from '../components/Address/AddressForm';

const enableAddressFields = (addressForm, autocompleteWrapper, addressFieldsWrapper) => {
    findOne(autocompleteWrapper, 'input').required = false;
    hide(autocompleteWrapper);

    addressForm.showFields();
    show(addressFieldsWrapper);
};

const enableAddressAutocomplete = (addressForm, autocompleteWrapper, addressFieldsWrapper) => {
    hide(addressFieldsWrapper);
    addressForm.hideFields();

    findOne(autocompleteWrapper, 'input').required = true;
    show(autocompleteWrapper);
};

export default () => {
    findAll(document, '.address-autocomplete').forEach((addressContainer) => {
        const autocompleteWrapper = findOne(addressContainer, '.address-autocomplete-wrapper');
        const addressFieldsWrapper = findOne(addressContainer, '.address-fields-wrapper');

        if ('undefined' === typeof google) {
            return;
        }

        const addressObject = {};

        if (addressContainer.dataset.form) {
            const formName = addressContainer.dataset.form;

            findAll(addressContainer.closest('form'), `input[name*="[${formName}]"],select[name*="[${formName}]"]`).forEach((addressElement) => {
                const name = addressElement.name.substring(addressElement.name.indexOf(`[${formName}]`) + formName.length + 2).replace(/(\]|\[)/g, '');

                if ('autocomplete' !== name) {
                    addressObject[name] = addressElement;
                    addressElement.dataset.required = addressElement.required;
                }
            });
        }

        const addressForm = Object.keys(addressObject).length ? new AddressForm(addressObject) : null;

        const widget = new GooglePlaceAutocomplete({
            wrapper: autocompleteWrapper,
            addressForm,
        });

        widget.build();

        findOne(autocompleteWrapper, '.enable-address-fields').onclick = (event) => {
            event.preventDefault();

            enableAddressFields(addressForm, autocompleteWrapper, addressFieldsWrapper);
        };

        findOne(addressFieldsWrapper, '.enable-address-autocomplete').onclick = (event) => {
            event.preventDefault();

            enableAddressAutocomplete(addressForm, autocompleteWrapper, addressFieldsWrapper);
        };

        enableAddressAutocomplete(addressForm, autocompleteWrapper, addressFieldsWrapper);
    });
};

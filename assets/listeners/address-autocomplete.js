import GooglePlaceAutocomplete from '../components/Address/GooglePlaceAutocomplete';
import AddressForm from '../components/Address/AddressForm';

export default () => {
    findAll(document, '.address-autocomplete').forEach((autocompleteWrapper) => {
        if ('undefined' === typeof google) {
            hide(autocompleteWrapper);
            return;
        }

        const addressObject = {};

        if (autocompleteWrapper.dataset.form) {
            const formName = autocompleteWrapper.dataset.form;

            findAll(autocompleteWrapper.closest('form'), `input[name*="[${formName}]"],select[name*="[${formName}]"]`).forEach((addressElement) => {
                const name = addressElement.name.substring(addressElement.name.indexOf(`[${formName}]`) + formName.length + 2).replace(/(\]|\[)/g, '');
                addressObject[name] = addressElement;
            });
        }

        const addressField = addressObject.address;
        const addressForm = Object.keys(addressObject).length ? new AddressForm(addressObject) : null;

        const widget = new GooglePlaceAutocomplete({
            wrapper: autocompleteWrapper,
            addressForm,
            inputClassName: addressField.className,
        });

        widget.on('changed', () => {
            find(autocompleteWrapper, 'input').value = addressField.value;
        });

        widget.build();

        hide(addressField);
    });
};

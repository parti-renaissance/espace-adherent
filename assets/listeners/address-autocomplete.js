import GooglePlaceAutocomplete from '../components/Address/GooglePlaceAutocomplete';
import AddressForm from '../components/Address/AddressForm';

export default () => {
    findAll(document, 'input.address-autocomplete').forEach((element) => {
        const addressObject = {};

        if (element.dataset.form) {
            findAll(element.closest('form'), `input[name*="${element.dataset.form}"]`).forEach((addressElement) => {
                const name = addressElement.name.substring(addressElement.name.indexOf(`[${element.dataset.form}]`) + element.dataset.form.length + 2).replace(/(\]|\[)/g, '');
                addressObject[name] = addressElement;
            });
        }

        const widget = new GooglePlaceAutocomplete({
            element,
            addressForm: (Object.keys(addressObject).length ? new AddressForm(addressObject) : null),
        });

        widget.build();
    });
};

import formValidator from '../validator/formValidator';
import GooglePlaceAutocomplete from '../services/address/GooglePlaceAutocomplete';
import AddressObject from '../services/address/AddressObject';

function hideAddress(address, addressBlock) {
    address.setRequired(false);
    hide(addressBlock);
}

function showAddress(address, addressBlock) {
    address.resetRequired();
    show(addressBlock);
}

export default (formType) => {
    formValidator(formType, dom('form[name="app_donation"]'));

    if ('undefined' === typeof google) {
        return;
    }

    const autocompeleteWrapper = dom('.address-autocomplete');
    const addressBlock = dom('.address-block');

    const address = new AddressObject(
        dom('#app_donation_address'),
        dom('#app_donation_postalCode'),
        dom('#app_donation_cityName'),
        dom('#app_donation_country')
    );

    // show the autocomplete when the address fields are not filled
    if (!address.isFilled()) {
        const autocomplete = new GooglePlaceAutocomplete(autocompeleteWrapper, address, 'form form--full form__field');
        autocomplete.build();

        hideAddress(address, addressBlock);

        autocomplete.on('changed', () => {
            showAddress(address, addressBlock);
            hide(autocompeleteWrapper);
        });
    }
};

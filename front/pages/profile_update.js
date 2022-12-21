/* eslint-disable no-restricted-globals */
import AutocompletedAddressForm from '../services/address/AutocompletedAddressForm';
import AddressObject from '../services/address/AddressObject';

export default () => {
    const autocompleteWrapper = dom('.address-autocomplete');

    const countryField = dom('#adherent_profile_address_country');
    const postalCodeField = dom('#adherent_profile_address_postalCode');
    const cityNameField = dom('#adherent_profile_address_cityName');
    const addressField = dom('#adherent_profile_address_address');

    if ('undefined' !== typeof google) {
        hide(addressField.parentNode);
    }

    const autocompleteWidget = new AutocompletedAddressForm(
        autocompleteWrapper,
        dom('.address-block'),
        new AddressObject(
            addressField,
            postalCodeField,
            cityNameField,
            null,
            countryField
        ),
        dom('#address-autocomplete-help-message'),
        true
    );

    autocompleteWidget.on('changed', () => {
        findOne(autocompleteWrapper, 'input.form').value = addressField.value;

        countryField.dispatchEvent(new CustomEvent('change', {
            target: countryField,
            detail: {
                zipCode: postalCodeField.value,
                cityName: cityNameField.value,
            },
        }));
    });

    autocompleteWidget.buildWidget();

    const autocompleteInput = findOne(autocompleteWrapper, 'input.form');

    if (autocompleteInput) {
        autocompleteInput.value = addressField.value;
    }

    autocompleteWidget.once('no_result', () => {
        hide(autocompleteWrapper);
        addressField.value = autocompleteInput.value;
        show(addressField.parentNode);
    });
};

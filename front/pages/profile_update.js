import AutocompletedAddressForm from '../services/address/AutocompletedAddressForm';
import AddressObject from '../services/address/AddressObject';

export default () => {
    const countryField = dom('#adherent_address_country');
    const postalCodeField = dom('#adherent_address_postalCode');
    const cityNameField = dom('#adherent_address_cityName');

    const autocompleteWidget = new AutocompletedAddressForm(
        dom('.address-autocomplete'),
        dom('.address-block'),
        new AddressObject(
            dom('#adherent_address_address'),
            postalCodeField,
            cityNameField,
            null,
            countryField
        ),
        dom('#address-autocomplete-help-message'),
        true
    );

    autocompleteWidget.once('changed', () => {
        countryField.dispatchEvent(new CustomEvent('change', {
            target: countryField,
            detail: {
                zipCode: postalCodeField.value,
                cityName: cityNameField.value,
            },
        }));
    });

    autocompleteWidget.buildWidget();
};

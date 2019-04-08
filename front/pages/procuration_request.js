import AutocompletedAddressForm from '../services/address/AutocompletedAddressForm';
import AddressObject from '../services/address/AddressObject';
import changeStateFieldVisibility from '../services/form/changeStateFieldVisibility';

export default (countryFieldSelector, stateSelector) => {
    (new AutocompletedAddressForm(
        dom('.address-autocomplete'),
        dom('.address-block'),
        new AddressObject(
            dom('#app_procuration_request_address'),
            dom('#app_procuration_request_postalCode'),
            dom('#app_procuration_request_cityName'),
            null,
            dom('#app_procuration_request_country')
        )
    )).buildWidget();

    changeStateFieldVisibility(countryFieldSelector, stateSelector);
};

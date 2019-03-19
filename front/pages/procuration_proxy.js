import AutocompletedAddressForm from '../services/address/AutocompletedAddressForm';
import AddressObject from '../services/address/AddressObject';

export default () => {
    (new AutocompletedAddressForm(
        dom('.address-autocomplete'),
        dom('.address-block'),
        new AddressObject(
            dom('#app_procuration_proposal_address'),
            dom('#app_procuration_proposal_postalCode'),
            dom('#app_procuration_proposal_cityName'),
            null,
            dom('#app_procuration_proposal_country')
        )
    )).buildWidget();
};

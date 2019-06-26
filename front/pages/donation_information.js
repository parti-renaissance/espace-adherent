import formValidator from '../validator/formValidator';
import AutocompletedAddressForm from '../services/address/AutocompletedAddressForm';
import AddressObject from '../services/address/AddressObject';

export default (formType) => {
    formValidator(formType, dom('form[name="app_donation"]'));

    (new AutocompletedAddressForm(
        dom('.address-autocomplete'),
        dom('.address-block'),
        new AddressObject(
            dom('#app_donation_address'),
            dom('#app_donation_postalCode'),
            dom('#app_donation_cityName'),
            null,
            dom('#app_donation_country')
        ),
        dom('#address-autocomplete-help-message')
    )).buildWidget();
};
